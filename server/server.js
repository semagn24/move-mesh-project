const express = require('express');
const cors = require('cors');
const session = require('express-session');
const MySQLStore = require('express-mysql-session')(session);
const pool = require('./config/db'); // We can pass the pool to the session store
require('dotenv').config();

const app = express();
<<<<<<< HEAD
//process.env.PORT ||
const PORT = 5003;
=======
const PORT = process.env.PORT || 5000;
>>>>>>> origin/main

// Middleware
const allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    process.env.FRONTEND_URL // Allow dynamic IP from .env
].filter(Boolean);

app.use(cors({
    origin: allowedOrigins,
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use('/uploads', express.static('../uploads'));



// Session Store Options
const sessionStore = new MySQLStore({
    host: process.env.DB_HOST || 'localhost',
<<<<<<< HEAD
    port: process.env.DB_PORT || 3306,
=======
    port: 3306,
>>>>>>> origin/main
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
        secure: false, // Set to true if using HTTPS
        httpOnly: true,
        maxAge: 1000 * 60 * 60 * 24 // 1 day
    }
}));

// Routes
app.get('/', (req, res) => {
    res.send('MovieStream API is running...');
});

const authRoutes = require('./routes/auth');
app.use('/api/auth', authRoutes);

const movieRoutes = require('./routes/movies');
app.use('/api/movies', movieRoutes);

const adminRoutes = require('./routes/admin');
app.use('/api/admin', adminRoutes);

app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
