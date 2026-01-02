@echo off
echo Starting MovieStream...
echo Client: http://localhost:5173
echo Server: http://localhost:5000
echo.
call npm run dev
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] The app crashed or failed to start.
    echo Check for missing 'node_modules' or errors above.
    pause
)
cmd /k
