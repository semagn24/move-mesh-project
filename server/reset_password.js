const pool = require('./config/db');
const bcrypt = require('bcrypt');

async function resetPassword(email, newPassword) {
    if (!email || !newPassword) {
        console.log('Usage: node reset_password.js <email> <new_password>');
        process.exit(1);
    }

    try {
        const salt = await bcrypt.genSalt(10);
        const hash = await bcrypt.hash(newPassword, salt);

        const [result] = await pool.query('UPDATE users SET password = ? WHERE email = ?', [hash, email]);

        if (result.affectedRows > 0) {
            console.log(`SUCCESS: Password for ${email} has been updated.`);
            console.log(`New Password: ${newPassword}`);
        } else {
            console.log(`ERROR: User ${email} not found.`);
        }

    } catch (err) {
        console.error('Database Error:', err);
    } finally {
        process.exit();
    }
}

const email = process.argv[2];
const password = process.argv[3];

resetPassword(email, password);
