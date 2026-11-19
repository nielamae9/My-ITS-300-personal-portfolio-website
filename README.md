# Daniela Mae Estrada Maro | Personal Portfolio

A responsive portfolio with the original HTML page URLs and a PHP/MySQL backend for database-driven content and admin management in XAMPP.

🔗 **Live Website:** [https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/](https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/)

---

## 🌸 About

I am Daniela Mae Estrada Maro, a 3rd-year Bachelor of Science in Information Technology student at Negros Oriental State University – Bayawan-Sta. Catalina Campus. I am passionate about design and technology, and I aim to become a professional UI/UX Designer.

---

## 📑 Pages

| Page | Description |
|------|-------------|
| **Home** | PHP homepage with profile information, typing animation, and call-to-action buttons |
| **About** | Profile biography and image |
| **Skills** | Database-driven technical skills and tools |
| **Projects** | Database-driven projects with categories, images, and technologies |
| **Resume** | Database-driven education, experience, and achievements |
| **Contact** | Validated contact form that stores messages for admin review |

---

## ✨ Features

- 🎨 Pink and black theme with dark/light mode toggle (saved via LocalStorage)
- 📱 Fully responsive — works on desktop, tablet, and mobile
- 🍔 Mobile hamburger navigation
- ⌨️ Typing effect on the homepage
- 📊 Animated skill bars
- 🔍 Project filtering by category
- 🖼️ Image modal for project previews
- 📂 Accordion for certifications
- ✅ JavaScript form validation on the contact page
- ⬆️ Scroll-to-top button
- 🔐 PHP admin login, dashboard, and one-time admin setup
- 🔑 Admins can update their login email and password after signing in
- ✏️ Admin editing for profile, website settings, skills, projects, education, experience, and achievements
- 📥 Admin contact-message review, read/unread status, and deletion
- 🖼️ Image uploads validated by file content, size-limited, and stored with random names
- 🛡️ Prepared database queries, CSRF checks, escaped output, and a honeypot on contact submissions

---

## 🛠️ Technologies Used

- **HTML5** — structure and content
- **CSS3** — styling with Flexbox and Grid
- **JavaScript (Vanilla)** — interactivity and features
- **PHP and MySQL** — dynamic pages and admin tools in XAMPP
- **GitHub Pages** — static preview only; it cannot run PHP or connect to MySQL

---

## 📁 Main Project Files

- `index.php` and the existing `.html` pages — dynamic home and static-compatible public page URLs
- `admin/` — login, dashboard, profile/settings, content, and message management
- `api/` — public content and contact-message endpoints
- `database/schema.sql` — database tables and starter content
- `includes/` — database access, validation, security, and upload helpers
- `js/` — public page rendering and interactivity
- `images/` and `uploads/` — bundled and admin-uploaded images

---

## 🚀 Open the Static Preview

1. Clone or download this repository.
2. Open `index.html` in any modern web browser.
3. Static pages keep their existing URLs. PHP endpoints and contact-message submission need Apache and MySQL as described below.

## 🧰 PHP and MySQL Development Setup

The dynamic version runs locally with XAMPP. GitHub Pages serves static files only, so PHP endpoints, database-backed pages, admin tools, uploads, and contact-message storage require a PHP/MySQL host. The original `.html` pages remain available as static previews.

The application requires PHP 8.0 or newer with PDO MySQL and Fileinfo enabled; XAMPP includes these extensions.

1. Install and start Apache and MySQL in XAMPP.
2. In phpMyAdmin, import `database/schema.sql`. It creates the `personal_portfolio` database and inserts the existing portfolio content as starter data.
3. Copy `includes/config.example.php` to `includes/config.local.php` and set the database username and password for your local MySQL installation. This local file is ignored by Git.
4. Open the project through Apache, for example `http://localhost/My-ITS-300-personal-portfolio-website/`.
5. Create the first admin account from a command prompt in the project folder: `C:\xampp\php\php.exe database\create_admin.php nielamae959@gmail.com`. The script generates a random password and displays it once. On hosting without command-line access, use the one-time setup page instead.
6. Open `http://localhost/My-ITS-300-personal-portfolio-website/admin/login.php` and sign in with the generated credentials.
7. Use the dashboard to change the admin login email or password, edit profile and website settings, manage portfolio content, and review contact messages.

For hosted first-admin setup, configure the database in the ignored `includes/config.local.php`, then open `/admin/setup.php` and enter the login email, a strong password, and its confirmation. The setup page creates an account only while the `admins` table is empty; otherwise, it redirects to the login page.

The PHP connection helper is in `includes/database.php`. Keep database credentials in the ignored `includes/config.local.php` file and never commit that file. Uploaded files are restricted by `uploads/.htaccess` on Apache.

---

## 📧 Contact

- **Email:** [nielamae959@gmail.com](mailto:nielamae959@gmail.com)
- **Facebook:** [Daniela Maro](https://www.facebook.com/daniela.542435)
- **Instagram:** [@da.niela_m](https://www.instagram.com/da.niela_m)

---

## 📝 Course Info

- **Course:** ITS 300 – Section B
- **Project:** Midterm Project – Static Personal Portfolio Website
- **School:** Negros Oriental State University – Bayawan-Sta. Catalina Campus

---

© 2026 Daniela Mae Estrada Maro. All rights reserved.
