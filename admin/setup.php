<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/admin_account.php';

start_admin_session();

try {
    $connection = get_database_connection();
    if ((int) $connection->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
        header('Location: login.php');
        exit;
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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errorMessage = 'Refresh the page and try again.';
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
            if ($error->getMessage() === 'An administrator already exists.') {
                header('Location: login.php');
                exit;
            }
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
