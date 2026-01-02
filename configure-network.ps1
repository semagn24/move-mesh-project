# Auto-Configure MovieStream for Current Network
# This script automatically detects your IP and updates all configurations

Write-Host "=== MovieStream Network Auto-Configuration ===" -ForegroundColor Cyan
Write-Host ""

# 1. Detect current WiFi IP address
Write-Host "[1/5] Detecting your current IP address..." -ForegroundColor Yellow
$ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
        $_.InterfaceAlias -like "*Wi-Fi*" -or $_.InterfaceAlias -like "*Wireless*"
    } | Select-Object -First 1).IPAddress

if (-not $ipAddress) {
    Write-Host "ERROR: Could not detect WiFi IP. Are you connected to WiFi?" -ForegroundColor Red
    exit 1
}

Write-Host "   Found IP: $ipAddress" -ForegroundColor Green
Write-Host ""

# 2. Ask for PC2 IP (optional)
Write-Host "[2/5] Enter PC2 IP address (or press Enter to skip failover):" -ForegroundColor Yellow
$pc2IP = Read-Host "   PC2 IP"
if ([string]::IsNullOrWhiteSpace($pc2IP)) {
    $pc2IP = "192.168.1.999"  # Dummy IP that won't resolve
    Write-Host "   Skipping PC2 failover configuration" -ForegroundColor Gray
}
else {
    Write-Host "   PC2 IP set to: $pc2IP" -ForegroundColor Green
}
Write-Host ""

# 3. Update Nginx configuration
Write-Host "[3/5] Updating Nginx configuration..." -ForegroundColor Yellow
$nginxConfig = @"
worker_processes  1;

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;
    sendfile        on;
    keepalive_timeout  65;

    upstream movie_backend {
        server 127.0.0.1:5000; 
        server ${pc2IP}:5003 backup;
    }

    upstream movie_frontend {
        server 127.0.0.1:5173;
        server ${pc2IP}:5173 backup;
    }

    server {
        listen       80;
        server_name  ${ipAddress};

        location /api/ {
            proxy_pass http://movie_backend;
            proxy_set_header Host `$host;
            proxy_set_header X-Real-IP `$remote_addr;
            proxy_set_header X-Forwarded-For `$proxy_add_x_forwarded_for;
        }

        location /uploads/ {
            alias "C:/xampp/htdocs/movie_stream (2)/movie_stream/uploads/";
            autoindex on;
        }

        location / {
            proxy_pass http://movie_frontend;
            proxy_set_header Host `$host;
            proxy_set_header X-Real-IP `$remote_addr;
            proxy_http_version 1.1;
            proxy_set_header Upgrade `$http_upgrade;
            proxy_set_header Connection "upgrade";
        }
    }
}
"@

$nginxConfigPath = "C:\xampp\htdocs\movie_stream (2)\nginx.conf"
Set-Content -Path $nginxConfigPath -Value $nginxConfig -Force
Write-Host "   ✓ Nginx config updated" -ForegroundColor Green

# Copy to Nginx directory
$nginxInstallPath = "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4\conf\nginx.conf"
if (Test-Path $nginxInstallPath) {
    Copy-Item $nginxConfigPath -Destination $nginxInstallPath -Force
    Write-Host "   ✓ Copied to Nginx directory" -ForegroundColor Green
}
Write-Host ""

# 4. Update client axios.js
Write-Host "[4/5] Updating frontend API configuration..." -ForegroundColor Yellow
$axiosPath = "C:\xampp\htdocs\movie_stream (2)\movie_stream\client\src\api\axios.js"
$axiosContent = Get-Content $axiosPath -Raw

# Update the NODES array
$newNodes = @"
const NODES = [
    'http://${ipAddress}/api',           // Nginx Load Balancer (Current PC)
    'http://${pc2IP}:5003/api',          // Direct to PC2 (Failover)
    'http://localhost:5000/api'          // Local developer access
];
"@

$axiosContent = $axiosContent -replace "const NODES = \[[^\]]+\];", $newNodes
Set-Content -Path $axiosPath -Value $axiosContent -Force
Write-Host "   ✓ Frontend configured for: http://${ipAddress}" -ForegroundColor Green
Write-Host ""

# 5. Reload Nginx
Write-Host "[5/5] Reloading Nginx..." -ForegroundColor Yellow
$nginxExe = "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4\nginx.exe"
if (Test-Path $nginxExe) {
    Push-Location "C:\Users\hp\Downloads\nginx-1.29.4\nginx-1.29.4"
    
    # Check if nginx is running
    $nginxProcess = Get-Process nginx -ErrorAction SilentlyContinue
    if ($nginxProcess) {
        & .\nginx.exe -s reload
        Write-Host "   ✓ Nginx reloaded" -ForegroundColor Green
    }
    else {
        Start-Process -FilePath ".\nginx.exe" -WindowStyle Hidden
        Write-Host "   ✓ Nginx started" -ForegroundColor Green
    }
    
    Pop-Location
}
else {
    Write-Host "   ⚠ Nginx not found at expected location" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "=== Configuration Complete! ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your MovieStream is now accessible at:" -ForegroundColor White
Write-Host "   http://${ipAddress}" -ForegroundColor Green -NoNewline
Write-Host " (from phone/other devices)" -ForegroundColor Gray
Write-Host "   http://localhost" -ForegroundColor Green -NoNewline
Write-Host " (from this PC)" -ForegroundColor Gray
Write-Host ""

if ($pc2IP -ne "192.168.1.999") {
    Write-Host "Failover configured to PC2: $pc2IP" -ForegroundColor Cyan
    Write-Host ""
}

Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Make sure backend is running: npm start (in server folder)"
Write-Host "  2. Make sure frontend is running: npm run dev (in client folder)"
Write-Host "  3. Open on your phone: http://${ipAddress}"
Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
