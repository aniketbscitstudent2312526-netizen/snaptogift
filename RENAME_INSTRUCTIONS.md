# Rename Project to SnapToGift

## Option 1: Automatic Rename (Run Batch File)
1. Close any open files in your IDE
2. Double-click `rename_project.bat` in this folder
3. The folder will be renamed from `ai_ecommerce` to `snaptogift`
4. New URL: `http://localhost/snaptogift/`

## Option 2: Manual Rename
1. Stop Apache in XAMPP Control Panel
2. Navigate to `C:\xampp\htdocs\`
3. Right-click `ai_ecommerce` folder → Rename → `snaptogift`
4. Start Apache in XAMPP Control Panel
5. Visit: `http://localhost/snaptogift/`

## Database Update Required
After renaming, update your database:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on `ai_ecommerce` database
3. Go to Operations tab
4. Under "Rename database to", type: `snaptogift`
5. Click "Go"

OR import fresh:
1. Drop old `ai_ecommerce` database
2. Create new `snaptogift` database
3. Import `database.sql`
4. Import `database_updates.sql`

## Changes Made
- `config.php` - Database name updated to `snaptogift`
- `database.sql` - Database name updated to `snaptogift`
- Brand name `SnapToGift` already set in all files

## New URLs
- Website: `http://localhost/snaptogift/`
- Admin: `http://localhost/snaptogift/admin/login.php`
- phpMyAdmin: `http://localhost/phpmyadmin`

## Troubleshooting
If you get "Access denied" errors after rename:
1. Clear browser cache
2. Restart XAMPP Apache
3. Check config.php has `define('DB_NAME', 'snaptogift');`
