-- Migration 005: Add Password Reset Columns to Users and Admins
ALTER TABLE `users` ADD COLUMN `reset_code` VARCHAR(20) NULL AFTER `activation_token`, ADD COLUMN `reset_expires_at` DATETIME NULL AFTER `reset_code`;
ALTER TABLE `admins` ADD COLUMN `reset_code` VARCHAR(20) NULL AFTER `password_hash`, ADD COLUMN `reset_expires_at` DATETIME NULL AFTER `reset_code`;
