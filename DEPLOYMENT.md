# Deployment Guide - Hostinger

This guide walks you through deploying the Medical School File Management System to Hostinger shared hosting.

## Prerequisites

Before starting:
- Active Hostinger hosting account
- FTP/SFTP credentials
- SSH access (if available on your plan)
- MySQL database created in Hostinger control panel

## Step 1: Prepare Local Project

### 1.1 Optimize for Production

```bash
# Install production dependencies only
composer install --no-dev --optimize-autoloader

# Clear development caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 1.2 Create Production .env

Create a `.env` file specifically for production:

```env
APP_NAME="Medical School File Manager"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database from Hostinger control panel
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_medical
DB_USERNAME=u123456789_medical
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Storage Provider
STORAGE_PROVIDER=local

# File Upload Limits
MAX_FILE_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,mp4,avi,mov

# School Branding (can be updated via admin panel)
SCHOOL_NAME="Medical School"
SCHOOL_TAGLINE="File Management System"
SCHOOL_LOGO=
SCHOOL_FOOTER="© 2024 Medical School. All rights reserved."
```

**IMPORTANT**: Do NOT upload your local .env file. Create it fresh on the server.

## Step 2: Create MySQL Database in Hostinger

1. Login to Hostinger control panel (hPanel)
2. Go to **Databases → MySQL Databases**
3. Click **Create New Database**
   - Database Name: `medical_file_manager` (Hostinger will prefix it)
   - Database User: Create new user with strong password
   - Grant all privileges
4. Note down:
   - Full database name (e.g., `u123456789_medical`)
   - Username (e.g., `u123456789_medical`)
   - Password
   - Hostname (usually `localhost`)

## Step 3: Upload Files via FTP/SFTP

### 3.1 Using FileZilla or Similar FTP Client

1. **Connect to your Hostinger server**:
   - Host: `ftp.yourdomain.com` or server IP
   - Username: Your Hostinger FTP username
   - Password: Your Hostinger FTP password
   - Port: 21 (FTP) or 22 (SFTP)

2. **Navigate to your domain's directory**:
   - Usually: `/public_html/` or `/domains/yourdomain.com/public_html/`

3. **Upload ALL files EXCEPT**:
   - `.git` folder (if present)
   - `node_modules` (if present)
   - Your local `.env` file
   - `vendor` folder (we'll regenerate it)

### 3.2 Recommended Upload Structure

Upload to: `/public_html/medical-system/`

This keeps your application separate from the public folder.

## Step 4: Configure Server via SSH

### 4.1 Connect via SSH

```bash
ssh u123456789@yourdomain.com
```

If your Hostinger plan doesn't include SSH, use the File Manager in hPanel.

### 4.2 Navigate to Your Application

```bash
cd /home/u123456789/public_html/medical-system
```

### 4.3 Install Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

If `composer` is not available globally:
```bash
php composer.phar install --no-dev --optimize-autoloader
```

Or download Composer first:
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### 4.4 Create and Configure .env File

```bash
cp .env.example .env
nano .env  # or use vi, vim, or hPanel File Manager
```

Update with your Hostinger database credentials.

### 4.5 Generate Application Key

```bash
php artisan key:generate
```

### 4.6 Run Migrations

```bash
php artisan migrate --seed --force
```

The `--force` flag is required in production.

This will:
- Create all database tables
- Seed academic levels (General, Level 1-7)
- Create demo users (remember to change passwords!)

### 4.7 Create Storage Symlink

```bash
php artisan storage:link
```

### 4.8 Set Correct Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

If you encounter permission issues:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Or set ownership (if you have sudo access):
```bash
chown -R $USER:$USER storage bootstrap/cache
```

## Step 5: Configure Domain to Point to /public

### Method 1: Using .htaccess (Subdirectory Installation)

If installed in a subdirectory, create `.htaccess` in your public_html root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ medical-system/public/$1 [L]
</IfModule>
```

### Method 2: Document Root Configuration (Recommended)

1. Login to Hostinger hPanel
2. Go to **Advanced → PHP Configuration** or **Website Settings**
3. Find **Document Root** or **Public HTML Path** setting
4. Change from `/public_html` to `/public_html/medical-system/public`
5. Save changes

### Method 3: Subdomain Setup

1. Create a subdomain in hPanel (e.g., `files.yourdomain.com`)
2. Set its document root to `/public_html/medical-system/public`

