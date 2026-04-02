# MovieStream Distributed Setup & Failover Guide

This guide describes how to run MovieStream across two PCs (PC1 and PC2) for load balancing and failover, and how to verify it works.

## 1. Prerequisites
- **PC1 (Main Server):** Hosting Nginx, MySQL, and the primary Backend/Frontend.
- **PC2 (Secondary Server):** Hosting a secondary Backend/Frontend for failover.
- **Network:** Both PCs must be on the same Wi-Fi/LAN.

## 2. File Synchronization (Syncing Uploads)
To ensure movies uploaded on PC2 appear on PC1 (and vice-versa), we use a shared network folder.

### Step A: Share Folder on PC1
1. On PC1, navigate to: `C:\xampp\htdocs\movie_stream (2)\movie_stream`
2. Right-click the `uploads` folder > **Properties** > **Sharing** > **Share...**
3. Select **Everyone** (or a specific user) > **Add**.
4. Set Permission Level to **Read/Write**.
5. Click **Share**. Note the Network Path (e.g., `\\DESKTOP-PC1\uploads` or `\\192.168.43.81\uploads`).

### Step B: Configure PC2
1. On PC2, open `server/.env`.
2. Add/Update the `UPLOAD_PATH` variable:
   ```ini
   UPLOAD_PATH=\\192.168.43.81\uploads
   ```
3. Restart the PC2 backend (`npm start`).

**Test:** Upload a movie from PC2. Check if the file appears inside the `uploads` folder on PC1.

## 3. Failover Verification (Testing High Availability)

### Setup Nginx on PC1
Ensure `nginx.conf` has both servers listed in the upstream block:
```nginx
upstream movie_backend {
    server 127.0.0.1:5000;       # PC1 (Primary)
    server 192.168.43.82:5003 backup; # PC2 (Backup)
}
```

### Test Scenario 1: Normal Operation
1. Start Backend on **PC1** and **PC2**.
2. Open the website on your phone (`http://192.168.43.81`).
3. The site should load. Nginx will route traffic to PC1 (Primary).

### Test Scenario 2: Failover
1. **Stop the Backend on PC1** (Press `Ctrl + C` in PC1's terminal).
2. Keep PC2 running.
3. Refresh the website on your phone.
4. **Result:** The site should STILL load! Nginx detects PC1 is down and automatically switches to PC2.

### Test Scenario 3: Recovery
1. **Start Backend on PC1** again.
2. Refresh the website multiple times.
3. **Result:** Nginx notices PC1 is back and switches traffic back to the primary server.

## 4. Mobile Responsiveness / Distributed Access
- **Phone Access:** Always use `http://192.168.43.81` (PC1's IP).
- **Frontend Config:** We updated `client/.env` to use `http://localhost:5000` (locally) but the code now smartly handles relative paths (`/uploads/...`) so it works on any device via Nginx.
