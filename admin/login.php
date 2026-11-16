<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';

start_admin_session();

$errorMessage = '';
$email = '';

if (($_GET['expired'] ?? '') === '1') {
    $errorMessage = 'You were signed out after being inactive.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errorMessage = 'Your form expired. Reload the page and try again.';
    } else {
        $emailInput = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';

        try {
            $connection = get_database_connection();
            $ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $connection->exec(
                'DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)'
            );
            $attemptCount = $connection->prepare(
                'SELECT COUNT(*) FROM login_attempts
                 WHERE ip_hash = :ip_hash
                 AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
            );
            $attemptCount->execute(['ip_hash' => $ipHash]);

            if ((int) $attemptCount->fetchColumn() >= 5) {
                http_response_code(429);
                $errorMessage = 'Too many failed attempts. Please wait 15 minutes and try again.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                $recordAttempt = $connection->prepare(
                    'INSERT INTO login_attempts (ip_hash) VALUES (:ip_hash)'
                );
                $recordAttempt->execute(['ip_hash' => $ipHash]);
                $errorMessage = 'The email or password is incorrect.';
            } else {
                $findAdmin = $connection->prepare(
                    'SELECT id, email, password_hash FROM admins
                     WHERE email = :email AND is_active = 1 LIMIT 1'
                );
                $findAdmin->execute(['email' => $email]);
                $admin = $findAdmin->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    if (!session_regenerate_id(true)) {
                        throw new RuntimeException('Unable to renew the administrator session.');
                    }

                    $_SESSION['admin_id'] = (int) $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    $clearAttempts = $connection->prepare(
                        'DELETE FROM login_attempts WHERE ip_hash = :ip_hash'
                    );
                    $clearAttempts->execute(['ip_hash' => $ipHash]);

                    header('Location: dashboard.php');
                    exit;
                }

                $recordAttempt = $connection->prepare(
                    'INSERT INTO login_attempts (ip_hash) VALUES (:ip_hash)'
                );
                $recordAttempt->execute(['ip_hash' => $ipHash]);
                $errorMessage = 'The email or password is incorrect.';
            }
        } catch (PDOException $error) {
            error_log('Admin login database error: ' . $error->getMessage());
            http_response_code(503);
            $errorMessage = 'Login is temporarily unavailable. Please try again later.';
        } catch (RuntimeException $error) {
            error_log('Admin login setup error: ' . $error->getMessage());
            http_response_code(503);
            $errorMessage = 'Login is temporarily unavailable. Please try again later.';
        }
    }
}

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-card">
    <h1>Admin Login</h1>
    <p>Sign in to manage the portfolio.</p>

    <?php if ($errorMessage !== ''): ?>
      <p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= escape_html($csrfToken) ?>">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" maxlength="254"
             value="<?= escape_html($email) ?>" autocomplete="username" required>

      <label for="password">Password</label>
      <input id="password" name="password" type="password"
             autocomplete="current-password" required>

      <button class="btn btn-primary" type="submit">Log in</button>
    </form>

    <a class="back-link" href="../index.html">Back to portfolio</a>
  </main>
</body>
</html>
