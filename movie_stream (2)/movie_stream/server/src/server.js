require('dotenv').config();
const app = require('./app');
const initDb = require('./utils/initDb');

const PORT = process.env.PORT || 5000;

// Initialize DB Tables
initDb();

app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on all interfaces at port ${PORT}`);
    console.log(`Local access: http://localhost:${PORT}`);
});
