const bcrypt = require('bcrypt');
const pool = require('../config/db');

exports.login = async (req, res) => {
    const { email, password } = req.body;
    console.log(`[LOGIN ATTEMPT] Email: ${email}`);

    if (!email || !password) {
        return res.status(400).json({ message: 'Please provide email and password' });
    }

    try {
        const [users] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);
        console.log(`[LOGIN DB RESULT] Found users: ${users.length}`);

        if (users.length === 0) {
            return res.status(401).json({ message: 'Invalid credentials' });
        }

        const user = users[0];

        // Fix for PHP password_hash compatibility ($2y$ -> $2b$)
        let passwordHash = user.password;
        if (passwordHash && passwordHash.startsWith('$2y$')) {
            passwordHash = passwordHash.replace(/^\$2y\$/, '$2b$');
        }

        const isMatch = await bcrypt.compare(password, passwordHash);

        if (!isMatch) {
            return res.status(401).json({ message: 'Invalid credentials' });
        }

        // Create Session
        req.session.user = {
            id: user.id,
            username: user.username,
            role: user.role,
            email: user.email
        };

        res.json({
            message: 'Login successful',
            user: req.session.user,
            success: true
        });

    } catch (error) {
        console.error('Login Error:', error);
        res.status(500).json({ message: 'Server error: ' + error.message });
    }
};

exports.register = async (req, res) => {
    const { username, email, password } = req.body;

    if (!username || !email || !password) {
        return res.status(400).json({ message: 'Please provide all fields' });
    }

    try {
        // Check if user exists
        const [existing] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);
        if (existing.length > 0) {
            return res.status(400).json({ message: 'User already exists' });
        }

        // Hash password
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);

        // Insert User (Default role: user)
        const [result] = await pool.query(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)',
            [username, email, hashedPassword, 'user']
        );

        res.status(201).json({ success: true, message: 'User registered successfully' });

    } catch (error) {
        console.error('Register Error:', error);
        res.status(500).json({ message: 'Server error: ' + error.message });
    }
};

exports.logout = (req, res) => {
    req.session.destroy((err) => {
        if (err) {
            return res.status(500).json({ message: 'Logout failed' });
        }
        res.clearCookie('session_cookie_name');
        res.json({ message: 'Logged out successfully' });
    });
};

exports.getMe = (req, res) => {
    if (req.session.user) {
        res.json({ user: req.session.user, success: true });
    } else {
        res.status(401).json({ message: 'Not authenticated', success: false });
    }
};

exports.getProfile = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;

        // Fetch user info
        const [users] = await pool.query('SELECT id, username, email, role, created_at FROM users WHERE id = ?', [userId]);
        if (users.length === 0) {
            return res.status(404).json({ success: false, message: 'User not found' });
        }

        // Fetch favorites
        const [favorites] = await pool.query(`
            SELECT m.* 
            FROM movies m 
            JOIN favorites f ON m.id = f.movie_id 
            WHERE f.user_id = ?
        `, [userId]);

        const favoritesWithUrls = favorites.map(movie => ({
            ...movie,
            poster_url: movie.poster ? (movie.poster.startsWith('http') ? movie.poster : (movie.poster.startsWith('/') ? movie.poster : `/uploads/posters/${movie.poster}`)) : null,
        }));

        res.json({
            success: true,
            user: users[0],
            favorites: favoritesWithUrls
        });

    } catch (err) {
        console.error('Profile Error:', err);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

exports.updateProfile = async (req, res) => {
    if (!req.session.user) return res.status(401).json({ success: false, message: 'Not authenticated' });

    const { username, email, currentPassword, newPassword } = req.body;
    const userId = req.session.user.id;

    try {
        // Verify current password if changing sensitive info or updating password
        const [users] = await pool.query('SELECT * FROM users WHERE id = ?', [userId]);
        const user = users[0];

        if (currentPassword) {
            let passwordHash = user.password;
            if (passwordHash && passwordHash.startsWith('$2y$')) {
                passwordHash = passwordHash.replace(/^\$2y\$/, '$2b$');
            }
            const isMatch = await bcrypt.compare(currentPassword, passwordHash);
            if (!isMatch) return res.status(400).json({ success: false, message: 'Incorrect current password' });
        } else if (newPassword) {
            return res.status(400).json({ success: false, message: 'Current password required to set new password' });
        }

        let query = 'UPDATE users SET username = ?, email = ?';
        let params = [username || user.username, email || user.email];

        if (newPassword) {
            const salt = await bcrypt.genSalt(10);
            const hashedPassword = await bcrypt.hash(newPassword, salt);
            query += ', password = ?';
            params.push(hashedPassword);
        }

        query += ' WHERE id = ?';
        params.push(userId);

        await pool.query(query, params);

        // Update session
        req.session.user.username = username || user.username;
        req.session.user.email = email || user.email;

        res.json({ success: true, message: 'Profile updated successfully' });
    } catch (err) {
        console.error('Update Profile Error:', err);
        res.status(500).json({ success: false, message: 'Server error: ' + err.message });
    }
};

exports.forgotPassword = async (req, res) => {
    const { email } = req.body;
    res.json({ success: true, message: 'If an account exists with that email, a reset link has been sent.' });
};
