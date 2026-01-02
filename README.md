# MovieStream - Advanced Distributed Streaming Platform

MovieStream is a modern, high-performance video streaming application built with the MERN stack (MySQL, Express, React, Node.js). It supports distributed deployment, failover redundancy, and premium subscriptions with payment integration.

## 🚀 Key Features

*   **Responsive UI**: Modern, glassmorphism-inspired design using Tailwind CSS. Fully responsive for Mobile, Tablet, and Desktop.
*   **Media Streaming**: High-performance video streaming with support for local and network storage.
*   **Distributed Architecture**: Support for Load Balancing and Failover (Primary/Backup servers) using Nginx.
*   **User Management**: Secure Authentication, Profile Management, and Role-based Access Control (Admin/User).
*   **Admin Dashboard**: Manage Movies, Users, Analytics, and Payments.
*   **Engagement**: Comments, Reviews, Ratings, and Watch History.
*   **Monetization**: Chapa Payment Gateway integration for Premium Subscriptions.
*   **Email Services**: Password Reset functionality using SMTP.

## 🛠️ Tech Stack

*   **Frontend**: React.js, Tailwind CSS, Vite, Axios
*   **Backend**: Node.js, Express.js
*   **Database**: MySQL (using mysql2 pool)
*   **Storage**: Multer for local/network file synchronization
*   **Server**: Nginx (Reverse Proxy & Load Balancing)

## 📦 Installation & Setup

### 1. Database Setup
1.  Open phpMyAdmin or MySQL Workbench.
2.  Create a database named `movie_stream`.
3.  Import the schema structure (automatically handled by `server/src/utils/initDb.js` on first run).

### 2. Backend Setup
```bash
cd server
npm install
# Configure .env file (see below)
npm start
```

### 3. Frontend Setup
```bash
cd client
npm install
# Configure .env file (see below)
npm run dev
```

### 4. Nginx Setup
Copy the contents of `nginx.conf` to your Nginx configuration folder or point Nginx to use it.
Ensure upstream servers are configured for failover if using multiple PCs.

## ⚙️ Configuration (.env)

**Server (.env):**
```ini
PORT=5000
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=movie_stream
JWT_SECRET=your_secret_key
SESSION_SECRET=your_session_secret
FRONTEND_URL=http://localhost:5173
# Email
SMTP_HOST=smtp.gmail.com
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
# Payments
CHAPA_SECRET_KEY=your_key
# Network Sync (Optional for PC2)
UPLOAD_PATH=\\PC1\uploads
```

**Client (.env):**
```ini
VITE_BACKEND_URL=http://localhost:5000
```

## 🔄 Distributed Failover & Sync

For details on running the distributed setup across two PCs, please refer to [FAILOVER_GUIDE.md](./FAILOVER_GUIDE.md).

## 📱 Mobile Access

To access from mobile on the same network:
1.  Ensure Nginx is running.
2.  Open browser and go to `http://YOUR_PC_IP` (e.g., `http://192.168.43.81`).
3.  Ensure your firewall allows traffic on Port 80.
