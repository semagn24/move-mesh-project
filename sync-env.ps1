# PowerShell script to sync global .env to client and server folders
$rootEnv = Get-Content ".env" -Raw | ConvertFrom-StringData

# 1. Update Server .env
$serverEnvContent = @"
DB_HOST=$($rootEnv.DB_HOST)
DB_PORT=$($rootEnv.DB_PORT)
DB_USER=$($rootEnv.DB_USER)
DB_PASSWORD=$($rootEnv.DB_PASSWORD)
DB_NAME=$($rootEnv.DB_NAME)
PORT=$($rootEnv.BACKEND_PORT)
SESSION_SECRET=$($rootEnv.SESSION_SECRET)
FRONTEND_URL=http://$($rootEnv.CLIENT_PC_IP):5173
"@
$serverEnvContent | Out-File -FilePath "server/.env" -Encoding utf8

# 2. Update Client .env
$clientEnvContent = @"
VITE_BACKEND_URL=http://$($rootEnv.SERVER_PC_IP):$($rootEnv.BACKEND_PORT)
"@
$clientEnvContent | Out-File -FilePath "client/.env" -Encoding utf8

Write-Host "✅ Environment files synchronized!" -ForegroundColor Green
Write-Host "Server is expected at: http://$($rootEnv.SERVER_PC_IP):$($rootEnv.BACKEND_PORT)"
Write-Host "Client is expected at: http://$($rootEnv.CLIENT_PC_IP):5173"
