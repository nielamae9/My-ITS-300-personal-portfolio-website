<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/uploads.php';
require_admin();

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Profile updated.' : '';
$profile = [];
$newImage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            $connection = get_database_connection();
            $currentProfile = get_portfolio_profile($connection);
            if (!$currentProfile) {
                throw new RuntimeException('The portfolio profile was not found.');
            }
            $imagePath = $currentProfile['profile_image'];
            $upload = $_FILES['profile_image'] ?? null;
            if ($upload !== null) {
                if (!is_array($upload)) {
                    throw new InvalidArgumentException('The image upload could not be verified.');
                }
                if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $newImage = save_uploaded_image($upload);
                    $imagePath = $newImage;
                }
            }
            $_POST['profile_image'] = $imagePath;
            update_portfolio_profile($connection, $_POST);
            header('Location: profile.php?saved=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
        } catch (PDOException $error) {
            error_log('Admin profile database error: ' . $error->getMessage());
            $errorMessage = 'The profile could not be saved.';
        } catch (RuntimeException $error) {
            error_log('Admin profile setup error: ' . $error->getMessage());
            $errorMessage = 'The profile could not be saved.';
        }
        if ($newImage !== '') {
            $imageFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newImage);
            if (is_file($imageFile) && !unlink($imageFile)) {
                error_log('Could not remove an unused profile image upload.');
            }
        }
    }
}

try {
    $profile = get_portfolio_profile(get_database_connection());
    if (!$profile) {
        throw new RuntimeException('The portfolio profile was not found.');
    }
} catch (PDOException $error) {
    error_log('Admin profile load database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'The profile is temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin profile load setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'The profile is temporarily unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Edit Profile</h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
      <label>Name <input name="full_name" maxlength="150" value="<?= escape_html((string) ($profile['full_name'] ?? '')) ?>" required></label>
      <label>Headline <input name="headline" maxlength="200" value="<?= escape_html((string) ($profile['headline'] ?? '')) ?>" required></label>
      <label>Introduction <textarea name="introduction" maxlength="2000" required><?= escape_html((string) ($profile['introduction'] ?? '')) ?></textarea></label>
      <label>Biography <textarea name="biography" maxlength="5000" required><?= escape_html((string) ($profile['biography'] ?? '')) ?></textarea></label>
      <label>Email <input type="email" name="email" maxlength="254" value="<?= escape_html((string) ($profile['email'] ?? '')) ?>" required></label>
      <label>Facebook URL <input type="url" name="facebook_url" maxlength="255" value="<?= escape_html((string) ($profile['facebook_url'] ?? '')) ?>"></label>
      <label>Instagram URL <input type="url" name="instagram_url" maxlength="255" value="<?= escape_html((string) ($profile['instagram_url'] ?? '')) ?>"></label>
      <label>Profile image (JPG, PNG, GIF, or WebP; up to 5 MB) <input type="file" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp"></label>
      <button type="submit">Save profile</button>
    </form>
  </main>
</body>
</html>
