<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

try {
    $connection = get_database_connection();
    $profileStatement = $connection->prepare(
        'SELECT full_name, headline, introduction, email, facebook_url, instagram_url, profile_image
         FROM profile WHERE id = 1 LIMIT 1'
    );
    $profileStatement->execute();
    $profile = $profileStatement->fetch();
} catch (Throwable $error) {
    error_log('Failed to load portfolio profile: ' . $error->getMessage());
    $profile = null;
}

$profile = $profile ?: [
    'full_name' => 'Daniela Mae Estrada Maro',
    'headline' => 'IT Student and Aspiring UI/UX Designer',
    'introduction' => 'I am an IT student aspiring to become a UI/UX designer.',
    'email' => 'nielamae959@gmail.com',
    'facebook_url' => 'https://www.facebook.com/daniela.542435',
    'instagram_url' => 'https://www.instagram.com/da.niela_m',
    'profile_image' => 'images/profile.jpg',
];

$fullName = escape_html((string) ($profile['full_name'] ?? 'Daniela Mae Estrada Maro'));
$headline = escape_html((string) ($profile['headline'] ?? 'IT Student and Aspiring UI/UX Designer'));
$introduction = escape_html((string) ($profile['introduction'] ?? 'I am an IT student aspiring to become a UI/UX designer.'));
$email = escape_html((string) ($profile['email'] ?? 'nielamae959@gmail.com'));
$facebookUrl = escape_html((string) ($profile['facebook_url'] ?? 'https://www.facebook.com/daniela.542435'));
$instagramUrl = escape_html((string) ($profile['instagram_url'] ?? 'https://www.instagram.com/da.niela_m'));
$profileImage = escape_html((string) ($profile['profile_image'] ?? 'images/profile.jpg'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $fullName ?> | IT Student Portfolio</title>

  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/" />
  <meta property="og:title" content="<?= $fullName ?> | IT Student Portfolio" />
  <meta property="og:description" content="<?= $introduction ?>" />
  <meta property="og:image" content="https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/images/profile.jpg" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= $fullName ?> | IT Student Portfolio" />
  <meta name="twitter:description" content="<?= $introduction ?>" />
  <meta name="twitter:image" content="https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/images/profile.jpg" />

  <meta name="description" content="Personal portfolio of <?= $fullName ?> — IT student and aspiring UI/UX designer." />

  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
</head>
<body>
  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Dani<span>ela</span></a>
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

  <section class="hero">
    <div class="hero-content">
      <p class="greeting">Hello, I'm</p>
      <h1><?= $fullName ?></h1>
      <h2 class="typing-container">
        <span id="typed-text"></span><span class="cursor">|</span>
      </h2>
      <p class="hero-desc">
        <?= $introduction ?>
      </p>
      <div class="hero-buttons">
        <a href="projects.html" class="btn btn-primary">View My Projects</a>
        <a href="contact.html" class="btn btn-outline">Contact Me</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="<?= $profileImage ?>" alt="<?= $fullName ?>" />
    </div>
  </section>

  <section class="highlights">
    <h2 class="section-title">What I Do</h2>
    <div class="highlight-grid">
      <div class="card">
        <h3>UI/UX Design</h3>
        <p>Designing simple and beautiful interfaces using Figma &amp; Canva.</p>
      </div>
      <div class="card">
        <h3>Web Development</h3>
        <p>Building responsive websites with HTML, CSS, and JavaScript.</p>
      </div>
      <div class="card">
        <h3>Database Basics</h3>
        <p>Working with MySQL for structured data storage and queries.</p>
      </div>
    </div>
  </section>

  <footer class="footer">
    <p>© 2026 <?= $fullName ?>. All rights reserved.</p>
    <div class="socials">
      <a href="<?= $facebookUrl ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
      <a href="<?= $instagramUrl ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
      <a href="mailto:<?= $email ?>">Email</a>
    </div>
  </footer>

  <button id="scrollTop" class="scroll-top" aria-label="Scroll to top">↑</button>

  <script src="js/script.js"></script>
</body>
</html>
