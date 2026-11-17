<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/uploads.php';
require_admin();

$section = $_GET['section'] ?? '';
if (!is_string($section)) {
    http_response_code(400);
    exit('Choose a valid content section.');
}
try {
    $config = get_managed_content_config($section);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    exit('Choose a valid content section.');
}

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Content updated.' : '';
$record = [];
$items = [];
$newImage = '';
$previousImagePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            $connection = get_database_connection();
            $action = $_POST['action'] ?? '';
            $recordId = $_POST['id'] ?? null;
            if ($action === 'delete' && is_string($recordId)) {
                if (!delete_managed_content($connection, $section, $recordId)) {
                    throw new InvalidArgumentException('The content item was not found.');
                }
            } elseif (($action === 'create' || $action === 'update')
                && (($action === 'create' && $recordId === null)
                    || ($action === 'update' && is_string($recordId)))
            ) {
                if ($section === 'projects') {
                    $previousImagePath = is_string($_POST['image_path'] ?? null) ? $_POST['image_path'] : '';
                    $upload = $_FILES['project_image'] ?? null;
                    if ($upload !== null) {
                        if (!is_array($upload)) {
                            throw new InvalidArgumentException('The image upload could not be verified.');
                        }
                        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                            $newImage = save_uploaded_image($upload);
                            $_POST['image_path'] = $newImage;
                        }
                    }
                }
                save_managed_content(
                    $connection,
                    $section,
                    $_POST,
                    $action === 'update' ? $recordId : null
                );
            } else {
                throw new InvalidArgumentException('Choose a valid content action.');
            }

            header('Location: content.php?section=' . rawurlencode($section) . '&saved=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
            $record = $_POST;
        } catch (PDOException $error) {
            error_log('Admin content database error: ' . $error->getMessage());
            $errorMessage = 'The content could not be saved.';
            $record = $_POST;
        } catch (RuntimeException $error) {
            error_log('Admin content setup error: ' . $error->getMessage());
            $errorMessage = 'The content could not be saved.';
            $record = $_POST;
        }

        if ($newImage !== '') {
            $imageFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newImage);
            if (is_file($imageFile) && !unlink($imageFile)) {
                error_log('Could not remove an unused project image upload.');
            }
            $record['image_path'] = $previousImagePath;
        }
    }
}

try {
    $connection = get_database_connection();
    $items = get_portfolio_items($connection, $section);
    $editId = $_GET['id'] ?? null;
    if ($record === [] && $editId !== null) {
        if (!is_string($editId)) {
            throw new InvalidArgumentException('Choose a valid content item.');
        }
        $record = get_managed_content_record($connection, $section, $editId);
        if (!$record) {
            throw new InvalidArgumentException('The content item was not found.');
        }
    }
} catch (InvalidArgumentException $error) {
    $errorMessage = $error->getMessage();
} catch (PDOException $error) {
    error_log('Admin content load database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Content is temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin content load setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Content is temporarily unavailable.';
}

$csrfToken = get_csrf_token();
$isEditing = (is_int($record['id'] ?? null) || is_string($record['id'] ?? null))
    && filter_var($record['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage <?= escape_html($config['title']) ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Manage <?= escape_html($config['title']) ?></h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= escape_html($csrfToken) ?>">
      <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create' ?>">
      <?php if ($isEditing): ?><input type="hidden" name="id" value="<?= (int) $record['id'] ?>"><?php endif; ?>
      <?php foreach ($config['fields'] as $name => $field): ?>
        <label for="<?= escape_html($name) ?>"><?= escape_html($field['label']) ?></label>
        <?php if ($field['type'] === 'textarea'): ?>
          <textarea id="<?= escape_html($name) ?>" name="<?= escape_html($name) ?>" maxlength="<?= (int) $field['max'] ?>" <?= !empty($field['required']) ? 'required' : '' ?>><?= escape_html((string) ($record[$name] ?? '')) ?></textarea>
        <?php elseif ($field['type'] === 'select'): ?>
          <select id="<?= escape_html($name) ?>" name="<?= escape_html($name) ?>" required>
            <?php foreach ($field['options'] as $option): ?>
              <option value="<?= escape_html($option) ?>" <?= ($record[$name] ?? '') === $option ? 'selected' : '' ?>><?= escape_html($option) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($field['type'] === 'image'): ?>
          <input type="hidden" name="image_path" value="<?= escape_html((string) ($record['image_path'] ?? '')) ?>">
          <input id="image_path" type="file" name="project_image" accept="image/jpeg,image/png,image/gif,image/webp">
        <?php else: ?>
          <input id="<?= escape_html($name) ?>" name="<?= escape_html($name) ?>"
            type="<?= escape_html($field['type']) ?>"
            <?= $field['type'] === 'text' && isset($field['max']) ? 'maxlength="' . (int) $field['max'] . '"' : '' ?>
            <?= isset($field['min']) ? 'min="' . (int) $field['min'] . '"' : '' ?>
            <?= isset($field['max']) && $field['type'] === 'number' ? 'max="' . (int) $field['max'] . '"' : '' ?>
            value="<?= escape_html((string) ($record[$name] ?? '')) ?>"
            <?= !empty($field['required']) ? 'required' : '' ?>>
        <?php endif; ?>
      <?php endforeach; ?>
      <button type="submit"><?= $isEditing ? 'Save changes' : 'Add item' ?></button>
      <?php if ($isEditing): ?><a href="content.php?section=<?= rawurlencode($section) ?>">Cancel</a><?php endif; ?>
    </form>

    <h2>Current <?= escape_html($config['title']) ?></h2>
    <?php foreach ($items as $item): ?>
      <article class="stat-card">
        <h3><?= escape_html((string) ($item['title'] ?? $item['program'] ?? $item['school'] ?? 'Content item')) ?></h3>
        <p><?= escape_html((string) ($item['description'] ?? $item['organization'] ?? '')) ?></p>
        <a href="content.php?section=<?= rawurlencode($section) ?>&amp;id=<?= (int) $item['id'] ?>">Edit</a>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= escape_html($csrfToken) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
          <button type="submit">Delete</button>
        </form>
      </article>
    <?php endforeach; ?>
  </main>
</body>
</html>
