const pool = require('../config/db');

exports.getMovies = async (req, res) => {
    try {
        const viewMode = req.query.view || 'all';
        const search = req.query.q ? req.query.q.trim() : '';

        if (viewMode === 'trending') {
            // Trending: Movies watched in the last 30 days, ranked by view count, then recency
            // We use a subquery or JOIN to count views specifically in that period
            const [movies] = await pool.query(`
                SELECT m.*, 
                       (SELECT COUNT(*) FROM history h WHERE h.movie_id = m.id AND h.watched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as view_count
                FROM movies m
                ORDER BY view_count DESC, m.created_at DESC
                LIMIT 12
            `);
            const moviesWithUrls = movies.map(movie => {
                const baseUrl = process.env.BASE_URL || 'http://localhost:5003';
                return {
                    ...movie,
                    poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `${baseUrl}${movie.poster}` : `${baseUrl}/uploads/posters/${movie.poster}`)) : null,
                    video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `${baseUrl}${movie.video}` : `${baseUrl}/uploads/videos/${movie.video}`)) : null
                };
            });
            return res.json({ success: true, data: moviesWithUrls });
        }

        let query = "SELECT * FROM movies WHERE 1=1";
        const params = [];

        if (search) {
            query += " AND (title LIKE ? OR actor LIKE ? OR genre LIKE ?)";
            params.push(`%${search}%`, `%${search}%`, `%${search}%`);
        }

        query += " ORDER BY created_at DESC";

        const [movies] = await pool.query(query, params);
        const moviesWithUrls = movies.map(movie => {
            const baseUrl = process.env.BASE_URL || 'http://localhost:5003';
            return {
                ...movie,
                poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `${baseUrl}${movie.poster}` : `${baseUrl}/uploads/posters/${movie.poster}`)) : null,
                video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `${baseUrl}${movie.video}` : `${baseUrl}/uploads/videos/${movie.video}`)) : null
            };
        });
        res.json({ success: true, data: moviesWithUrls });

    } catch (err) {
        console.error('Movies List Error:', err);
        res.status(500).json({ message: 'Server error: ' + err.message });
    }
};

exports.getMovieById = async (req, res) => {
    try {
        const [movies] = await pool.query("SELECT * FROM movies WHERE id = ?", [req.params.id]);
        if (movies.length === 0) {
            return res.status(404).json({ message: 'Movie not found' });
        }
        const movie = movies[0];
        const baseUrl = process.env.BASE_URL || 'http://localhost:5002';
        const movieWithUrls = {
            ...movie,
            poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `${baseUrl}${movie.poster}` : `${baseUrl}/uploads/posters/${movie.poster}`)) : null,
            video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `${baseUrl}${movie.video}` : `${baseUrl}/uploads/videos/${movie.video}`)) : null
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
};

exports.postComment = async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });
    const { comment } = req.body;
    try {
        await pool.query("INSERT INTO comments (user_id, movie_id, comment) VALUES (?, ?, ?)", [req.session.user.id, req.params.id, comment]);
        res.json({ success: true, message: 'Comment posted' });
    } catch (err) {
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.getComments = async (req, res) => {
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
};

exports.updateProgress = async (req, res) => {
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
};

exports.postReview = async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });
    const { rating, comment } = req.body;
    try {
        // Insert or update review
        await pool.query(`
            INSERT INTO reviews (user_id, movie_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE rating = ?, comment = ?, created_at = NOW()
        `, [req.session.user.id, req.params.id, rating, comment, rating, comment]);

        // Calculate new average
        const [avgResult] = await pool.query("SELECT AVG(rating) as average FROM reviews WHERE movie_id = ?", [req.params.id]);
        const newRating = avgResult[0].average || 0;

        // Update movie rating
        await pool.query("UPDATE movies SET rating = ? WHERE id = ?", [newRating, req.params.id]);

        res.json({ success: true, message: 'Review submitted' });
    } catch (err) {
        console.error('Post Review Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.getReviews = async (req, res) => {
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
};

exports.getContinueWatching = async (req, res) => {
    if (!req.session.user) return res.json({ success: true, data: [] });

    try {
        const [movies] = await pool.query(`
            SELECT m.*, h.last_time, h.watched_at
            FROM history h
            JOIN movies m ON h.movie_id = m.id
            WHERE h.user_id = ?
            ORDER BY h.watched_at DESC
            LIMIT 10
        `, [req.session.user.id]);

        const moviesWithUrls = movies.map(movie => {
            const baseUrl = process.env.BASE_URL || 'http://localhost:5003';
            return {
                ...movie,
                poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? `${baseUrl}${movie.poster}` : `${baseUrl}/uploads/posters/${movie.poster}`)) : null,
                video_url: movie.video ? (movie.video.startsWith('http') ? movie.video : (movie.video.startsWith('/') ? `${baseUrl}${movie.video}` : `${baseUrl}/uploads/videos/${movie.video}`)) : null
            };
        });

        res.json({ success: true, data: moviesWithUrls });
    } catch (err) {
        console.error('Continue Watching Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.toggleFavorite = async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Login required' });

    try {
        const [exists] = await pool.query("SELECT * FROM favorites WHERE user_id = ? AND movie_id = ?", [req.session.user.id, req.params.id]);

        if (exists.length > 0) {
            await pool.query("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?", [req.session.user.id, req.params.id]);
            res.json({ success: true, isFavorite: false, message: 'Removed from favorites' });
        } else {
            await pool.query("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)", [req.session.user.id, req.params.id]);
            res.json({ success: true, isFavorite: true, message: 'Added to favorites' });
        }
    } catch (err) {
        console.error('Toggle Favorite Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.checkFavorite = async (req, res) => {
    if (!req.session.user) return res.json({ success: true, isFavorite: false });
    try {
        const [exists] = await pool.query("SELECT * FROM favorites WHERE user_id = ? AND movie_id = ?", [req.session.user.id, req.params.id]);
        res.json({ success: true, isFavorite: exists.length > 0 });
    } catch (err) {
        res.status(500).json({ success: false });
    }
};
