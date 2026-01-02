const pool = require('../config/db');

async function migrate() {
    try {
        console.log('Checking transactions table...');

        // Check if column exists
        const [rows] = await pool.query(`SHOW COLUMNS FROM transactions LIKE 'type'`);

        if (rows.length === 0) {
            console.log('Adding type column to transactions table...');
            await pool.query(`ALTER TABLE transactions ADD COLUMN type VARCHAR(10) DEFAULT 'new' AFTER amount`);
            console.log('Column added successfully.');
        } else {
            console.log('Column type already exists.');
        }

        console.log('Migration completed.');
        process.exit(0);
    } catch (error) {
        console.error('Migration failed:', error);
        process.exit(1);
    }
}

migrate();
