# GameHub Website Setup Instructions

## Prerequisites
- XAMPP installed and running
- Apache and MySQL services started in XAMPP Control Panel

## Step 1: Database Setup

### Option A: Using phpMyAdmin (Recommended)
1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on "SQL" tab at the top
3. Copy the entire contents of `database/setup.sql` file
4. Paste it into the SQL query box
5. Click "Go" to execute the SQL commands

### Option B: Using MySQL Command Line
1. Open Command Prompt as Administrator
2. Navigate to XAMPP MySQL bin directory:
   ```
   cd C:\xampp\mysql\bin
   ```
3. Connect to MySQL:
   ```
   mysql -u root -p
   ```
4. When prompted for password, just press Enter (default is no password)
5. Execute the setup file:
   ```
   source C:\xampp\htdocs\GameHub\database\setup.sql
   ```

## Step 2: Verify Database Configuration
The database connection is configured in `config/database.php` with these settings:
- Host: localhost
- Database: video_game_storeg
- Username: root
- Password: (empty)

If your MySQL settings are different, edit `config/database.php` accordingly.

## Step 3: Access the Website
1. Make sure Apache and MySQL are running in XAMPP
2. Open your web browser
3. Navigate to: `http://localhost/GameHub/`

## Step 4: Test User Accounts
The setup creates a default test user:
- Email: `test@gamehub.com`
- Password: `test123`

## Step 5: Admin Access
Check if there's an admin account created. You can access admin features at:
`http://localhost/GameHub/admin/`

## Troubleshooting

### Database Connection Issues
- Ensure MySQL service is running in XAMPP
- Check that the database name matches in `config/database.php`
- Verify MySQL credentials

### Page Not Loading
- Ensure Apache service is running
- Check that the project is in the correct XAMPP htdocs directory
- Try accessing: `http://localhost/GameHub/index.html`

### PHP Errors
- Check XAMPP error logs in: `C:\xampp\apache\logs\error.log`
- Ensure PHP is properly configured in XAMPP

## Features to Test
1. **Homepage**: Browse products, search functionality
2. **User Registration**: Create new account at `pages/register.html`
3. **User Login**: Login with test account at `pages/login.html`
4. **Cart/Wishlist**: Add products to cart and wishlist
5. **Admin Panel**: Access admin dashboard (if available)

## File Structure Overview
- `index.html` - Main homepage
- `pages/` - All individual pages (login, register, etc.)
- `api/` - PHP backend API endpoints
- `config/` - Database and authentication configuration
- `assets/` - CSS, JavaScript, and images
- `admin/` - Administrative interface
