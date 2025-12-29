# UIU Supplements - Deployment Guide for yzz.me

## Production Environment Details

- **Domain**: `uiusupplements.yzz.me`
- **Database Host**: `sql105.yzz.me`
- **Database Name**: `yzzme_40788122_uiusupplements`
- **Database Username**: `yzzme_40788122`
- **Database Password**: `Sharif2025`

---

## Pre-Deployment Checklist

### 1. Database Setup
1. Log in to your yzz.me cPanel or database manager
2. Create the database `yzzme_40788122_uiusupplements` if it doesn't exist
3. Import your local database:
   - Export from local phpMyAdmin: `uiusupplements`
   - Import to yzz.me: `yzzme_40788122_uiusupplements`

### 2. Configuration File
The `config.php` file has been created with production credentials:
```php
// Production settings (active)
define('DB_HOST', 'sql105.yzz.me');
define('DB_USERNAME', 'yzzme_40788122');
define('DB_PASSWORD', 'Sharif2025');
define('DB_NAME', 'yzzme_40788122_uiusupplements');
define('BASE_URL', 'http://uiusupplements.yzz.me');
```

To switch back to local development, comment out the production settings and uncomment the development settings in `config.php`.

---

## Deployment Steps

### Step 1: Export Local Database
```bash
# Using phpMyAdmin or command line:
mysqldump -u root uiusupplements > uiusupplements_backup.sql
```

### Step 2: Upload Files
Upload all project files to your yzz.me hosting via:
- FTP (FileZilla recommended)
- cPanel File Manager

**Important directories to upload:**
- All PHP files in root
- `/api/` directory
- `/assets/` directory (CSS, JS)
- `/logo/` directory
- `/uploads/` directory (create if needed)
- `/LostandFound/` directory
- `config.php` (with production credentials)

### Step 3: Import Database
1. Access phpMyAdmin on yzz.me
2. Select database: `yzzme_40788122_uiusupplements`
3. Go to "Import" tab
4. **IMPORTANT**: Before importing, edit your SQL file to remove all `CREATE TRIGGER` statements:
   - Remove `after_bargain_insert` trigger
   - Remove any other `CREATE TRIGGER ... END` blocks
   - (Free hosting doesn't allow trigger creation)
5. Upload your edited `.sql` file
6. Click "Go" to import

### Step 4: Set Directory Permissions
Ensure these directories are writable (chmod 755 or 777):
```
/uploads/
/LostandFound/imgOfLost/
/uploads/claims/
/uploads/rooms/
/uploads/products/
/uploads/mentors/
```

### Step 5: Test the Application
1. Open `http://uiusupplements.yzz.me`
2. Test login functionality
3. Test database operations (add/edit/delete)
4. Test file uploads
5. Test all major features

---

## Files Updated for Deployment

The following PHP files have been updated to use centralized `config.php`:

### Root Directory
- `config.php` - **NEW** (centralized database configuration)
- `login.php`
- `registeruser.php`
- `createuseraccount.php`
- `chat.php`
- `delete.php`
- `fetch_messages.php`
- `send_message.php`
- `fetch_claims.php`
- `insert_data.php`
- `searchroom.php`
- `rentroom.php`
- `shulltequery.php`
- `settings.php`
- `useraccount.php`
- `uiusupplementhomepage.php`
- `adminpanel.php`
- `lostandfound.php`
- `SellAndExchange.php`
- `mybargains.php`
- `mydeals.php`

### API Directory (`/api/`)
- `rooms.php`
- `rentedrooms.php`
- `mentors.php`
- `notifications.php`
- `messages.php`
- `offers.php`
- `bargains.php`
- `deals.php`
- `deal_chat.php`
- `jobs.php`
- `admin_rooms.php`
- `admin_users.php`

---

## Switching Between Environments

### For Production (yzz.me)
In `config.php`, ensure production settings are active:
```php
// Production settings
define('DB_HOST', 'sql105.yzz.me');
define('DB_USERNAME', 'yzzme_40788122');
define('DB_PASSWORD', 'Sharif2025');
define('DB_NAME', 'yzzme_40788122_uiusupplements');
```

### For Local Development
In `config.php`, comment out production and uncomment development:
```php
// Development settings (XAMPP)
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'uiusupplements');
```

---

## Troubleshooting

### Database Connection Issues
1. Verify database credentials in `config.php`
2. Check if database server permits external connections
3. Verify database and tables exist

### File Upload Issues
1. Check directory permissions (755 or 777)
2. Verify upload directories exist
3. Check PHP upload limits in hosting

### Session Issues
1. Ensure `session_start()` is called before any output
2. Check for any whitespace before `<?php` tags

### 500 Internal Server Error
1. Check `.htaccess` file compatibility
2. Verify PHP version compatibility
3. Check error logs in cPanel

---

## Support

For issues or questions, contact the developer.

**Last Updated**: December 2024
