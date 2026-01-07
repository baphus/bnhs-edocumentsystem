#!/bin/bash

# Railway Deployment Script for BNHS Document Request System
# This script helps automate the deployment to Railway

set -e

echo "========================================"
echo "Railway Deployment Helper"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo -e "${RED}Error: Railway CLI is not installed.${NC}"
    echo "Install it with: npm install -g @railway/cli"
    echo "Or visit: https://docs.railway.app/develop/cli"
    exit 1
fi

echo -e "${GREEN}✓ Railway CLI is installed${NC}"

# Check if logged in
if ! railway whoami &> /dev/null; then
    echo -e "${YELLOW}You need to login to Railway first.${NC}"
    echo "Running: railway login"
    railway login
fi

echo -e "${GREEN}✓ Logged in to Railway${NC}"

# Link to project (if not already linked)
if [ ! -f "railway.json" ] || [ ! -d ".railway" ]; then
    echo -e "${YELLOW}Linking to Railway project...${NC}"
    echo "If you don't have a project yet, create one at: https://railway.app/new"
    railway link
fi

echo -e "${GREEN}✓ Project linked${NC}"

# Run tests before deployment
echo ""
echo "Running tests before deployment..."
if npm run test:all; then
    echo -e "${GREEN}✓ All tests passed${NC}"
else
    echo -e "${RED}✗ Tests failed. Fix errors before deploying.${NC}"
    read -p "Do you want to continue anyway? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Build assets
echo ""
echo "Building frontend assets..."
if npm run build; then
    echo -e "${GREEN}✓ Build successful${NC}"
else
    echo -e "${RED}✗ Build failed${NC}"
    exit 1
fi

# Show current environment
echo ""
echo "Current Railway environment:"
railway status

# Confirm deployment
echo ""
echo -e "${YELLOW}Ready to deploy to Railway!${NC}"
read -p "Continue with deployment? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled."
    exit 0
fi

# Deploy
echo ""
echo "Deploying to Railway..."
railway up

echo ""
echo -e "${GREEN}========================================"
echo "Deployment complete!"
echo "========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Check deployment logs: railway logs"
echo "2. Open your app: railway open"
echo "3. Check environment variables: railway variables"
echo ""
echo "Monitor your deployment at: https://railway.app/dashboard"
