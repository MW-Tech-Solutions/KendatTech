-- Migration: 003_add_rate_limits.sql
-- Create persistent rate limits table

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `key_name` VARCHAR(191) NOT NULL PRIMARY KEY,
    `attempts` INT NOT NULL DEFAULT 1,
    `first_attempt` INT NOT NULL,
    `expires_at` INT NOT NULL,
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
