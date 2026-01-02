@echo off
echo ===========================================
echo   MovieStream Output: Initial Setup
echo ===========================================
echo.

echo [1/3] Installing Root Dependencies...
call npm install
if %errorlevel% neq 0 (
    echo Error installing root dependencies.
    pause
    exit /b %errorlevel%
)

echo.
echo [2/3] Installing Client Dependencies...
cd client
call npm install
if %errorlevel% neq 0 (
    echo Error installing client dependencies.
    cd ..
    pause
    exit /b %errorlevel%
)
cd ..

echo.
echo [3/3] Installing Server Dependencies...
cd server
call npm install
if %errorlevel% neq 0 (
    echo Error installing server dependencies.
    cd ..
    pause
    exit /b %errorlevel%
)
cd ..

echo.
echo ===========================================
echo   Setup Complete! 
echo   You can now run 'run.bat' to start the app.
echo ===========================================
echo Press any key to close...
pause >nul
