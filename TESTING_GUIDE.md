# Quick Testing Guide

## Test the Notification System

### 1. Add Sample Notifications
Run this SQL command in your database:

```sql
-- Add notifications for user ID 1 (change to your user ID)
INSERT INTO notifications (user_id, type, title, message, link) VALUES
(1, 'welcome', 'Welcome to MovieStream!', 'Start exploring our vast collection of movies.', '/'),
(1, 'movie', 'New Movie Added', 'Check out the latest blockbuster!', '/movies/1'),
(1, 'system', 'System Update', 'We have improved the video player.', null);
```

### 2. View Notifications
1. Login to your account
2. Look at the top-right navbar
3. Click the bell icon 🔔
4. You should see the notification dropdown with your notifications
5. Click "Mark as read" or delete notifications

## Test the Payment System

### 1. Mark a Movie as Premium
```sql
-- Make movie ID 1 premium
UPDATE movies SET is_premium = 1 WHERE id = 1;
```

### 2. Test as Free User
1. Make sure you're logged in
2. Navigate to the premium movie (e.g., `/movies/1`)
3. You should see a locked overlay with "Premium Content" message
4. Click "Upgrade to Premium" button
5. You'll be redirected to your profile page

### 3. Test Subscription Purchase
1. On your profile page, scroll down to the "Premium Subscription" section
2. Click "Subscribe Now" button
3. You'll be redirected to Chapa payment page
4. Complete the payment (use test credentials if in test mode)
5. After payment, you'll be redirected back to your profile
6. You should see a success message
7. The subscription card should now show "Premium Member" status

### 4. Test Premium Access
1. After subscribing, navigate to the premium movie again
2. The video should now play without the locked overlay
3. You should see a "PREMIUM" badge next to the movie title

### 5. Test as Admin
1. Login as admin user
2. Navigate to Admin → Payments
3. You should see all transactions in the system
4. Check transaction status, amounts, and user details

## Manual Subscription Grant (For Testing)

If you want to manually grant premium access without payment:

```sql
-- Grant 30 days premium to user ID 1
UPDATE users 
SET subscription_status = 'premium', 
    subscription_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY)
WHERE id = 1;
```

## Check Subscription Status

```sql
-- Check user subscription
SELECT id, username, subscription_status, subscription_expiry 
FROM users 
WHERE id = 1;
```

## View All Notifications

```sql
-- View all notifications for user ID 1
SELECT * FROM notifications WHERE user_id = 1 ORDER BY created_at DESC;
```

## View All Transactions

```sql
-- View all transactions
SELECT t.*, u.username, u.email 
FROM transactions t 
JOIN users u ON t.user_id = u.id 
ORDER BY t.created_at DESC;
```

## Test Notification Types

```sql
-- Payment notification
INSERT INTO notifications (user_id, type, title, message, link) 
VALUES (1, 'payment', 'Payment Successful!', 'Your premium subscription is now active.', '/profile');

-- Movie notification
INSERT INTO notifications (user_id, type, title, message, link) 
VALUES (1, 'movie', 'New Release!', 'The Dark Knight is now available.', '/movies/2');

-- System notification
INSERT INTO notifications (user_id, type, title, message) 
VALUES (1, 'system', 'Maintenance Notice', 'Scheduled maintenance on Sunday at 2 AM.');
```

## Expected Behavior

### Notifications
- ✅ Bell icon shows unread count
- ✅ Dropdown opens on click
- ✅ Notifications sorted by newest first
- ✅ Different icons for different types
- ✅ Can mark as read
- ✅ Can delete notifications
- ✅ Auto-refreshes every 30 seconds

### Premium Subscription
- ✅ Free users see upgrade card
- ✅ Premium users see status and expiry
- ✅ Warning shown when < 7 days remaining
- ✅ Payment redirects to Chapa
- ✅ Success message after payment
- ✅ Subscription status updates automatically

### Premium Movies
- ✅ Premium badge shown on title
- ✅ Free users see locked overlay
- ✅ Premium users can watch
- ✅ Upgrade button redirects to profile
- ✅ Video player disabled for non-premium users

## Troubleshooting

### Notifications not showing?
1. Check if notifications table exists
2. Check if user_id matches logged-in user
3. Check browser console for errors
4. Verify API endpoint is accessible

### Payment not working?
1. Check Chapa API key in .env
2. Verify transactions table exists
3. Check browser console for errors
4. Ensure axios is installed in server

### Premium access not working?
1. Check if is_premium column exists in movies table
2. Verify subscription_status and subscription_expiry in users table
3. Check if subscription_expiry is in the future
4. Clear browser cache and reload

## Quick Reset

To reset everything for testing:

```sql
-- Clear all notifications
DELETE FROM notifications;

-- Clear all transactions
DELETE FROM transactions;

-- Reset all users to free
UPDATE users SET subscription_status = 'free', subscription_expiry = NULL;

-- Reset all movies to free
UPDATE movies SET is_premium = 0;
```
