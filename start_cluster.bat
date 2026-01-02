@echo off
echo Starting MovieStream Distributed Cluster...

:: Start Backend Instance 1 on Port 5001
start "Backend Node 1 (Port 5001)" cmd /k "cd server && set PORT=5001 && node src/server.js"

:: Start Backend Instance 2 on Port 5002
start "Backend Node 2 (Port 5002)" cmd /k "cd server && set PORT=5002 && node src/server.js"

:: Start Backend Instance 3 on Port 5003
start "Backend Node 3 (Port 5003)" cmd /k "cd server && set PORT=5003 && node src/server.js"

echo Cluster started!
echo IMPORTANT: Nginx is handling the Load Balancing on http://localhost (Port 80)
echo Backend Nodes are running on 5001, 5002, 5003
pause
