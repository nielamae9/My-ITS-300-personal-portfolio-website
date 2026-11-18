<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/admin_account.php';
require_admin();

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Your account details were updated.' : '';
$email = (string) ($_SESSION['admin_email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            $email = update_admin_account(
                get_database_connection(),
                $_SESSION['admin_id'],
                $_POST
            );
            $_SESSION['admin_email'] = $email;
            header('Location: account.php?saved=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
            $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : $email;
        } catch (PDOException $error) {
            error_log('Admin account database error: ' . $error->getMessage());
            $errorMessage = 'Your account details could not be updated.';
        } catch (RuntimeException $error) {
            error_log('Admin account setup error: ' . $error->getMessage());
            $errorMessage = 'Your account details could not be updated.';
        }
    }
}

try {
    $account = get_admin_account(get_database_connection(), $_SESSION['admin_id']);
    $email = $account['email'];
} catch (PDOException $error) {
    error_log('Admin account load database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Account settings are temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin account load setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Account settings are temporarily unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Account</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Admin Account</h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <p>Enter your current password to change your login email or password. Leave both new password fields empty to keep your current password.</p>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
      <label for="email">Login email</label>
      <input id="email" name="email" type="email" maxlength="254" value="<?= escape_html($email) ?>" required>
      <label for="current-password">Current password</label>
      <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
      <label for="new-password">New password (at least 12 characters)</label>
      <input id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password">
      <label for="confirm-password">Confirm new password</label>
      <input id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password">
      <button type="submit">Update account</button>
    </form>
  </main>
</body>
</html>
