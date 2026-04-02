const pool = require('./server/src/config/db');
async function check() {
    try {
        const [movies] = await pool.query('SELECT id, title FROM movies');
        console.log('Movies found:', movies);
        process.exit(0);
    } catch (err) {
        console.error(err);
        process.exit(1);
    }
}
check();
