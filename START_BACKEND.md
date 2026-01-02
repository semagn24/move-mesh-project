# MovieStream Backend Startup Guide

## The Problem
You're getting "404 Not Found" because the backend server needs to be restarted to load the new payment routes.

## Solution: Start/Restart Backend Server

### Option 1: Using npm (Recommended)
Open a NEW terminal in the `server` folder and run:
```bash
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\server"
npm run dev
```

### Option 2: Using Node directly
```bash
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\server"
node src/server.js
```

## Stop Existing Server (if port 5000 is in use)

### Find the process:
```bash
netstat -ano | findstr :5000
```

### Kill the process (replace PID with actual number):
```bash
taskkill /PID <PID_NUMBER> /F
```

Example:
```bash
taskkill /PID 12345 /F
```

## Verify Backend is Running

1. Open browser to: `http://localhost:5000`
2. You should see: "MovieStream API is running..."

3. Test payment route:
   - Open browser to: `http://localhost:5000/api/payments/subscription/check`
   - You should see an "Unauthorized" or authentication error (NOT 404)

## What Should Be Running

You need **TWO terminals**:

### Terminal 1 - Backend (Port 5000)
```bash
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\server"
npm run dev
```
Output should show:
```
Server running on port 5000
```

### Terminal 2 - Frontend (Port 5173) - Already Running
```bash
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\client"
npm run dev
```
Output should show:
```
Local: http://localhost:5173/
```

## After Starting Backend

1. **Go to Profile page** in browser (http://localhost:5173/profile)
2. **Click "Subscribe Now"**
3. **Check browser console** - should see:
   ```
   [SUBSCRIPTION] Starting subscription process...
   [SUBSCRIPTION] Response received: {success: true, demo: true}
   ```

4. **Check server terminal** - should see:
   ```
   [PAYMENT] Initialize payment request received
   [PAYMENT] Demo mode payment successful
   ```

## Still Getting 404?

If after restarting you still get 404:

1. **Check app.js line 59** has:
   ```javascript
   app.use('/api/payments', paymentRoutes);
   ```

2. **Check payment.routes.js exists** at:
   ```
   server/src/routes/payment.routes.js
   ```

3. **Check payment.controller.js exists** at:
   ```
   server/src/controllers/payment.controller.js
   ```

4. **Restart backend server** (kill and start again)

## Quick Test Commands

### Test if backend is running:
```bash
curl http://localhost:5000
```
Should return: "MovieStream API is running..."

### Test if payment route exists:
```bash
curl http://localhost:5000/api/payments/subscription/check
```
Should return: JSON with "Not authenticated" (NOT 404)

---

## TL;DR - Just Do This

1. Open NEW PowerShell terminal
2. Run:
   ```bash
   cd "c:\xampp\htdocs\movie_stream (2)\movie_stream\server"
   npm run dev
   ```
3. Wait for "Server running on port 5000"
4. Try subscribing again in the browser
5. It should work! 🎉
