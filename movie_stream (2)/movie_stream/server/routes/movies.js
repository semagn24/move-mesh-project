const express = require('express');
const router = express.Router();
const pool = require('../config/db');

// Get All Movies (supports ?view=trending & ?q=search)
router.get('/', async (req, res) => {
    try {
        const viewMode = req.query.view || 'all';
        const search = req.query.q ? req.query.q.trim() : '';

        if (viewMode === 'trending') {
            const [movies] = await pool.query(`
                SELECT m.*, COUNT(h.movie_id) as view_count 
                FROM movies m 
                INNER JOIN history h ON m.id = h.movie_id 
                WHERE h.watched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY m.id 
                HAVING view_count >= 1
                ORDER BY view_count DESC 
                LIMIT 12
            `);
            const moviesWithUrls = movies.map(movie => ({
                ...movie,
                poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `http://localhost:5000${movie.poster}` : `http://localhost:5000/uploads/posters/${movie.poster}`)) : null,
                video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `http://localhost:5000${movie.video}` : `http://localhost:5000/uploads/videos/${movie.video}`)) : null
            }));
            return res.json({ success: true, data: moviesWithUrls });
        }

        let query = "SELECT * FROM movies WHERE 1=1";
        const params = [];

        if (search) {
            query += " AND (title LIKE ? OR actor LIKE ?)";
            params.push(`%${search}%`, `%${search}%`);
        }

        query += " ORDER BY created_at DESC";

        const [movies] = await pool.query(query, params);
        const moviesWithUrls = movies.map(movie => ({
            ...movie,
            poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `http://localhost:5000${movie.poster}` : `http://localhost:5000/uploads/posters/${movie.poster}`)) : null,
            video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `http://localhost:5000${movie.video}` : `http://localhost:5000/uploads/videos/${movie.video}`)) : null
        }));
        res.json({ success: true, data: moviesWithUrls });

    } catch (err) {
        console.error('Movies List Error:', err);
        res.status(500).json({ message: 'Server error: ' + err.message });
    }
});

// Get Single Movie
router.get('/:id', async (req, res) => {
    try {
        const [movies] = await pool.query("SELECT * FROM movies WHERE id = ?", [req.params.id]);
        if (movies.length === 0) {
            return res.status(404).json({ message: 'Movie not found' });
        }
        const movie = movies[0];
        const movieWithUrls = {
            ...movie,
            poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `http://localhost:5000${movie.poster}` : `http://localhost:5000/uploads/posters/${movie.poster}`)) : null,
            video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `http://localhost:5000${movie.video}` : `http://localhost:5000/uploads/videos/${movie.video}`)) : null
        };

        // Fetch user progress if logged in
        let progress = null;
        if (req.session.user) {
            const [history] = await pool.query("SELECT last_time FROM history WHERE user_id = ? AND movie_id = ?", [req.session.user.id, req.params.id]);
            if (history.length > 0) progress = history[0].last_time;
        }

        res.json({ success: true, data: movieWithUrls, progress });
    } catch (err) {
        console.error('Movie Details Error:', err);
        res.status(500).json({ message: 'Server error' });
    }
});

// Post Comment
router.post('/:id/comment', async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });
    const { comment } = req.body;
    try {
        await pool.query("INSERT INTO comments (user_id, movie_id, comment) VALUES (?, ?, ?)", [req.session.user.id, req.params.id, comment]);
        res.json({ success: true, message: 'Comment posted' });
    } catch (err) {
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

// Get Comments
router.get('/:id/comments', async (req, res) => {
    try {
        const [comments] = await pool.query(`
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.movie_id = ? 
            ORDER BY c.created_at DESC
        `, [req.params.id]);
        res.json({ success: true, data: comments });
    } catch (err) {
        res.status(500).json({ success: false, message: 'Server error' });
    }
});

// Post/Update Watch Progress
router.post('/:id/progress', async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });
    const { last_time } = req.body;
    try {
        await pool.query(`
            INSERT INTO history (user_id, movie_id, last_time, watched_at) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE last_time = ?, watched_at = NOW()
        `, [req.session.user.id, req.params.id, last_time, last_time]);
        res.json({ success: true });
    } catch (err) {
        console.error('Progress Error:', err);
        res.status(500).json({ success: false });
    }
});

// Post Review/Rating
router.post('/:id/review', async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });
    const { rating, comment } = req.body;
    try {
        await pool.query(`
            INSERT INTO reviews (user_id, movie_id, rating, comment) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE rating = ?, comment = ?
        `, [req.session.user.id, req.params.id, rating, comment, rating, comment]);
        res.json({ success: true, message: 'Review submitted' });
    } catch (err) {
        res.status(500).json({ success: false });
    }
});

// Get Movie Reviews
router.get('/:id/reviews', async (req, res) => {
    try {
        const [reviews] = await pool.query(`
            SELECT r.*, u.username 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.movie_id = ? 
            ORDER BY r.created_at DESC
        `, [req.params.id]);
        res.json({ success: true, data: reviews });
    } catch (err) {
        res.status(500).json({ success: false });
    }
});

module.exports = router;
