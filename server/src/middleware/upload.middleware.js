const multer = require('multer');
const path = require('path');
const fs = require('fs');

// Ensure upload directories exist
// __dirname is server/src/middleware
// We want project_root/uploads. 
// server/src/middleware -> server/src -> server -> project_root
// server/src/middleware -> server/src -> server -> project_root
const uploadDir = process.env.UPLOAD_PATH || path.join(__dirname, '../../../uploads');
const postersDir = path.join(uploadDir, 'posters');
const videosDir = path.join(uploadDir, 'videos');

[uploadDir, postersDir, videosDir].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
});

// Configure Multer Storage
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        if (file.fieldname === 'poster') {
            cb(null, postersDir);
        } else if (file.fieldname === 'video') {
            cb(null, videosDir);
        } else {
            cb(null, uploadDir);
        }
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + path.extname(file.originalname));
    }
});

const upload = multer({ storage: storage });

module.exports = upload;
