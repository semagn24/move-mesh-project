-- Migration: add subscription fields and premium flag

-- Add a column to the movies table to mark premium content
ALTER TABLE movies ADD COLUMN is_premium TINYINT(1) DEFAULT 0;

-- Update the users table to track subscription status and when it expires
ALTER TABLE users ADD COLUMN subscription_status ENUM('free','premium') DEFAULT 'free';
ALTER TABLE users ADD COLUMN subscription_expiry DATETIME NULL;
