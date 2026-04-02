const pool = require('./server/config/db');

(async () => {
    try {
        const [rows] = await pool.query('SELECT 1 + 1 AS solution');
        console.log('Database Connection: SUCCESS. Solution:', rows[0].solution);

        const [tables] = await pool.query('SHOW TABLES');
        console.log('Tables found:', tables.map(t => Object.values(t)[0]).join(', '));

        process.exit(0);
    } catch (err) {
        console.error('Database Connection Failed:', err.message);
        process.exit(1);
    }
})();
