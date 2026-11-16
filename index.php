<?php

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

try {
    $connection = get_database_connection();
    $profile = $connection->query('SELECT * FROM profile WHERE id = 1')->fetch();

    if (!$profile) {
        throw new RuntimeException('The portfolio profile is missing.');
    }

    $projectQuery = $connection->query(
        'SELECT title, description, image_path, technologies
         FROM projects
         ORDER BY sort_order, id DESC
         LIMIT 3'
    );
    $projects = $projectQuery->fetchAll();

    $settingsQuery = $connection->prepare(
        'SELECT setting_key, setting_value FROM website_settings
         WHERE setting_key IN (:title_key, :footer_key)'
    );
    $settingsQuery->execute([
        'title_key' => 'site_title',
        'footer_key' => 'footer_text',
    ]);

    $settings = [];
    foreach ($settingsQuery->fetchAll() as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    if (!isset($settings['site_title'], $settings['footer_text'])) {
        throw new RuntimeException('Portfolio settings are missing.');
    }
} catch (PDOException $error) {
    error_log('Portfolio home database error: ' . $error->getMessage());
    http_response_code(503);
    exit('The portfolio is temporarily unavailable. Please try again later.');
} catch (RuntimeException $error) {
    error_log('Portfolio home setup error: ' . $error->getMessage());
    http_response_code(503);
    exit('The portfolio is temporarily unavailable. Please try again later.');
}

function safe_external_url(string $url): string
{
    $scheme = parse_url($url, PHP_URL_SCHEME);

    if (!filter_var($url, FILTER_VALIDATE_URL)
        || !is_string($scheme)
        || !in_array(strtolower($scheme), ['http', 'https'], true)
    ) {
        return '';
    }

    return escape_html($url);
}

$facebookUrl = safe_external_url($profile['facebook_url']);
$instagramUrl = safe_external_url($profile['instagram_url']);
$email = filter_var($profile['email'], FILTER_VALIDATE_EMAIL)
    ? escape_html($profile['email'])
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= escape_html($settings['site_title']) ?></title>
  <meta name="description" content="<?= escape_html($profile['introduction']) ?>">
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo"><?= escape_html($profile['full_name']) ?></a>
      <nav>
        <ul class="nav-menu" id="navMenu">
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="about.html">About</a></li>
          <li><a href="skills.html">Skills</a></li>
          <li><a href="projects.html">Projects</a></li>
          <li><a href="resume.html">Resume</a></li>
          <li><a href="contact.html">Contact</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" aria-label="Toggle theme">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="hero-content">
        <p class="greeting">Hello, I'm</p>
        <h1><?= escape_html($profile['full_name']) ?></h1>
        <h2><?= escape_html($profile['headline']) ?></h2>
        <p class="hero-desc"><?= escape_html($profile['introduction']) ?></p>
        <div class="hero-buttons">
          <a href="projects.html" class="btn btn-primary">View My Projects</a>
          <a href="contact.html" class="btn btn-outline">Contact Me</a>
        </div>
      </div>
      <div class="hero-image">
        <img src="<?= escape_html($profile['profile_image']) ?>"
             alt="<?= escape_html($profile['full_name']) ?>">
      </div>
    </section>

    <section class="projects-section">
      <h2 class="section-title">Featured Projects</h2>
      <div class="projects-grid">
        <?php if (count($projects) === 0): ?>
          <p>No projects have been added yet.</p>
        <?php else: ?>
          <?php foreach ($projects as $project): ?>
            <article class="project-card">
              <?php if ($project['image_path'] !== ''): ?>
                <img class="project-img"
                     src="<?= escape_html($project['image_path']) ?>"
                     alt="<?= escape_html($project['title']) ?>">
              <?php endif; ?>
              <div class="project-info">
                <h3><?= escape_html($project['title']) ?></h3>
                <p><?= escape_html($project['description']) ?></p>
                <?php if (trim($project['technologies']) !== ''): ?>
                  <div class="tech-tags">
                    <?php foreach (explode(',', $project['technologies']) as $technology): ?>
                      <?php if (trim($technology) !== ''): ?>
                        <span><?= escape_html(trim($technology)) ?></span>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    <p><?= escape_html($settings['footer_text']) ?></p>
    <div class="socials">
      <?php if ($facebookUrl !== ''): ?>
        <a href="<?= $facebookUrl ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
      <?php endif; ?>
      <?php if ($instagramUrl !== ''): ?>
        <a href="<?= $instagramUrl ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
      <?php endif; ?>
      <?php if ($email !== ''): ?>
        <a href="mailto:<?= $email ?>">Email</a>
      <?php endif; ?>
    </div>
  </footer>

  <button id="scrollTop" class="scroll-top" aria-label="Scroll to top">↑</button>
  <script src="js/script.js"></script>
</body>
</html>
