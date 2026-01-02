const pool = require('../config/db');

// Get user notifications
exports.getNotifications = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;
        const [notifications] = await pool.query(
            `SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 20`,
            [userId]
        );

        res.json({ success: true, notifications });
    } catch (error) {
        console.error('Get Notifications Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Mark notification as read
exports.markAsRead = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { id } = req.params;
        const userId = req.session.user.id;

        await pool.query(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?',
            [id, userId]
        );

        res.json({ success: true, message: 'Notification marked as read' });
    } catch (error) {
        console.error('Mark as Read Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Mark all notifications as read
exports.markAllAsRead = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;

        await pool.query(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ?',
            [userId]
        );

        res.json({ success: true, message: 'All notifications marked as read' });
    } catch (error) {
        console.error('Mark All as Read Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Delete notification
exports.deleteNotification = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { id } = req.params;
        const userId = req.session.user.id;

        await pool.query(
            'DELETE FROM notifications WHERE id = ? AND user_id = ?',
            [id, userId]
        );

        res.json({ success: true, message: 'Notification deleted' });
    } catch (error) {
        console.error('Delete Notification Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Create notification (helper function for internal use)
exports.createNotification = async (userId, type, title, message, link = null) => {
    try {
        await pool.query(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)',
            [userId, type, title, message, link]
        );
        return true;
    } catch (error) {
        console.error('Create Notification Error:', error);
        return false;
    }
};

module.exports = exports;
