const pool = require('./server/config/db');
pool.query("SELECT username, email, role FROM users WHERE role = 'admin'")
    .then(([rows]) => {
        console.log(JSON.stringify(rows, null, 2));
        process.exit(0);
    })
    .catch(err => {
        console.error(err);
        process.exit(1);
    });
