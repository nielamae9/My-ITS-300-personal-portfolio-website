<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/admin_account.php';

start_admin_session();

$configPath = __DIR__ . '/../includes/config.local.php';
if (!is_file($configPath)) {
    http_response_code(404);
    exit('Setup is unavailable.');
}

$config = require $configPath;
$setupToken = is_array($config) ? ($config['admin_setup_token'] ?? null) : null;
if (!is_string($setupToken) || strlen($setupToken) < 32) {
    http_response_code(404);
    exit('Setup is unavailable.');
}

try {
    $connection = get_database_connection();
    if ((int) $connection->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
        http_response_code(404);
        exit('Setup is unavailable.');
    }
} catch (PDOException $error) {
    error_log('Initial admin setup database error: ' . $error->getMessage());
    http_response_code(503);
    exit('Admin setup is temporarily unavailable.');
} catch (RuntimeException $error) {
    error_log('Initial admin setup configuration error: ' . $error->getMessage());
    http_response_code(503);
    exit('Admin setup is temporarily unavailable.');
}

$errorMessage = '';
$email = 'nielamae959@gmail.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errorMessage = 'Refresh the page and try again.';
    } elseif (!is_string($_POST['setup_token'] ?? null)
        || !hash_equals($setupToken, $_POST['setup_token'])
    ) {
        http_response_code(404);
        exit('Setup is unavailable.');
    } else {
        try {
            $email = create_initial_admin($connection, $_POST);
            header('Location: login.php?setup=complete');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
            $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : $email;
        } catch (PDOException $error) {
            error_log('Initial admin creation database error: ' . $error->getMessage());
            $errorMessage = 'The admin account could not be created.';
        } catch (RuntimeException $error) {
            error_log('Initial admin creation setup error: ' . $error->getMessage());
            $errorMessage = 'Admin setup is no longer available. Try signing in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Initial Admin Setup</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-card">
    <h1>Initial Admin Setup</h1>
    <p>Create the first admin account. This page is disabled after an account is created.</p>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
      <label for="setup-token">Setup token</label>
      <input id="setup-token" name="setup_token" type="password" autocomplete="off" required>
      <label for="email">Login email</label>
      <input id="email" name="email" type="email" maxlength="254" value="<?= escape_html($email) ?>" required>
      <label for="password">Password (at least 12 characters)</label>
      <input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
      <label for="confirm-password">Confirm password</label>
      <input id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
      <button type="submit">Create admin account</button>
    </form>
  </main>
</body>
</html>
