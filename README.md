# Daniela Mae Estrada Maro | Personal Portfolio

A responsive personal portfolio website built with HTML, CSS, and JavaScript. The midterm version is static; the final project is being extended with a PHP and MySQL backend for local development in XAMPP.

🔗 **Live Website:** [https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/](https://nielamae9.github.io/My-ITS-300-personal-portfolio-website/)

---

## 🌸 About

I am Daniela Mae Estrada Maro, a 3rd-year Bachelor of Science in Information Technology student at Negros Oriental State University – Bayawan-Sta. Catalina Campus. I am passionate about design and technology, and I aim to become a professional UI/UX Designer.

---

## 📑 Pages

| Page | Description |
|------|-------------|
| **Home** | Introduction, profile image, typing animation, and call-to-action buttons |
| **About** | Background, education, goals, and interests |
| **Skills** | Technical skills with animated bars and tools grid |
| **Projects** | 4 featured projects with images, descriptions, and technologies used |
| **Resume** | Education timeline, experience, activities, and certifications |
| **Contact** | Email, social links, and a validated contact form |

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

---

## 🛠️ Technologies Used

- **HTML5** — structure and content
- **CSS3** — styling with Flexbox and Grid
- **JavaScript (Vanilla)** — interactivity and features
- **GitHub Pages** — free hosting

---

## 📁 Folder Structure
My-ITS-300-personal-portfolio-website/
├── index.html # Home page
├── about.html # About page
├── skills.html # Skills page
├── projects.html # Projects page
├── resume.html # Resume page
├── contact.html # Contact page
├── README.md # This file
├── css/
│ └── style.css # All styles
├── js/
│ └── script.js # All JavaScript features
└── images/
├── profile.jpg
├── project1.jpg
├── project2.jpg
├── project3.jpg
└── project4.jpg

---

## 🚀 Run the Midterm Website Locally

1. Clone or download this repository.
2. Open `index.html` in any modern web browser.
3. That's it — no installation or build step needed!

## 🧰 PHP and MySQL Development Setup

The dynamic version is developed locally with XAMPP. GitHub Pages serves static files only, so it cannot run the PHP application or connect to its MySQL database.

1. Install and start Apache and MySQL in XAMPP.
2. In phpMyAdmin, import `database/schema.sql`. It creates the `personal_portfolio` database and inserts the existing portfolio content as starter data.
3. Copy `includes/config.example.php` to `includes/config.local.php` and set the database username and password for your local MySQL installation. This local file is ignored by Git.
4. Open the project through Apache, for example `http://localhost/My-ITS-300-personal-portfolio-website/`.

The PHP connection helper is in `includes/database.php`. The public pages and admin dashboard are still being migrated; this setup currently adds the database foundation only. Do not put database credentials in tracked files.

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
