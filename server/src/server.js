require('dotenv').config();
const app = require('./app');
const initDb = require('./utils/initDb');
//process.env.PORT || 
const PORT = 5003;

// Initialize DB Tables
(async () => {
    try {
        console.log(`Connecting to database at ${process.env.DB_HOST || '127.0.0.1'}:${process.env.DB_PORT || 3306}...`);
        await initDb();
        app.listen(PORT, () => {
            console.log(`Server running on http://localhost:${PORT}`);
        });
    } catch (err) {
        console.error('Failed to start server:', err);
        process.exit(1);
    }
})();
