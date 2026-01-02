-- Add subscription fields to users table if they don't exist
ALTER TABLE users 
ADD COLUMN subscription_status VARCHAR(20) DEFAULT 'free',
ADD COLUMN subscription_expiry DATETIME NULL;

-- Create notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Create transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tx_ref VARCHAR(255) UNIQUE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'chapa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tx_ref (tx_ref),
    INDEX idx_status (status)
);

-- Add premium field to movies table if it doesn't exist
ALTER TABLE movies 
ADD COLUMN is_premium BOOLEAN DEFAULT FALSE;

-- Insert sample notifications for existing users (optional)
INSERT INTO notifications (user_id, type, title, message, link)
SELECT id, 'welcome', 'Welcome to MovieStream!', 'Start exploring our vast collection of movies and shows.', '/'
FROM users
WHERE id NOT IN (SELECT DISTINCT user_id FROM notifications WHERE type = 'welcome')
LIMIT 10;
