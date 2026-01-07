# Railway Deployment Script for BNHS Document Request System (Windows)
# This script helps automate the deployment to Railway

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Railway Deployment Helper" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Railway CLI is installed
Write-Host "Checking Railway CLI installation..." -ForegroundColor Yellow
if (!(Get-Command railway -ErrorAction SilentlyContinue)) {
    Write-Host "Error: Railway CLI is not installed." -ForegroundColor Red
    Write-Host "Install it with: npm install -g @railway/cli" -ForegroundColor Yellow
    Write-Host "Or visit: https://docs.railway.app/develop/cli" -ForegroundColor Yellow
    exit 1
}
Write-Host "✓ Railway CLI is installed" -ForegroundColor Green

# Check if logged in
Write-Host "Checking Railway login status..." -ForegroundColor Yellow
try {
    railway whoami | Out-Null
    Write-Host "✓ Logged in to Railway" -ForegroundColor Green
} catch {
    Write-Host "You need to login to Railway first." -ForegroundColor Yellow
    Write-Host "Running: railway login" -ForegroundColor Yellow
    railway login
}

# Link to project (if not already linked)
if (!(Test-Path "railway.json") -or !(Test-Path ".railway")) {
    Write-Host "Linking to Railway project..." -ForegroundColor Yellow
    Write-Host "If you don't have a project yet, create one at: https://railway.app/new" -ForegroundColor Yellow
    railway link
}
Write-Host "✓ Project linked" -ForegroundColor Green

# Run tests before deployment
Write-Host ""
Write-Host "Running tests before deployment..." -ForegroundColor Yellow
try {
    npm run test:all
    Write-Host "✓ All tests passed" -ForegroundColor Green
} catch {
    Write-Host "✗ Tests failed. Fix errors before deploying." -ForegroundColor Red
    $continue = Read-Host "Do you want to continue anyway? (y/N)"
    if ($continue -ne "y" -and $continue -ne "Y") {
        exit 1
    }
}

# Build assets
Write-Host ""
Write-Host "Building frontend assets..." -ForegroundColor Yellow
try {
    npm run build
    Write-Host "✓ Build successful" -ForegroundColor Green
} catch {
    Write-Host "✗ Build failed" -ForegroundColor Red
    exit 1
}

# Show current environment
Write-Host ""
Write-Host "Current Railway environment:" -ForegroundColor Yellow
railway status

# Confirm deployment
Write-Host ""
Write-Host "Ready to deploy to Railway!" -ForegroundColor Yellow
$deploy = Read-Host "Continue with deployment? (y/N)"
if ($deploy -ne "y" -and $deploy -ne "Y") {
    Write-Host "Deployment cancelled." -ForegroundColor Yellow
    exit 0
}

# Deploy
Write-Host ""
Write-Host "Deploying to Railway..." -ForegroundColor Yellow
railway up

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Deployment complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Check deployment logs: railway logs" -ForegroundColor White
Write-Host "2. Open your app: railway open" -ForegroundColor White
Write-Host "3. Check environment variables: railway variables" -ForegroundColor White
Write-Host ""
Write-Host "Monitor your deployment at: https://railway.app/dashboard" -ForegroundColor Cyan
