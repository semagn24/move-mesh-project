require('dotenv').config();
const express = require('express');
const cors = require('cors');
const session = require('express-session');
const MySQLStore = require('express-mysql-session')(session);
const pool = require('./config/db');

const app = express();
app.set('trust proxy', 1); // Trust first proxy (Nginx)

// Middleware
app.use(cors({
    origin: [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost',
        'http://127.0.0.1',
        'http://192.168.137.134',
        'http://192.168.137.134:5173',
        'http://192.168.137.1:5173',
        process.env.CLIENT_PC_IP ? `http://${process.env.CLIENT_PC_IP}:5173` : null
    ].filter(Boolean),
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use('/uploads', express.static('../../uploads')); // Adjust path relative to src/app.js

// Session Store
const sessionStore = new MySQLStore({
    host: process.env.DB_HOST || '127.0.0.1',
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'movie_stream',
    createDatabaseTable: true
});

app.use(session({
    key: 'session_cookie_name',
    secret: process.env.SESSION_SECRET || 'super_secret_key_change_me',
    store: sessionStore,
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: false,
        httpOnly: true,
        sameSite: 'lax',
        maxAge: 1000 * 60 * 60 * 24 // 1 day
    }
}));

// Routes
app.get('/', (req, res) => {
    res.send('MovieStream API is running...');
});

// Import Routes
const authRoutes = require('./routes/auth.routes');
const movieRoutes = require('./routes/movie.routes');
const adminRoutes = require('./routes/admin.routes');
const notificationRoutes = require('./routes/notification.routes');
const paymentRoutes = require('./routes/payment.routes');

app.use('/api/auth', authRoutes);
app.use('/api/movies', movieRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/notifications', notificationRoutes);
app.use('/api/payments', paymentRoutes);

// Error Handling
const errorHandler = require('./middleware/error.middleware');
app.use(errorHandler);

module.exports = app;