## Step 6: PHP Configuration

### 6.1 Update PHP Version

Ensure PHP 8.1+ is selected:
1. Go to **hPanel → Advanced → PHP Configuration**
2. Select **PHP 8.1** or higher
3. Save

### 6.2 Increase Upload Limits

Create or edit `php.ini` in your application root:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M
```

Or use `.user.ini` in `/public` folder:

```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### 6.3 Enable Required PHP Extensions

Ensure these are enabled in PHP Configuration:
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `fileinfo`

## Step 7: Secure Your Installation

### 7.1 Protect Sensitive Files

Create/update `.htaccess` in application root (not public):

```apache
# Deny access to sensitive files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "composer\.json|composer\.lock">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 7.2 Change Default User Passwords

1. Login as admin: `admin@medicalschool.com` / `password`
2. Go to **Admin → Users**
3. Edit each demo user and change passwords
4. Or delete demo users and create new ones

### 7.3 Secure .env File

```bash
chmod 600 .env
```

### 7.4 Enable HTTPS

1. In Hostinger hPanel, go to **SSL**
2. Enable **Free SSL Certificate**
3. Update `APP_URL` in `.env` to use `https://`

## Step 8: Post-Deployment Checklist

- [ ] Application loads without errors
- [ ] Can login with demo credentials
- [ ] Change all demo user passwords
- [ ] Upload test file
- [ ] Download test file
- [ ] Create test folder
- [ ] Test folder permissions
- [ ] Configure school branding (Admin → Settings)
- [ ] Upload school logo
- [ ] Create actual user accounts
- [ ] Delete or disable demo accounts
- [ ] Test on mobile devices
- [ ] Enable HTTPS and force redirect
- [ ] Set up regular database backups

## Step 9: Configure Google Drive (Optional)

If using Google Drive storage:

1. **Install Google API Client** (if not already installed):
```bash
composer require google/apiclient
```

2. **Update .env**:
```env
STORAGE_PROVIDER=gdrive
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

3. **Clear config cache**:
```bash
php artisan config:clear
```

## Step 10: Maintenance & Backups

### Database Backups

Use Hostinger's built-in backup feature or create manual backups:

```bash
mysqldump -u u123456789_medical -p u123456789_medical > backup_$(date +%Y%m%d).sql
```

### File Backups

Backup the `storage/app` directory regularly:
```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app
```

### Automated Backups via Cron

Set up in **hPanel → Advanced → Cron Jobs**:

```bash
# Daily database backup at 2 AM
0 2 * * * cd /home/u123456789/public_html/medical-system && mysqldump -u username -ppassword database_name > backup_$(date +\%Y\%m\%d).sql 2>&1
```

## Troubleshooting

### 500 Internal Server Error

1. Check `.htaccess` in `/public` folder exists
2. Verify file permissions (755 for directories, 644 for files)
3. Check PHP error logs in hPanel
4. Ensure `APP_KEY` is set in `.env`
5. Run: `php artisan config:clear`

### Database Connection Error

1. Verify database credentials in `.env`
2. Ensure database user has all privileges
3. Check hostname (usually `localhost` on shared hosting)
4. Test connection with command:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

### File Upload Not Working

1. Check `MAX_FILE_SIZE` in `.env`
2. Verify PHP upload limits in `php.ini`
3. Ensure `storage/app` has write permissions
4. Check available disk space

### CSS/JS Not Loading

1. Ensure URL in `.env` matches your domain
2. Check `/public/css` and `/public/js` folders exist
3. Verify `.htaccess` in `/public` is correct
4. Clear browser cache

### Storage Link Broken

```bash
rm public/storage
php artisan storage:link
```

## Performance Optimization

### Enable OPcache

In `php.ini` or via hPanel:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Config Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Note**: Clear these caches when updating `.env`:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support Resources

- **Hostinger Knowledge Base**: https://support.hostinger.com/
- **Laravel Documentation**: https://laravel.com/docs
- **Project README**: See README.md in project root

## Common Hostinger Locations

- **Application Root**: `/home/u123456789/public_html/`
- **Public Folder**: `/home/u123456789/public_html/medical-system/public/`
- **Logs**: `storage/logs/laravel.log`
- **PHP Errors**: Check in hPanel → Files → Error Logs

---

**Deployment Complete!**

Your Medical School File Management System should now be live and accessible at your domain.
