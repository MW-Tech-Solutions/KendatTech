-- Migration: 004_create_team_members.sql
-- Create team_members table and seed initial team members

CREATE TABLE IF NOT EXISTS `team_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `role_title` VARCHAR(150) NOT NULL,
    `specialties` VARCHAR(255) DEFAULT NULL,
    `photo` VARCHAR(255) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active', 'hidden') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_team_status_sort` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default team members if table is empty
INSERT INTO `team_members` (`name`, `role_title`, `specialties`, `bio`, `photo`, `sort_order`, `status`)
SELECT 'Engr. Muhammad Mukhtar', 'Chief Executive Officer', 'LEADERSHIP • STRATEGY • IMPACT', 'Executive leader & chief software engineer directing enterprise AI solutions, cloud architecture, and digital transformation for institutions and businesses across Africa and globally.', NULL, 1, 'active'
FROM DUAL WHERE NOT EXISTS (SELECT * FROM `team_members` WHERE `role_title` = 'Chief Executive Officer' LIMIT 1);

INSERT INTO `team_members` (`name`, `role_title`, `specialties`, `bio`, `photo`, `sort_order`, `status`)
SELECT 'Lead Software Architect', 'Lead Developer', 'SOFTWARE • AI SOLUTIONS • INNOVATION', 'Principal software architect specializing in full-stack web portals, scalable PHP engines, microservices, and AI workflow automation.', NULL, 2, 'active'
FROM DUAL WHERE NOT EXISTS (SELECT * FROM `team_members` WHERE `specialties` LIKE '%SOFTWARE • AI%' LIMIT 1);

INSERT INTO `team_members` (`name`, `role_title`, `specialties`, `bio`, `photo`, `sort_order`, `status`)
SELECT 'Lead Systems Engineer', 'Lead Developer', 'SYSTEMS • CLOUD • DIGITAL SOLUTIONS', 'Lead systems engineer focusing on cloud infrastructure, database optimization, high-availability security systems, and enterprise portals.', NULL, 3, 'active'
FROM DUAL WHERE NOT EXISTS (SELECT * FROM `team_members` WHERE `specialties` LIKE '%SYSTEMS • CLOUD%' LIMIT 1);
