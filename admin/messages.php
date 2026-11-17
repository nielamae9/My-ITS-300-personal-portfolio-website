<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/messages.php';
require_admin();

$errorMessage = '';
$successMessage = isset($_GET['updated']) ? 'Messages updated.' : '';
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            $id = $_POST['id'] ?? null;
            if (!is_string($id)) {
                throw new InvalidArgumentException('Choose a valid message.');
            }
            $action = $_POST['action'] ?? '';
            $connection = get_database_connection();
            if ($action === 'delete') {
                if (!delete_contact_message($connection, $id)) {
                    throw new InvalidArgumentException('The message was not found.');
                }
            } elseif (!is_string($action) || !set_contact_message_status($connection, $id, $action)) {
                throw new InvalidArgumentException('Choose a valid message action.');
            }
            header('Location: messages.php?updated=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
        } catch (PDOException $error) {
            error_log('Admin messages database error: ' . $error->getMessage());
            $errorMessage = 'Messages could not be updated.';
        } catch (RuntimeException $error) {
            error_log('Admin messages setup error: ' . $error->getMessage());
            $errorMessage = 'Messages could not be updated.';
        }
    }
}

try {
    $messages = get_contact_messages(get_database_connection());
} catch (PDOException $error) {
    error_log('Admin messages load database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Messages are temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin messages load setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Messages are temporarily unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Contact Messages</h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <?php if (!$messages && $errorMessage === ''): ?><p>No contact messages yet.</p><?php endif; ?>
    <?php foreach ($messages as $message): ?>
      <article class="stat-card">
        <h2><?= escape_html($message['subject']) ?></h2>
        <p><?= escape_html($message['name']) ?> · <?= escape_html($message['email']) ?></p>
        <p><?= escape_html($message['created_at']) ?> · <?= escape_html($message['status']) ?></p>
        <p><?= nl2br(escape_html($message['message'])) ?></p>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
          <button name="action" value="<?= $message['status'] === 'read' ? 'unread' : 'read' ?>" type="submit">
            Mark <?= $message['status'] === 'read' ? 'unread' : 'read' ?>
          </button>
          <button name="action" value="delete" type="submit">Delete</button>
        </form>
      </article>
    <?php endforeach; ?>
  </main>
</body>
</html>
