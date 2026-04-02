const express = require('express');
const router = express.Router();
const pool = require('../config/db');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
// Ensure upload directories exist
const uploadDir = path.join(__dirname, '../../uploads');
const postersDir = path.join(uploadDir, 'posters');
const videosDir = path.join(uploadDir, 'videos');

[uploadDir, postersDir, videosDir].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
});

// Configure Multer Storage
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        if (file.fieldname === 'poster') {
            cb(null, postersDir);
        } else if (file.fieldname === 'video') {
            cb(null, videosDir);
        } else {
            cb(null, uploadDir);
        }
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({ storage: storage });

// Get Dashboard Stats
router.get('/stats', async (req, res) => {
    try {
        const [movieCount] = await pool.query('SELECT COUNT(*) as count FROM movies');
        const [userCount] = await pool.query('SELECT COUNT(*) as count FROM users');
        const [adminCount] = await pool.query('SELECT COUNT(*) as count FROM users WHERE role = "admin"');
        const [historyCount] = await pool.query('SELECT COUNT(*) as count FROM history');

        // Mock revenue/premium for now as schema might not have it
        const stats = {
            total_movies: movieCount[0].count,
            total_users: userCount[0].count,
            watch_count: historyCount[0].count,
            revenue: 0,
            premium_users: 0,
            admins: adminCount[0].count
        };

        res.json({ success: true, stats });
    } catch (err) {
        console.error('Admin Stats Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

// Get All Users
router.get('/users', async (req, res) => {
    try {
        const [users] = await pool.query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
        res.json({ success: true, users });
    } catch (err) {
        console.error('Fetch Users Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

// Update User
router.put('/users/:id', async (req, res) => {
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
});

// Delete User
router.delete('/users/:id', async (req, res) => {
    try {
        await pool.query('DELETE FROM users WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'User deleted successfully' });
    } catch (err) {
        console.error('Delete User Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

// Add New Movie
router.post('/movies', upload.fields([{ name: 'poster', maxCount: 1 }, { name: 'video', maxCount: 1 }]), async (req, res) => {
    try {
        const { title, description, actor, genre, year } = req.body;
        const posterFile = req.files['poster'] ? req.files['poster'][0] : null;
        const videoFile = req.files['video'] ? req.files['video'][0] : null;

        if (!title || !videoFile) {
            return res.status(400).json({ success: false, message: 'Title and Video file are required' });
        }

        // Generate URLs (relative to server root /uploads route)
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
});

// Update Movie (Metadata only for now, poster/video can be separate or handled if needed)
router.put('/movies/:id', async (req, res) => {
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
});

// Delete Movie
router.delete('/movies/:id', async (req, res) => {
    try {
        // Fetch files to delete if we want to be thorough
        const [movies] = await pool.query('SELECT poster, video FROM movies WHERE id = ?', [req.params.id]);
        if (movies.length > 0) {
            const movie = movies[0];
            // Simple deletion of record for now, file cleanup could be added
        }

        await pool.query('DELETE FROM movies WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'Movie deleted successfully' });
    } catch (err) {
        console.error('Delete Movie Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

module.exports = router;
