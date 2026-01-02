const pool = require('./config/db');
const bcrypt = require('bcrypt');

const resetPassword = async () => {
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash('password123', salt);
    await pool.query("UPDATE users SET password = ? WHERE username = 'admin'", [hashedPassword]);
    console.log('Password updated for admin');
    process.exit(0);
};

resetPassword().catch(err => {
    console.error(err);
    process.exit(1);
});
