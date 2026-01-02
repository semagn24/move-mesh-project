# Failover Testing Guide - PC1 to PC2

## Prerequisites
- PC1 IP: 192.168.137.132
- PC2 IP: 192.168.137.134 (confirm this first!)
- Both PCs on the same network

## Step-by-Step Failover Test

### PART 1: Setup PC2 (Secondary Server)

#### 1. On PC2 - Copy the Project
Copy the entire `movie_stream` folder from PC1 to PC2 (same location if possible)

#### 2. On PC2 - Update Environment Variables
Edit `server/.env` file:
```env
DB_HOST=192.168.137.132
DB_PORT=3309
DB_USER=root
DB_PASSWORD=
DB_NAME=movie_stream
PORT=5003
SESSION_SECRET=your_super_secret_session_key
```

**Important:** 
- `DB_HOST` points to PC1 (where MySQL is)
- `PORT` is **5003** (different from PC1's 5000)

#### 3. On PC2 - Install Dependencies (if not done)
```powershell
# In server folder
cd server
npm install

# In client folder
cd ..\client
npm install
```

#### 4. On PC2 - Start the Services
Open TWO terminals:

**Terminal 1 - Backend:**
```powershell
cd "path\to\movie_stream\server"
npm start
```
You should see: `Server running on all interfaces at port 5003`

**Terminal 2 - Frontend:**
```powershell
cd "path\to\movie_stream\client"
npm run dev
```
You should see: `Network: http://192.168.137.134:5173/`

#### 5. On PC2 - Open Firewall Ports
```powershell
# Run PowerShell as Administrator
New-NetFirewallRule -DisplayName "MovieStream Backend" -Direction Inbound -LocalPort 5003 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "MovieStream Frontend" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow
```

### PART 2: Verify PC2 is Working

#### Test PC2 Backend Directly
From PC1, run:
```powershell
curl http://192.168.137.134:5003/api/movies
```
✅ Should return JSON with movies

#### Test PC2 Frontend Directly
Open browser on PC1:
```
http://192.168.137.134:5173
```
✅ Should show the website

### PART 3: Configure Failover on PC1

#### Update Nginx Configuration
The configuration should already have PC2 as backup:
```nginx
upstream movie_backend {
    server 127.0.0.1:5000;           # PC1 (Primary)
    server 192.168.137.134:5003 backup;  # PC2 (Backup)
}

upstream movie_frontend {
    server 127.0.0.1:5173;           # PC1 (Primary)
    server 192.168.137.134:5173 backup;  # PC2 (Backup)
}
```

#### Reload Nginx
```powershell
cd "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
.\nginx.exe -s reload
```

### PART 4: Test Failover

#### Initial State - Both PCs Running
1. Open phone browser: `http://192.168.137.132`
2. ✅ Website should load (served by PC1)
3. Check browser console (F12) - you should see requests going to PC1

#### Test 1 - Stop PC1 Backend Only
1. On PC1: Stop the `npm start` terminal (Ctrl+C)
2. On phone: Refresh the page
3. ✅ Website should still load (frontend from PC1, backend from PC2)
4. ✅ Movies should appear (data from PC2)

#### Test 2 - Stop PC1 Frontend Only
1. On PC1: Start backend again (`npm start`)
2. On PC1: Stop the `npm run dev` terminal (Ctrl+C)
3. On phone: Refresh the page
4. ✅ Website should still load (frontend from PC2, backend from PC1)

#### Test 3 - Stop Both PC1 Services (Full Failover)
1. On PC1: Stop both `npm start` AND `npm run dev`
2. On phone: Refresh the page
3. ✅ Website should still load (everything from PC2)
4. ✅ All features should work (movies, login, etc.)

#### Test 4 - PC1 Comes Back Online
1. On PC1: Start both services again
2. On phone: Refresh the page
3. ✅ Traffic should automatically switch back to PC1

### PART 5: Advanced Testing

#### Test Database Sync
1. On PC1: Add a new movie via admin panel
2. On PC2: Check if the movie appears
3. ✅ Should appear immediately (both use same DB)

#### Test Session Persistence
1. Login on phone (while PC1 is serving)
2. Stop PC1 services
3. Refresh page
4. ✅ Should still be logged in (sessions stored in MySQL)

#### Test Upload Sync (Manual)
1. Upload a movie poster on PC1
2. Stop PC1
3. Try to view that movie on PC2
4. ⚠️ Poster won't show (uploads are local to PC1)
5. **Solution:** Copy `uploads` folder from PC1 to PC2, or use network share

## Troubleshooting

### PC2 backend won't start
- Check if port 5003 is already in use: `netstat -ano | findstr :5003`
- Verify `.env` has `PORT=5003`
- Check MySQL is accessible from PC2: `ping 192.168.137.132`

### Failover not happening
- Check Nginx error log: `C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4\logs\error.log`
- Verify PC2 firewall allows ports 5003 and 5173
- Test PC2 directly: `curl http://192.168.137.134:5003/api/movies`

### Phone shows "502 Bad Gateway" after PC1 stops
- This means Nginx can't reach PC2
- Verify PC2 services are running
- Check PC2 IP is correct (192.168.137.134)
- Ping PC2 from PC1: `ping 192.168.137.134`

## Success Criteria ✅

Your failover is working correctly if:
- [ ] PC2 backend responds on port 5003
- [ ] PC2 frontend responds on port 5173
- [ ] Website loads when PC1 is completely off
- [ ] Login sessions persist across failover
- [ ] Movies from database appear on both PCs
- [ ] No errors in browser console during failover

## Quick Commands Reference

### Check What's Running
```powershell
# On PC1
netstat -ano | findstr :5000  # Backend
netstat -ano | findstr :5173  # Frontend
netstat -ano | findstr :80    # Nginx

# On PC2
netstat -ano | findstr :5003  # Backend
netstat -ano | findstr :5173  # Frontend
```

### Test Connectivity
```powershell
# From PC1 to PC2
curl http://192.168.137.134:5003/api/movies
curl http://192.168.137.134:5173

# From PC2 to PC1
curl http://192.168.137.132:5000/api/movies
curl http://192.168.137.132:5173
```

### Restart Services
```powershell
# Nginx
cd "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
.\nginx.exe -s reload

# Backend (in server folder)
npm start

# Frontend (in client folder)
npm run dev
```
