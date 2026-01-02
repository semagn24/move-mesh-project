# MovieStream Multi-PC Distribution & Load Balancing Guide

## Current Status ✅
Your system is **WORKING** with the following setup:
- **Backend (PC1)**: Running on `localhost:5000` via `npm start`
- **Frontend (PC1)**: Running on `localhost:5173` via `npm run dev`
- **Nginx Load Balancer**: Running on port `80`, routing traffic

## How to Access from Phone/PC2

### Step 1: Open Windows Firewall Port 80
1. Press `Win + R`, type `wf.msc`, press Enter
2. Click **"Inbound Rules"** on the left
3. Click **"New Rule..."** on the right
4. Select **"Port"**, click Next
5. Select **"TCP"**, type `80` in "Specific local ports", click Next
6. Select **"Allow the connection"**, click Next
7. Check all three boxes (Domain, Private, Public), click Next
8. Name it **"Nginx HTTP"**, click Finish

### Step 2: Access from Your Phone
Open Chrome on your phone and go to:
```
http://192.168.137.132
```

### Step 3: Test Failover (Optional)
1. **On PC2**: 
   - Navigate to the `movie_stream` folder
   - Run `npm start` in the `server` folder (port 5003)
   - Run `npm run dev` in the `client` folder (port 5173)

2. **Stop PC1 services** (close the terminals)

3. **Refresh your phone** - it should automatically switch to PC2!

## Quick Start Commands

### On PC1:
```powershell
# Terminal 1 - Backend
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\server"
npm start

# Terminal 2 - Frontend
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\client"
npm run dev

# Terminal 3 - Nginx (if not running)
cd "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
start nginx.exe
```

### On PC2 (for failover):
```powershell
# Update .env file first:
# DB_HOST=192.168.137.132
# PORT=5003

# Terminal 1 - Backend
cd "path\to\movie_stream\server"
npm start

# Terminal 2 - Frontend  
cd "path\to\movie_stream\client"
npm run dev
```

## Troubleshooting

### Phone shows "Site can't be reached"
- **Check Firewall**: Make sure port 80 is allowed (see Step 1 above)
- **Check Network**: Ensure phone is on the same WiFi/Hotspot as PC1
- **Verify Nginx**: Run `netstat -ano | findstr :80` - should show LISTENING

### Movies not loading
- **Check Backend**: Run `curl http://localhost:5000/api/movies` - should return JSON
- **Check Nginx**: Run `curl http://localhost/api/movies` - should return same JSON
- **Restart Nginx**: 
  ```powershell
  cd "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
  .\nginx.exe -s reload
  ```

### PC2 not taking over when PC1 fails
- Ensure PC2 backend is on port **5003** (check `.env` file)
- Ensure PC2's IP is **192.168.137.134** (or update `nginx.conf`)
- Check PC2 firewall allows ports 5003 and 5173

## System Architecture

```
Phone/Browser
    ↓
http://192.168.137.132 (Nginx on PC1:80)
    ↓
┌─────────────────┬──────────────────┐
│   Frontend      │    Backend       │
│  PC1:5173 ✓     │   PC1:5000 ✓     │
│  PC2:5173 ⚠     │   PC2:5003 ⚠     │
└─────────────────┴──────────────────┘
         ↓                  ↓
    React App          MySQL (PC1:3309)
```

✓ = Primary (always used first)
⚠ = Backup (used only if primary fails)

## Notes
- MySQL stays on PC1 (XAMPP) - both servers connect to it
- Uploads folder should be synced between PCs or use network share
- Docker setup is available but optional (manual setup is simpler)
