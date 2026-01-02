# Quick Network Configuration Guide

## When Your IP Changes (Different WiFi/Hotspot)

### Option 1: Automatic Configuration (Recommended) ⚡

Just run this script - it will detect your IP and update everything:

```powershell
cd "c:\xampp\htdocs\movie_stream (2)\movie_stream"
.\configure-network.ps1
```

**What it does:**
- ✅ Detects your current WiFi IP automatically
- ✅ Updates Nginx configuration
- ✅ Updates frontend API configuration
- ✅ Reloads Nginx
- ✅ Shows you the new URL to use

### Option 2: Manual Configuration 🔧

If you prefer to do it manually:

#### Step 1: Find Your New IP
```powershell
ipconfig
```
Look for "Wireless LAN adapter Wi-Fi" → "IPv4 Address"
Example: `192.168.1.100`

#### Step 2: Update Nginx Config
Edit: `C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4\conf\nginx.conf`

Find this line:
```nginx
server_name  192.168.137.132;
```

Change to your new IP:
```nginx
server_name  192.168.1.100;
```

#### Step 3: Update Frontend Config
Edit: `client\src\api\axios.js`

Find this:
```javascript
const NODES = [
    'http://192.168.137.132/api',
```

Change to:
```javascript
const NODES = [
    'http://192.168.1.100/api',
```

#### Step 4: Reload Nginx
```powershell
cd "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
.\nginx.exe -s reload
```

## Common Scenarios

### Scenario 1: Using Your Phone's Hotspot
1. Connect PC1 to your phone's hotspot
2. Run `configure-network.ps1`
3. The script will detect the hotspot IP (usually `192.168.x.x`)
4. Access from phone: `http://192.168.x.x` (the IP shown by the script)

### Scenario 2: Different Home WiFi
1. Connect to the new WiFi
2. Run `configure-network.ps1`
3. Access from any device on that WiFi using the new IP

### Scenario 3: University/Office Network
1. Connect to the network
2. Run `configure-network.ps1`
3. **Note**: Some networks block port 80. If it doesn't work:
   - Try accessing via `http://YOUR_IP:5173` (direct to frontend)
   - Or ask IT to allow port 80

## Quick Reference

| What Changed | What to Do |
|--------------|------------|
| WiFi Network | Run `configure-network.ps1` |
| Phone Hotspot | Run `configure-network.ps1` |
| PC2 IP Changed | Run script, enter new PC2 IP when asked |
| Just moved locations | Run `configure-network.ps1` |

## Troubleshooting

### Script says "Could not detect WiFi IP"
- Make sure you're connected to WiFi (not Ethernet)
- Or manually find IP with `ipconfig` and use Option 2

### Phone still can't connect after running script
1. Check Windows Firewall (port 80 must be open)
2. Make sure phone is on the SAME network as PC
3. Try `http://YOUR_IP:5173` instead

### PC2 failover not working
- Make sure PC2 is on the same network
- Run `configure-network.ps1` on PC2 as well
- Update PC2's `.env` file: `DB_HOST=YOUR_PC1_IP`

## Pro Tip 💡
Create a desktop shortcut to `configure-network.ps1` so you can run it with one click whenever you change networks!
