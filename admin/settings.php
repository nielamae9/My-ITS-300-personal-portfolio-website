<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/content.php';
require_admin();

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Website settings updated.' : '';
$settings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            update_portfolio_settings(get_database_connection(), $_POST);
            header('Location: settings.php?saved=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
        } catch (PDOException $error) {
            error_log('Admin settings database error: ' . $error->getMessage());
            $errorMessage = 'The settings could not be saved.';
        } catch (RuntimeException $error) {
            error_log('Admin settings setup error: ' . $error->getMessage());
            $errorMessage = 'The settings could not be saved.';
        }
    }
}

try {
    $settings = get_portfolio_settings(get_database_connection());
} catch (PDOException $error) {
    error_log('Admin settings load database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Website settings are temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin settings load setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Website settings are temporarily unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Website Settings</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Website Settings</h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
      <label>Site title <input name="site_title" maxlength="150" value="<?= escape_html((string) ($settings['site_title'] ?? '')) ?>" required></label>
      <label>Footer text <input name="footer_text" maxlength="300" value="<?= escape_html((string) ($settings['footer_text'] ?? '')) ?>" required></label>
      <button type="submit">Save settings</button>
    </form>
  </main>
</body>
</html>
