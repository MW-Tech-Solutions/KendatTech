-- Migration: 006_create_hero_slides.sql
-- Create hero_slides table for dynamic software development hero carousel

CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `badge_text` VARCHAR(150) NOT NULL DEFAULT 'INNOVATIVE TECHNOLOGY SOLUTIONS',
    `title` VARCHAR(255) NOT NULL,
    `highlight_text` VARCHAR(120) DEFAULT 'KendatTech',
    `description` TEXT NOT NULL,
    `primary_cta_text` VARCHAR(80) DEFAULT 'Our Services',
    `primary_cta_url` VARCHAR(255) DEFAULT 'services.php',
    `secondary_cta_text` VARCHAR(80) DEFAULT 'Learn More',
    `secondary_cta_url` VARCHAR(255) DEFAULT 'about.php',
    `slide_image` VARCHAR(255) DEFAULT 'assets/images/hero-laptop.png',
    `badge1_icon` VARCHAR(60) DEFAULT 'Code2',
    `badge1_title` VARCHAR(100) DEFAULT 'Clean Code',
    `badge1_subtitle` VARCHAR(120) DEFAULT 'Scalable & Maintainable',
    `badge2_icon` VARCHAR(60) DEFAULT 'ShieldCheck',
    `badge2_title` VARCHAR(100) DEFAULT 'Reliable Security',
    `badge2_subtitle` VARCHAR(120) DEFAULT 'Your Data, Our Priority',
    `tech_tags` VARCHAR(255) DEFAULT 'PHP, React, MySQL, Cloud APIs',
    `sort_order` INT DEFAULT 1,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hero_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default Software Development hero slides if table is empty
INSERT INTO `hero_slides` (
    `badge_text`, `title`, `highlight_text`, `description`,
    `primary_cta_text`, `primary_cta_url`, `secondary_cta_text`, `secondary_cta_url`,
    `slide_image`, `badge1_icon`, `badge1_title`, `badge1_subtitle`,
    `badge2_icon`, `badge2_title`, `badge2_subtitle`, `tech_tags`,
    `sort_order`, `status`
)
SELECT 
    'INNOVATIVE TECHNOLOGY SOLUTIONS',
    'Build Smarter With KendatTech',
    'KendatTech',
    'We design, develop and deliver modern software solutions that help businesses grow, operate efficiently and stay ahead in a digital world.',
    'Our Services',
    'services.php',
    'Learn More',
    'about.php',
    'assets/images/hero-laptop.png',
    'Code2',
    'Clean Code',
    'Scalable & Maintainable',
    'ShieldCheck',
    'Reliable Security',
    'Your Data, Our Priority',
    'PHP, React, MySQL, REST APIs',
    1,
    'active'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `hero_slides` WHERE `sort_order` = 1 LIMIT 1);

INSERT INTO `hero_slides` (
    `badge_text`, `title`, `highlight_text`, `description`,
    `primary_cta_text`, `primary_cta_url`, `secondary_cta_text`, `secondary_cta_url`,
    `slide_image`, `badge1_icon`, `badge1_title`, `badge1_subtitle`,
    `badge2_icon`, `badge2_title`, `badge2_subtitle`, `tech_tags`,
    `sort_order`, `status`
)
SELECT 
    'FULL-STACK SOFTWARE ENGINEERING',
    'Enterprise Web & Mobile App Development',
    'Enterprise Web & Mobile',
    'End-to-end custom software engineering with reactive frontends, resilient databases, and automated business workflows built to scale seamlessly.',
    'Request a Project',
    'request-project.php',
    'View Projects',
    'projects.php',
    'assets/images/services-hero-laptop.jpg',
    'Smartphone',
    'Cross-Platform',
    'iOS, Android & Web',
    'Rocket',
    'Blazing Fast',
    'High Performance Core',
    'React, Next.js, Laravel, Flutter',
    2,
    'active'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `hero_slides` WHERE `sort_order` = 2 LIMIT 1);

INSERT INTO `hero_slides` (
    `badge_text`, `title`, `highlight_text`, `description`,
    `primary_cta_text`, `primary_cta_url`, `secondary_cta_text`, `secondary_cta_url`,
    `slide_image`, `badge1_icon`, `badge1_title`, `badge1_subtitle`,
    `badge2_icon`, `badge2_title`, `badge2_subtitle`, `tech_tags`,
    `sort_order`, `status`
)
SELECT 
    'CLOUD ARCHITECTURE & MICROSERVICES',
    'Scalable Cloud Systems & High-Speed APIs',
    'Cloud Systems & APIs',
    'Architecting resilient cloud backends, optimized databases, and bulletproof microservices capable of processing millions of concurrent transactions.',
    'Book Consultation',
    'book-appointment.php',
    'Explore Services',
    'services.php',
    'assets/images/about-workspace.jpg',
    'Cloud',
    'Cloud Native',
    '99.99% Guaranteed Uptime',
    'Database',
    'Optimized SQL',
    'High-Throughput Engines',
    'AWS, Docker, Microservices, CI/CD',
    3,
    'active'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `hero_slides` WHERE `sort_order` = 3 LIMIT 1);

INSERT INTO `hero_slides` (
    `badge_text`, `title`, `highlight_text`, `description`,
    `primary_cta_text`, `primary_cta_url`, `secondary_cta_text`, `secondary_cta_url`,
    `slide_image`, `badge1_icon`, `badge1_title`, `badge1_subtitle`,
    `badge2_icon`, `badge2_title`, `badge2_subtitle`, `tech_tags`,
    `sort_order`, `status`
)
SELECT 
    'AI & INTELLIGENT AUTOMATION',
    'AI-Powered Systems & Smart Business Automation',
    'AI-Powered Systems',
    'Empower your applications with artificial intelligence, machine learning forecasting, and automated decision bots tailored to accelerate operations.',
    'AI Solutions',
    'ai-solutions.php',
    'Contact Us',
    'contact.php',
    'assets/images/ai-hero-visual.png',
    'Cpu',
    'Intelligent AI',
    'NLP & Machine Learning',
    'Bot',
    'Smart Automation',
    '24/7 Workflow Processing',
    'Python, OpenAI, PyTorch, Automation',
    4,
    'active'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `hero_slides` WHERE `sort_order` = 4 LIMIT 1);
