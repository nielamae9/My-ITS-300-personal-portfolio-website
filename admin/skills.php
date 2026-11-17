<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/content.php';
require_admin();
$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Skill added.' : (isset($_GET['updated']) ? 'Skill updated.' : (isset($_GET['deleted']) ? 'Skill deleted.' : ''));
$skills = [];
$editSkill = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Refresh the page and try again.';
    } else {
        try {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                create_skill(get_database_connection(), $_POST);
                header('Location: skills.php?saved=1');
                exit;
            }
            if ($action === 'update' && is_string($_POST['id'] ?? null)) {
                update_skill(get_database_connection(), $_POST['id'], $_POST);
                header('Location: skills.php?updated=1');
                exit;
            }
            if ($action !== 'delete' || !is_string($_POST['id'] ?? null)) {
                throw new InvalidArgumentException('Choose a valid action.');
            }
            if (!delete_skill(get_database_connection(), $_POST['id'])) {
                throw new InvalidArgumentException('Choose an existing skill.');
            }
            header('Location: skills.php?deleted=1');
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
            if (($_POST['action'] ?? '') === 'update') {
                $editSkill = $_POST;
            }
        } catch (PDOException $error) {
            error_log('Admin skill save database error: ' . $error->getMessage());
            $errorMessage = 'The skill could not be saved.';
        } catch (RuntimeException $error) {
            error_log('Admin skill save setup error: ' . $error->getMessage());
            $errorMessage = 'The skill could not be saved.';
        }
    }
}
try {
    $skills = get_portfolio_items(get_database_connection(), 'skills');
    $editId = $_GET['id'] ?? null;
    if ($editSkill === [] && $editId !== null) {
        if (!is_string($editId)) {
            throw new InvalidArgumentException('Choose a valid skill.');
        }
        foreach ($skills as $skill) {
            if ((string) $skill['id'] === $editId) {
                $editSkill = $skill;
                break;
            }
        }
        if ($editSkill === []) {
            throw new InvalidArgumentException('The skill was not found.');
        }
    }
} catch (PDOException $error) {
    error_log('Admin skills database error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Skills are temporarily unavailable.';
} catch (RuntimeException $error) {
    error_log('Admin skills setup error: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Skills are temporarily unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Skills</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <h1>Manage Skills</h1>
    <a class="back-link" href="dashboard.php">Back to dashboard</a>
    <?php if ($successMessage !== ''): ?><p role="status"><?= escape_html($successMessage) ?></p><?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
      <p class="admin-message" role="alert"><?= escape_html($errorMessage) ?></p>
    <?php elseif (!$skills): ?>
      <p>No skills have been added yet.</p>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
      <input type="hidden" name="action" value="<?= $editSkill ? 'update' : 'create' ?>">
      <?php if ($editSkill): ?><input type="hidden" name="id" value="<?= (int) $editSkill['id'] ?>"><?php endif; ?>
      <label>Skill name <input name="name" maxlength="100" value="<?= escape_html((string) ($editSkill['name'] ?? '')) ?>" required></label>
      <label>Category <input name="category" maxlength="100" value="<?= escape_html((string) ($editSkill['category'] ?? '')) ?>" required></label>
      <label>Proficiency (optional) <input name="proficiency" type="number" min="0" max="100" value="<?= escape_html((string) ($editSkill['proficiency'] ?? '')) ?>"></label>
      <button type="submit"><?= $editSkill ? 'Save skill' : 'Add skill' ?></button>
      <?php if ($editSkill): ?><a href="skills.php">Cancel</a><?php endif; ?>
    </form>
    <?php foreach ($skills as $skill): ?>
      <article class="stat-card">
        <h2><?= escape_html($skill['name']) ?></h2>
        <p><?= escape_html($skill['category']) ?>
          <?php if ($skill['proficiency'] !== null): ?> · <?= (int) $skill['proficiency'] ?>%<?php endif; ?>
        </p>
        <a href="skills.php?id=<?= (int) $skill['id'] ?>">Edit</a>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= escape_html(get_csrf_token()) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $skill['id'] ?>">
          <button type="submit">Delete skill</button>
        </form>
      </article>
    <?php endforeach; ?>
  </main>
</body>
</html>
