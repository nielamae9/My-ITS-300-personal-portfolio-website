<?php

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';

require_admin();

try {
    $connection = get_database_connection();
    $statistics = [
        'Projects' => (int) $connection->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
        'Skills' => (int) $connection->query('SELECT COUNT(*) FROM skills')->fetchColumn(),
        'Messages' => (int) $connection->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
        'Experience items' => (int) $connection->query('SELECT COUNT(*) FROM experience')->fetchColumn(),
    ];
} catch (PDOException $error) {
    error_log('Admin dashboard database error: ' . $error->getMessage());
    http_response_code(503);
    exit('The dashboard is temporarily unavailable. Please try again later.');
} catch (RuntimeException $error) {
    error_log('Admin dashboard setup error: ' . $error->getMessage());
    http_response_code(503);
    exit('The dashboard is temporarily unavailable. Please try again later.');
}

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
  <main class="admin-dashboard">
    <header class="dashboard-header">
      <div>
        <h1>Admin Dashboard</h1>
        <p>Signed in as <?= escape_html((string) $_SESSION['admin_email']) ?></p>
      </div>
      <form method="post" action="logout.php">
        <input type="hidden" name="csrf_token" value="<?= escape_html($csrfToken) ?>">
        <button class="btn btn-outline" type="submit">Log out</button>
      </form>
    </header>

    <h2>Website statistics</h2>
    <section class="statistics-grid" aria-label="Website statistics">
      <?php foreach ($statistics as $label => $count): ?>
        <article class="stat-card">
          <h3><?= escape_html($label) ?></h3>
          <p><?= $count ?></p>
        </article>
      <?php endforeach; ?>
    </section>

    <p class="dashboard-note">Content management tools will be added in the next development steps.</p>
    <a class="back-link" href="../index.html">View portfolio</a>
  </main>
</body>
</html>
