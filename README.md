# Medical School File Management System

A professional, Neo-Brutalism UI file management system built with Laravel for medical schools. Supports nested folders, role-based access control, and interchangeable cloud storage providers.

## Features

### Core Functionality
- **Flexible Folder System**: Create unlimited nested folder structures organized by academic levels (1-7)
- **File Management**: Upload, download, preview, and organize files with drag-and-drop support
- **Soft Delete & Archive**: Files can be archived and restored by admins
- **Multi-Provider Storage**: Switch between local storage and Google Drive via configuration

### User Roles & Permissions
- **Admin**: Full system control, user management, permanent file deletion
- **Level Moderator**: Manage files/folders within assigned academic levels
- **Folder Owner**: Users who create folders automatically own them with permission management
- **Standard User**: Upload, download, and manage own files

### Neo-Brutalism UI
- **Color Palette**:
  - Tea Green (#BDD9BF) - Background
  - Charcoal Blue (#2E4052) - Primary UI
  - Golden Pollen (#FFC857) - Accents
  - White (#FFFFFF) - Cards
  - Midnight Violet (#412234) - Borders & Text
- **Design Elements**: 4px borders, 8px hard shadows, bold Space Grotesk typography
- **Fully Responsive**: Mobile-first design with adaptive layouts

### Admin Features
- **User Management**: Create, edit, delete users and assign roles
- **Settings**: Configure school branding (name, logo, tagline, footer)
- **Statistics Dashboard**: View system-wide metrics and recent activity
- **Archive Management**: Restore or permanently delete archived files

## Technology Stack

- **Framework**: Laravel 10.x
- **PHP**: 8.1+
- **Database**: MySQL/MariaDB
- **CSS**: Custom Neo-Brutalism design system
- **JavaScript**: Vanilla JS for drag-and-drop
- **Storage**: Local + Google Drive support

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Node.js & NPM (optional, for asset compilation)

### Local Development Setup

1. **Clone the repository**
```bash
git clone <repository-url>
cd Medical-School-File-Manager
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_file_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

This creates:
- All necessary database tables
- Academic levels (General, Level 1-7)
- Demo users:
  - Admin: `admin@medicalschool.com` / `password`
  - Moderator: `moderator@medicalschool.com` / `password`
  - Student: `student@medicalschool.com` / `password`

**IMPORTANT**: Change these passwords immediately in production!

6. **Create storage symlink**
```bash
php artisan storage:link
```

7. **Set permissions**
```bash
chmod -R 775 storage bootstrap/cache
```

8. **Start development server**
```bash
php artisan serve
```

Visit `http://localhost:8000`

## Storage Providers

### Local Storage (Default)
Files are stored in `storage/app/public` directory.

```env
STORAGE_PROVIDER=local
```

### Google Drive
For cloud storage integration:

1. **Install Google API Client** (if not already installed)
```bash
composer require google/apiclient
```

2. **Configure Google Drive credentials** in `.env`:
```env
STORAGE_PROVIDER=gdrive
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

3. **Obtain Google Drive API credentials**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project
   - Enable Google Drive API
   - Create OAuth 2.0 credentials
   - Generate refresh token using OAuth playground

No code changes required - the system automatically uses the configured provider.

## Folder Permissions

### How Permissions Work

1. **Automatic Ownership**: Users who create folders become owners automatically
2. **Permission Levels**:
   - `read`: View folder contents and download files
   - `write`: Upload files and create subfolders
   - `delete`: Delete files and folders

3. **Granting Permissions**:
   - Navigate to folder details page
   - Click "Manage Permissions"
   - Select user and permission levels
   - Save changes

4. **Permission Hierarchy**:
   - Admins: Full access to everything
   - Level Moderators: Full access to their assigned levels
   - Folder Owners: Full access to their folders
   - Standard Users: Access based on granted permissions

## Academic Levels

The system includes 8 predefined levels:
- **General**: Files not specific to any academic year
- **Level 1-7**: Corresponding to medical school years

Levels are used to:
- Organize content by academic year
- Assign moderator responsibilities
- Filter and browse content efficiently

Levels do NOT restrict folder structure - you can nest folders freely regardless of level.

## Archive System

### Soft Delete Workflow
1. User deletes a file → File moves to archive (soft delete)
2. File remains in database with `is_archived=true` and `deleted_at` timestamp
3. Only admins can access the archive

### Archive Management
- **Restore**: Brings file back to its original location
- **Permanent Delete**: Removes file from database and storage (admins only)

## Deployment to Hostinger

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

### Quick Deployment Steps

1. **Upload files** via FTP/SFTP to your hosting directory
2. **Install dependencies**:
```bash
composer install --no-dev --optimize-autoloader
```

3. **Configure environment**:
   - Copy `.env.example` to `.env`
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure database credentials
   - Generate app key: `php artisan key:generate`

4. **Run migrations**:
```bash
php artisan migrate --seed --force
```

5. **Set permissions**:
```bash
chmod -R 755 storage bootstrap/cache
```

6. **Point domain to `/public` folder** in Hostinger control panel

7. **Test the application**

## File Upload Limits

Configure in `.env`:
```env
MAX_FILE_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,txt,zip,mp4,avi,mov
```

**Note**: Also check PHP and web server upload limits:
- `php.ini`: `upload_max_filesize` and `post_max_size`
- Apache/Nginx: `client_max_body_size`

## Admin Configuration

### Updating School Branding

1. Login as admin
2. Navigate to **Admin → Settings**
3. Configure:
   - School Name
   - School Tagline
   - Upload Logo (PNG, JPG, SVG - max 2MB)
   - Footer Text
4. Click "Save Branding Settings"

Changes apply immediately across the entire application.

## User Management

### Creating Users (Admin)
1. Go to **Admin → Users → Create User**
2. Fill in user details
3. Assign role (Admin, Moderator, User)
4. For moderators: Select academic levels they can manage
5. Save

### Assigning Moderators to Levels
1. Edit user or create new moderator
2. Select "Moderator" role
3. Choose one or more academic levels
4. Moderators can manage all folders/files in their assigned levels

## Troubleshooting

### Storage Link Not Working
```bash
php artisan storage:link
```

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Failed
- Verify database credentials in `.env`
- Ensure database exists
- Check database server is running

### File Upload Fails
- Check `MAX_FILE_SIZE` in `.env`
- Verify PHP `upload_max_filesize` and `post_max_size`
- Ensure storage directory has write permissions

### Google Drive Not Working
- Verify all Google Drive credentials in `.env`
- Ensure Google Drive API is enabled
- Check refresh token is valid
- Install `google/apiclient` if missing

## Security Best Practices

1. **Change default passwords** immediately after installation
2. **Set `APP_DEBUG=false`** in production
3. **Use HTTPS** for production deployments
4. **Regular backups** of database and uploaded files
5. **Keep Laravel and dependencies updated**
6. **Restrict .env file permissions**: `chmod 600 .env`
7. **Use strong database passwords**
8. **Enable CSRF protection** (enabled by default)

## License

MIT License

## Support

For issues or questions:
1. Check this README and DEPLOYMENT.md
2. Review Laravel documentation: https://laravel.com/docs
3. Create an issue in the repository

## Credits

Built with Laravel and designed with Neo-Brutalism principles for medical education institutions.
