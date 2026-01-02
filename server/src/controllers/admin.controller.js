const pool = require('../config/db');

exports.getStats = async (req, res) => {
    try {
        const [movieCount] = await pool.query('SELECT COUNT(*) as count FROM movies');
        const [userCount] = await pool.query('SELECT COUNT(*) as count FROM users');
        const [adminCount] = await pool.query('SELECT COUNT(*) as count FROM users WHERE role = "admin"');
        const [historyCount] = await pool.query('SELECT COUNT(*) as count FROM history');

        // Calculate Revenue from completed transactions
        const [revenueData] = await pool.query('SELECT SUM(amount) as total FROM transactions WHERE status = "completed"');

        // Count Premium Users (status is premium and expiry > now)
        const [premiumData] = await pool.query(
            "SELECT COUNT(*) as count FROM users WHERE subscription_status = 'premium' AND subscription_expiry > NOW()"
        );

        const stats = {
            total_movies: movieCount[0].count,
            total_users: userCount[0].count,
            watch_count: historyCount[0].count,
            revenue: revenueData[0].total || 0,
            premium_users: premiumData[0].count,
            admins: adminCount[0].count
        };

        res.json({ success: true, stats });
    } catch (err) {
        console.error('Admin Stats Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.getAllUsers = async (req, res) => {
    try {
        const [users] = await pool.query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
        res.json({ success: true, users });
    } catch (err) {
        console.error('Fetch Users Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.updateUser = async (req, res) => {
    try {
        const { username, email, role } = req.body;
        await pool.query(
            'UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?',
            [username, email, role, req.params.id]
        );
        res.json({ success: true, message: 'User updated successfully' });
    } catch (err) {
        console.error('Update User Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.deleteUser = async (req, res) => {
    try {
        await pool.query('DELETE FROM users WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'User deleted successfully' });
    } catch (err) {
        console.error('Delete User Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.addMovie = async (req, res) => {
    try {
        const { title, description, actor, genre, year } = req.body;
        const posterFile = req.files['poster'] ? req.files['poster'][0] : null;
        const videoFile = req.files['video'] ? req.files['video'][0] : null;

        if (!title || !videoFile) {
            return res.status(400).json({ success: false, message: 'Title and Video file are required' });
        }

        // Generate URLs (relative to server root /uploads route)
        // Since we serve /uploads in app.js, the URL client uses is /uploads/...
        const posterUrl = posterFile ? `/uploads/posters/${posterFile.filename}` : '';
        const videoUrl = videoFile ? `/uploads/videos/${videoFile.filename}` : '';

        const [result] = await pool.query(
            'INSERT INTO movies (title, description, actor, genre, year, poster, video) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [title, description, actor, genre, year, posterUrl, videoUrl]
        );

        res.json({ success: true, message: 'Movie added successfully', movieId: result.insertId });

    } catch (err) {
        console.error('Add Movie Error:', err);
        res.status(500).json({ success: false, message: 'Server error: ' + err.message });
    }
};

exports.updateMovie = async (req, res) => {
    try {
        const { title, description, actor, genre, year } = req.body;
        await pool.query(
            'UPDATE movies SET title = ?, description = ?, actor = ?, genre = ?, year = ? WHERE id = ?',
            [title, description, actor, genre, year, req.params.id]
        );
        res.json({ success: true, message: 'Movie updated successfully' });
    } catch (err) {
        console.error('Update Movie Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.deleteMovie = async (req, res) => {
    try {
        const [movies] = await pool.query('SELECT poster, video FROM movies WHERE id = ?', [req.params.id]);
        if (movies.length > 0) {
            // Optional: Delete files from disk here using fs.unlink
        }

        await pool.query('DELETE FROM movies WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'Movie deleted successfully' });
    } catch (err) {
        console.error('Delete Movie Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Create notification (for admin simulator)
exports.createNotification = async (req, res) => {
    try {
        const { user_id, type, title, message, link } = req.body;

        if (!user_id || !type || !title || !message) {
            return res.status(400).json({ success: false, message: 'Missing required fields' });
        }

        await pool.query(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)',
            [user_id, type, title, message, link || null]
        );

        res.json({ success: true, message: 'Notification created successfully' });
    } catch (err) {
        console.error('Create Notification Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};
