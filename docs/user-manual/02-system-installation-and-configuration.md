# SECTION 2: SYSTEM INSTALLATION & CONFIGURATION

## Table of Contents
- [System Requirements](#system-requirements)
- [Installation Steps](#installation-steps)
- [Initial Database Setup](#initial-database-setup)
- [Starting the System](#starting-the-system)
- [Post-Installation Configuration](#post-installation-configuration)
- [Production Deployment](#production-deployment)
- [Troubleshooting Installation Issues](#troubleshooting-installation-issues)

---

## System Requirements

Before installing the HR Management System, ensure your server meets the following requirements:

### Server Requirements

#### Minimum Hardware
- **CPU**: 2 cores (4 cores recommended for production)
- **RAM**: 2GB (4GB+ recommended for production)
- **Storage**: 10GB free disk space (20GB+ recommended)
- **Network**: Stable internet connection for initial setup

#### Software Requirements

**Web Server**
- Apache 2.4+ with `mod_rewrite` enabled, OR
- Nginx 1.18+ with proper PHP-FPM configuration

**PHP Requirements**
- PHP 8.2 or higher
- PHP Extensions:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - PDO_MySQL (for MySQL) or PDO_SQLite (for SQLite)
  - Tokenizer
  - XML
  - GD or Imagick (for image processing)
  - Zip

**Database**
- MySQL 8.0+ or MariaDB 10.3+, OR
- SQLite 3.35+

**Node.js & NPM**
- Node.js 18.x or higher
- NPM 9.x or higher

**Composer**
- Composer 2.5+

**Optional (for Fingerprint Integration)**
- Python 3.8+
- `pyzk` library for ZKTeco device integration

### Client Requirements (End Users)

**Supported Browsers**
- Google Chrome 100+ (Recommended)
- Mozilla Firefox 100+
- Microsoft Edge 100+
- Safari 15+ (macOS/iOS)

**Screen Resolution**
- Minimum: 1280x720
- Recommended: 1920x1080 or higher

---

## Installation Steps

### Step 1: Download/Clone the Repository

Choose one of the following methods:

**Option A: Using Git (Recommended)**
```bash
# Clone the repository
git clone https://github.com/your-organization/dasher.git

# Navigate to the project directory
cd dasher
```

**Option B: Download ZIP**
1. Download the ZIP file from your repository
2. Extract to your desired location
3. Navigate to the extracted directory

### Step 2: Install PHP Dependencies

```bash
# Install Composer dependencies
composer install

# For production, use:
composer install --optimize-autoloader --no-dev
```

**Expected Output:**
```
Loading composer repositories with package information
Installing dependencies from lock file
...
Generating optimized autoload files
```

⚠️ **Note**: This may take several minutes depending on your internet connection.

### Step 3: Install JavaScript Dependencies

```bash
# Install NPM packages
npm install

# For production, use:
npm ci
```

**Expected Output:**
```
added 147 packages in 45s
```

### Step 4: Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env
```

Edit the `.env` file with your preferred text editor:

```bash
# Using nano
nano .env

# Using vim
vim .env
```

**Important Environment Variables:**

```env
# Application Settings
APP_NAME="HR Management System"
APP_ENV=local                    # Change to 'production' for live deployment
APP_KEY=                         # Will be generated in next step
APP_DEBUG=true                   # Set to 'false' in production
APP_URL=http://localhost:8000    # Your application URL

# Database Configuration (MySQL Example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dasher_db
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Database Configuration (SQLite Alternative)
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Queue Configuration
QUEUE_CONNECTION=database        # Uses database for job queue

# Session Configuration
SESSION_DRIVER=database          # Stores sessions in database

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Fingerprint Device Integration (Optional)
FINGERPRINT_ENABLED=false
FINGERPRINT_DEVICE_IP=192.168.1.100
FINGERPRINT_DEVICE_PORT=4370
```

💡 **Tip**: For development, SQLite is easier to set up. For production, use MySQL/MariaDB.

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

**Expected Output:**
```
Application key set successfully.
```

This command generates a secure encryption key and updates your `.env` file.

### Step 6: Create Database

**For MySQL:**
```bash
# Log into MySQL
mysql -u root -p

# Create database
CREATE DATABASE dasher_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create database user (optional but recommended)
CREATE USER 'dasher_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON dasher_db.* TO 'dasher_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**For SQLite:**
```bash
# Create database file
touch database/database.sqlite

# Ensure proper permissions
chmod 664 database/database.sqlite
```

### Step 7: Set Directory Permissions

```bash
# Storage and cache directories must be writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Ensure web server user owns the files (adjust 'www-data' to your web server user)
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## Initial Database Setup

### Step 1: Run Migrations

```bash
# Run all database migrations
php artisan migrate
```

**Expected Output:**
```
Migration table created successfully.
Migrating: 2024_01_01_000000_create_users_table
Migrated:  2024_01_01_000000_create_users_table (45.67ms)
...
(41 migrations total)
```

⚠️ **Warning**: This creates all database tables. Do not interrupt this process.

### Step 2: Seed Initial Data

```bash
# Seed the database with initial data
php artisan db:seed
```

This will populate:
- Permission categories and permissions
- Default roles (admin, staff, etc.)
- Sample location data (optional)
- System configuration defaults

**Expected Output:**
```
Seeding: Database\Seeders\PermissionSeeder
Permissions seeded successfully!
...
Database seeding completed successfully.
```

### Step 3: Create Admin User

**Option A: Using Seeder (if configured)**
```bash
php artisan db:seed --class=UserSeeder
```

**Option B: Manual Creation**
```bash
php artisan tinker
```

Then in the Tinker console:
```php
$user = new App\Models\User();
$user->name = 'System Administrator';
$user->email = 'admin@example.com';
$user->password = Hash::make('SecurePassword123!');
$user->save();

// Assign admin role
$user->assignRole('admin');

exit
```

**Default Admin Credentials (if using seeder):**
- **Email**: admin@example.com
- **Password**: password (⚠️ Change immediately after first login!)

### Step 4: Generate IDE Helper Files (Optional)

For better code completion in your IDE:

```bash
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

---

## Starting the System

### Development Environment

**Option A: Using Composer Script (Recommended)**
```bash
composer dev
```

This single command starts 4 concurrent processes:
1. **Development Server** (`php artisan serve`) - Port 8000
2. **Queue Worker** (`php artisan queue:listen`) - Background jobs
3. **Log Viewer** (`php artisan pail`) - Real-time logs
4. **Vite Dev Server** (`npm run dev`) - Asset compilation with HMR

**Expected Output:**
```
Starting development servers...
[Server] Laravel development server started: http://127.0.0.1:8000
[Queue] Processing jobs from database queue...
[Pail] Listening for logs...
[Vite] VITE v5.0.0  ready in 823 ms
```

**Option B: Individual Services**

Run each service in a separate terminal:

```bash
# Terminal 1: Development Server
php artisan serve

# Terminal 2: Queue Worker
php artisan queue:listen

# Terminal 3: Asset Compilation
npm run dev

# Terminal 4: Log Viewer (optional)
php artisan pail
```

### Accessing the System

1. Open your browser and navigate to: **http://localhost:8000**
2. You should see the login page
3. Log in with your admin credentials
4. **First Login Checklist:**
   - [ ] Change default admin password
   - [ ] Configure system settings
   - [ ] Set up organization structure
   - [ ] Create user accounts

### Building for Production

```bash
# Build optimized assets
npm run build

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Post-Installation Configuration

### 1. Application URL Configuration

Update `APP_URL` in `.env` to match your actual domain:

```env
APP_URL=https://yourdomain.com
```

Then clear configuration cache:
```bash
php artisan config:clear
```

### 2. Mail Configuration

Configure email settings for notifications:

**Using Gmail (Example):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="HR Management System"
```

Test email configuration:
```bash
php artisan tinker
Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')->subject('Test');
});
exit
```

### 3. Storage Link

Create symbolic link for public file access:

```bash
php artisan storage:link
```

This creates a link from `public/storage` to `storage/app/public`.

### 4. Schedule Configuration

Add to your server's crontab for scheduled tasks:

```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/dasher && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Queue Worker (Production)

For production, use Supervisor to keep queue worker running:

**Install Supervisor:**
```bash
sudo apt-get install supervisor
```

**Create configuration file:**
```bash
sudo nano /etc/supervisor/conf.d/dasher-queue.conf
```

**Add configuration:**
```ini
[program:dasher-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/dasher/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/dasher/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start dasher-queue:*
```

### 6. Fingerprint Device Integration (Optional)

If using ZKTeco fingerprint devices:

**Install Python dependencies:**
```bash
# Create virtual environment
python3 -m venv zkteco_venv

# Activate virtual environment
source zkteco_venv/bin/activate

# Install pyzk library
pip install pyzk

# Test connection
python3 public/zkteco_sync.py
```

**Configure in `.env`:**
```env
FINGERPRINT_ENABLED=true
FINGERPRINT_DEVICE_IP=192.168.1.100
FINGERPRINT_DEVICE_PORT=4370
```

---

## Production Deployment

### Web Server Configuration

**Apache Configuration:**

Create virtual host configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/dasher/public

    <Directory /var/www/dasher/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dasher-error.log
    CustomLog ${APACHE_LOG_DIR}/dasher-access.log combined
</VirtualHost>
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/dasher/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### SSL Certificate (HTTPS)

**Using Let's Encrypt (Free):**

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache

# For Apache
sudo certbot --apache -d yourdomain.com

# For Nginx
sudo certbot --nginx -d yourdomain.com
```

### Production Environment Settings

Update `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong session encryption
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

# Disable debug features
DEBUGBAR_ENABLED=false
```

### Optimize for Production

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build
```

### Backup Strategy

**Automated Daily Backups:**

Create backup script `/var/scripts/backup-dasher.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/dasher"
APP_DIR="/var/www/dasher"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u dasher_user -p dasher_db > $BACKUP_DIR/database_$DATE.sql

# Backup storage files
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz $APP_DIR/storage/app

# Keep only last 30 days of backups
find $BACKUP_DIR -type f -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /var/scripts/backup-dasher.sh
```

---

## Troubleshooting Installation Issues

### Issue 1: "Permission Denied" Errors

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/dasher
sudo chmod -R 775 storage bootstrap/cache
```

### Issue 2: "Class not found" Errors

**Solution:**
```bash
composer dump-autoload
php artisan cache:clear
```

### Issue 3: NPM Installation Fails

**Solution:**
```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Issue 4: Database Connection Failed

**Check:**
- Database server is running
- Credentials in `.env` are correct
- Database exists
- User has proper permissions

**Test connection:**
```bash
php artisan tinker
DB::connection()->getPdo();
exit
```

### Issue 5: 500 Internal Server Error

**Debug:**
```bash
# Enable debug mode temporarily
# Edit .env
APP_DEBUG=true

# Check error logs
tail -f storage/logs/laravel.log
```

### Issue 6: Assets Not Loading

**Solution:**
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Check browser console for errors
```

### Issue 7: Queue Jobs Not Processing

**Check:**
```bash
# Verify queue worker is running
ps aux | grep queue

# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed
```

### Getting Additional Help

If you encounter issues not covered here:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs: `/var/log/apache2/` or `/var/log/nginx/`
3. Enable debug mode temporarily in `.env`
4. Contact system administrator
5. Review Laravel documentation: https://laravel.com/docs

---

**Next Steps**:
- Proceed to [Section 3: Administrator Guide](./03-administrator-guide.md) for initial system setup
- Or jump to [Section 4: User Management & Access Control](./04-user-management-and-access-control.md) to create users
