#!/bin/bash

###############################################################################
# Manual Deployment Script for Medical School File Management System
#
# This script helps deploy your application to Hostinger via SSH/FTP
#
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
PROJECT_NAME="Medical School File Manager"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ${PROJECT_NAME} - Deployment Script${NC}"
echo -e "${GREEN}  Environment: ${ENVIRONMENT}${NC}"
echo -e "${GREEN}========================================${NC}"

# Load environment-specific configuration
if [ -f ".env.${ENVIRONMENT}" ]; then
    source ".env.${ENVIRONMENT}"
else
    echo -e "${YELLOW}Warning: .env.${ENVIRONMENT} not found. Using default values.${NC}"
fi

# Prompt for server details if not set
read -p "SSH Host (e.g., yourdomain.com): " SSH_HOST
read -p "SSH Username: " SSH_USERNAME
read -p "SSH Port [22]: " SSH_PORT
SSH_PORT=${SSH_PORT:-22}
read -p "Remote directory (e.g., /home/user/public_html): " REMOTE_DIR

echo -e "\n${YELLOW}Starting deployment...${NC}\n"

# Step 1: Run tests (optional)
echo -e "${GREEN}[1/8] Running tests...${NC}"
if [ -d "tests" ]; then
    # php artisan test
    echo "Skipping tests (uncomment to enable)"
else
    echo "No tests directory found, skipping..."
fi

# Step 2: Install production dependencies
echo -e "\n${GREEN}[2/8] Installing production dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Step 3: Clear local caches
echo -e "\n${GREEN}[3/8] Clearing local caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Step 4: Create deployment archive
echo -e "\n${GREEN}[4/8] Creating deployment archive...${NC}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ARCHIVE_NAME="deploy_${TIMESTAMP}.tar.gz"

tar -czf "$ARCHIVE_NAME" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='*.tar.gz' \
    --exclude='deploy.sh' \
    .

echo "Archive created: $ARCHIVE_NAME"

# Step 5: Upload to server
echo -e "\n${GREEN}[5/8] Uploading to server...${NC}"
scp -P "$SSH_PORT" "$ARCHIVE_NAME" "${SSH_USERNAME}@${SSH_HOST}:~/"

# Step 6: Extract and setup on server
echo -e "\n${GREEN}[6/8] Extracting files on server...${NC}"
ssh -p "$SSH_PORT" "${SSH_USERNAME}@${SSH_HOST}" << EOF
    set -e
    cd "$REMOTE_DIR"

    # Backup current installation
    if [ -d "backup" ]; then
        rm -rf backup_old
        mv backup backup_old
    fi
    mkdir -p backup
    cp -r * backup/ 2>/dev/null || true

    # Extract new files
    tar -xzf ~/"$ARCHIVE_NAME"
    rm ~/"$ARCHIVE_NAME"

    # Set permissions
    chmod -R 755 storage bootstrap/cache

    echo "Files extracted successfully!"
EOF

# Step 7: Run post-deployment commands
echo -e "\n${GREEN}[7/8] Running post-deployment commands...${NC}"
ssh -p "$SSH_PORT" "${SSH_USERNAME}@${SSH_HOST}" << 'EOF'
    set -e
    cd "$REMOTE_DIR"

    # Clear caches
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    # Cache config and routes
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Create storage link if needed
    php artisan storage:link

    echo "Post-deployment commands completed!"
EOF

# Step 8: Cleanup
echo -e "\n${GREEN}[8/8] Cleaning up...${NC}"
rm "$ARCHIVE_NAME"

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  Deployment Completed Successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Test your application at your domain"
echo "2. Check error logs if any issues occur"
echo "3. Update database if needed: php artisan migrate --force"
echo -e "\n${YELLOW}Important:${NC} Don't forget to:"
echo "- Update .env file on the server with correct settings"
echo "- Change default user passwords"
echo "- Enable HTTPS if not already enabled"

exit 0
