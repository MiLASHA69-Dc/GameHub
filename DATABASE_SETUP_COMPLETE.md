# GameHub - Game Store Database System

## 🎮 Overview
Your GameHub admin panel is now fully configured to store game data in the database when admins submit the "Add New Game" form.

## 📋 Database Structure

### Games Table
The main `games` table stores all game information with these fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT | Primary key, auto-increment |
| `title` | VARCHAR(255) | Game name |
| `description` | TEXT | Game description |
| `price` | DECIMAL(10,2) | Game price in dollars |
| `discount_price` | DECIMAL(10,2) | Price after discount |
| `discount_percentage` | DECIMAL(5,2) | Discount percentage (0-100) |
| `category` | VARCHAR(100) | Game category (action, rpg, etc.) |
| `platform` | VARCHAR(100) | Gaming platform |
| `system_requirements` | VARCHAR(100) | System requirements level |
| `release_date` | DATE | Game release date |
| `image_url` | VARCHAR(500) | Game cover image URL |
| `game_setup_file` | VARCHAR(500) | Path to uploaded setup file |
| `stock_quantity` | INT | Available stock (default: 999999) |
| `created_at` | TIMESTAMP | When record was created |

### Game Files Table
The `game_files` table tracks uploaded setup files:

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT | Primary key |
| `game_id` | INT | Links to games table |
| `file_name` | VARCHAR(255) | Original filename |
| `file_path` | VARCHAR(500) | Server file path |
| `file_size` | BIGINT | File size in bytes |
| `file_type` | VARCHAR(50) | MIME type |
| `upload_date` | TIMESTAMP | Upload timestamp |

## 🚀 How It Works

### When Admin Submits Form:
1. **Form Validation**: Required fields are checked
2. **File Upload**: Game setup file is uploaded to `uploads/games/`
3. **Database Insert**: Game data is inserted into `games` table
4. **File Record**: Upload details stored in `game_files` table
5. **Response**: Success/error message returned to admin

### Form Fields Mapping:
- **Game Name** → `title`
- **Category** → `category`
- **Price** → `price`
- **Discount** → `discount_percentage` (calculates `discount_price`)
- **Description** → `description`
- **Game Image URL** → `image_url`
- **Game Setup File** → `game_setup_file` + `game_files` record
- **Platform** → `platform`
- **System Requirements** → `system_requirements`
- **Release Date** → `release_date`

## 🔧 API Endpoints

### POST `/api/add_product.php`
Handles game creation with file uploads.

**Required Fields:**
- `name` (string): Game title
- `category` (string): Game category
- `price` (float): Game price
- `description` (string): Game description
- `platform` (string): Gaming platform
- `game_setup` (file): Setup file upload

**Optional Fields:**
- `discount` (float): Discount percentage
- `image_url` (string): Cover image URL
- `system_requirements` (string): System requirements
- `release_date` (date): Release date

**Response:**
```json
{
  "success": true,
  "message": "Game added successfully",
  "product_id": 123,
  "data": { ... }
}
```

## 📁 File Upload Details

### Supported Formats:
- `.exe` - Windows executables
- `.msi` - Windows installers
- `.zip` - Compressed archives
- `.rar` - WinRAR archives
- `.7z` - 7-Zip archives
- `.iso` - Disc images
- `.bin` - Binary files

### Limits:
- **Max File Size**: 500MB
- **Upload Directory**: `uploads/games/`
- **Security**: Files are renamed with unique IDs
- **Protection**: .htaccess prevents script execution

## 🔐 Admin Access

### Default Admin Credentials:
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: `super_admin`

### Admin Features:
- Add new games with file uploads
- View all uploaded games
- Manage categories and platforms
- Monitor file uploads and storage

## 🛠 Installation Complete

Your system is now ready! To add games:

1. Visit: `http://localhost/video_game_storeG/admin/dashboard.html`
2. Click "Add New Game"
3. Fill out the form
4. Upload setup file
5. Submit to store in database

The form will validate all fields, upload the file securely, and store all data in your MySQL database automatically.

## 🐛 Troubleshooting

### Common Issues:
- **File upload fails**: Check uploads directory permissions
- **Database errors**: Verify MySQL is running and credentials are correct
- **Form submission fails**: Check browser console for JavaScript errors

### Log Files:
- Check Apache error logs for server issues
- PHP errors will be displayed in the admin interface
- File upload progress is shown in real-time

---

**🎉 Your GameHub game store database system is fully operational!**
