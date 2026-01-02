const pool = require('./config/db');
const bcrypt = require('bcrypt');

async function testLogin(email, password) {
    console.log(`Testing login for: ${email}`);
    try {
        const [users] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);

        if (users.length === 0) {
            console.log('User not found in database');
            process.exit(0);
        }

        const user = users[0];
        console.log(`User found: ${user.email} (ID: ${user.id})`);
        console.log(`Stored Password Hash: ${user.password ? user.password.substring(0, 10) + '...' : 'NULL'}`);

        if (!user.password) {
            console.log('Error: User has no password set');
            process.exit(1);
        }

        try {
            // PHP uses $2y$ prefix, Node bcrypt uses $2b$. 
            // Most Node bcrypt versions support $2y$ but let's see.
            // We'll also try a simple check if it's plain text (unlikely but possible during dev)
            if (password === user.password) {
                console.log('Match: PLAINTEXT (Warning: Insecure)');
            } else {
                const isMatch = await bcrypt.compare(password, user.password);
                console.log('Bcrypt Match Result:', isMatch);
            }
        } catch (bcryptError) {
            console.error('Bcrypt Error:', bcryptError.message);
        }

    } catch (err) {
        console.error('Database Query Error:', err.message);
    } finally {
        process.exit();
    }
}

// Default test credentials
const testEmail = process.argv[2] || 'admin@example.com';
const testPass = process.argv[3] || '123456';

testLogin(testEmail, testPass);
