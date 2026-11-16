CREATE DATABASE IF NOT EXISTS personal_portfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE personal_portfolio;

CREATE TABLE IF NOT EXISTS profile (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    headline VARCHAR(200) NOT NULL,
    introduction TEXT NOT NULL,
    biography TEXT NOT NULL,
    email VARCHAR(254) NOT NULL,
    facebook_url VARCHAR(255) NOT NULL DEFAULT '',
    instagram_url VARCHAR(255) NOT NULL DEFAULT '',
    profile_image VARCHAR(255) NOT NULL DEFAULT 'images/profile.jpg',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    proficiency TINYINT UNSIGNED DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80) NOT NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    technologies VARCHAR(500) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS education (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    school VARCHAR(180) NOT NULL,
    program VARCHAR(180) NOT NULL,
    start_year SMALLINT UNSIGNED DEFAULT NULL,
    end_year SMALLINT UNSIGNED DEFAULT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS experience (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    organization VARCHAR(180) NOT NULL DEFAULT '',
    date_label VARCHAR(100) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    date_label VARCHAR(100) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS website_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO profile (
    id, full_name, headline, introduction, biography, email, facebook_url, instagram_url
) VALUES (
    1,
    'Daniela Mae Estrada Maro',
    'IT Student and Aspiring UI/UX Designer',
    'I am an IT student aspiring to become a UI/UX designer.',
    'I am a Bachelor of Science in Information Technology student at Negros Oriental State University – Bayawan-Sta. Catalina Campus. I enjoy design and technology and creating clean, user-friendly digital experiences.',
    'nielamae959@gmail.com',
    'https://www.facebook.com/daniela.542435',
    'https://www.instagram.com/da.niela_m'
)
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO skills (name, category, proficiency, sort_order) VALUES
    ('HTML', 'Technical', 85, 1),
    ('CSS', 'Technical', 80, 2),
    ('JavaScript', 'Technical', 65, 3),
    ('C++', 'Technical', 70, 4),
    ('PHP', 'Technical', 60, 5),
    ('MySQL', 'Technical', 70, 6),
    ('Canva', 'Tools', NULL, 7),
    ('Adobe Illustrator', 'Tools', NULL, 8),
    ('Adobe Animate', 'Tools', NULL, 9),
    ('Cisco Packet Tracer', 'Tools', NULL, 10),
    ('Microsoft Word', 'Tools', NULL, 11),
    ('Microsoft Excel', 'Tools', NULL, 12),
    ('Microsoft PowerPoint', 'Tools', NULL, 13),
    ('MySQL Workbench', 'Tools', NULL, 14);

INSERT INTO projects (title, description, category, image_path, technologies, sort_order) VALUES
    ('Personal Portfolio Website', 'A responsive portfolio built with HTML, CSS, and JavaScript featuring dark/light mode and form validation.', 'Web', 'images/project1.jpg', 'HTML, CSS, JavaScript', 1),
    ('Mobile App UI Concept', 'A student planner app mockup created in Canva, focusing on simplicity and accessibility.', 'Design', 'images/project2.jpg', 'Canva, UI Design', 2),
    ('Student Record System', 'A MySQL-based database system for storing and retrieving student information with basic CRUD operations.', 'Database', 'images/project3.jpg', 'MySQL, PHP', 3),
    ('JavaScript Calculator', 'An interactive calculator that performs basic arithmetic operations.', 'Web', 'images/project4.jpg', 'HTML, CSS, JavaScript', 4);

INSERT INTO education (school, program, start_year, end_year, description, sort_order) VALUES
    ('Negros Oriental State University – Bayawan-Sta. Catalina Campus', 'Bachelor of Science in Information Technology', 2024, 2028, '', 1),
    ('Bayawan National High School', 'Senior High School', 2022, 2024, '', 2),
    ('Bayawan National High School', 'Junior High School', 2018, 2022, '', 3),
    ('Holy Family Elementary School', 'Elementary', 2012, 2018, '', 4);

INSERT INTO experience (title, organization, date_label, description, sort_order) VALUES
    ('Part-time Tutor', '', 'Ongoing', 'Helping students with basic subjects and computer literacy.', 1),
    ('Volunteer', 'NGO Protecting Children', 'Community Activity', 'Participated in activities that support and protect children''s welfare.', 2);

INSERT INTO achievements (title, description, date_label, sort_order) VALUES
    ('Certificate of Participation — NGO Children''s Program', 'Received a certificate for active participation in a non-government organization program protecting children.', '', 1),
    ('Certificate of Recognition — Academic Activities', 'Recognized for involvement in school-based IT and academic activities.', '', 2);

INSERT INTO website_settings (setting_key, setting_value) VALUES
    ('site_title', 'Daniela Mae Estrada Maro | IT Student Portfolio'),
    ('footer_text', '© 2026 Daniela Mae Estrada Maro. All rights reserved.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
