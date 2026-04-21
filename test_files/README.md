# SmartCast - Voting Management System

SmartCast is a comprehensive PHP-based voting management system designed for events, competitions, and contests. It provides a complete solution for organizing voting events with real-time results, contestant management, and secure voting mechanisms.

## Features

### 🎯 Core Features
- **Multi-tenant Architecture** - Support multiple organizations
- **Event Management** - Create and manage voting events
- **Contestant Management** - Add contestants with profiles and categories
- **Real-time Voting** - Secure voting with instant results
- **Vote Bundles** - Flexible pricing with different vote packages
- **Results Dashboard** - Real-time leaderboards and analytics
- **User Management** - Role-based access control
- **Audit Logging** - Complete activity tracking

### 🔐 Security Features
- **Authentication & Authorization** - Secure login system
- **Input Validation** - Comprehensive data validation
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - Output sanitization
- **Rate Limiting** - Prevent abuse
- **Audit Trail** - Complete activity logging

### 📊 Analytics & Reporting
- **Real-time Results** - Live vote counting
- **Revenue Tracking** - Transaction monitoring
- **User Analytics** - Activity insights
- **Export Capabilities** - Data export features

## Requirements

- **PHP 7.4+** with extensions:
  - PDO MySQL
  - JSON
  - GD (for image processing)
  - cURL
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache/Nginx** web server
- **Composer** (optional, for dependencies)

## Installation

### 1. Database Setup

1. Import the provided SQL file:
```sql
mysql -u root -p smartcast < smartcast.sql
```

2. Update database configuration in `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartcast');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 2. File Permissions

Set proper permissions for upload directories:
```bash
chmod 755 public/uploads/
chmod 755 public/uploads/events/
chmod 755 public/uploads/contestants/
```

### 3. Security Configuration

Update security keys in `config/config.php`:
```php
define('JWT_SECRET', 'your-unique-jwt-secret-key');
define('ENCRYPTION_KEY', 'your-unique-encryption-key');
define('PASSWORD_SALT', 'your-unique-password-salt');
```

### 4. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Directory Structure

```
smartcast/
├── config/                 # Configuration files
│   ├── config.php         # Main configuration
│   └── config.local.php   # Local overrides (optional)
├── includes/              # Include files
│   └── autoloader.php     # Class autoloader
├── public/                # Public assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── uploads/          # File uploads
├── src/                   # Application source code
│   ├── Core/             # Core framework classes
│   ├── Controllers/      # Application controllers
│   └── Models/           # Data models
├── views/                 # View templates
│   ├── admin/            # Admin interface
│   ├── auth/             # Authentication pages
│   ├── home/             # Public pages
│   └── layout/           # Layout templates
├── index.php             # Application entry point
└── README.md             # This file
```

## Usage

### Initial Setup

1. **Access the application** at `http://localhost/smartcast`
2. **Register a new organization** using the registration form
3. **Login** with your credentials
4. **Create your first event** from the admin dashboard

### Creating an Event

1. Navigate to **Admin → Events → Create Event**
2. Fill in event details:
   - Event name and code
   - Description
   - Start and end dates
   - Visibility settings
3. **Upload a featured image** (optional)
4. **Save the event**

### Adding Contestants

1. Go to **Admin → Contestants → Create Contestant**
2. Enter contestant information:
   - Name and bio
   - Upload contestant photo
   - Assign to event
3. **Add to categories** as needed

### Managing Categories

1. Access **Admin → Events → [Event Name] → Categories**
2. **Create categories** for organized voting
3. **Assign contestants** to categories

### Vote Bundles

Vote bundles are automatically created with default pricing:
- 1 Vote - GH₵1.00
- 5 Votes - GH₵4.50
- 10 Votes - GH₵8.00
- 25 Votes - GH₵18.00

You can customize these in the event management interface.

## API Endpoints

### Public API

```
GET  /api/events           # List public events
GET  /api/events/{id}      # Get event details
GET  /api/events/{id}/results  # Get event results
POST /api/vote             # Cast a vote
```

### Admin API

```
GET  /admin/api/stats      # Dashboard statistics
POST /admin/api/events     # Create event
PUT  /admin/api/events/{id}    # Update event
DELETE /admin/api/events/{id}  # Delete event
```

## User Roles

### Platform Admin
- Manage all tenants and events
- System-wide administration
- Access to all features

### Owner
- Manage organization events
- User management within organization
- Full access to tenant features

### Manager
- Event and contestant management
- Limited user management
- Reporting access

### Staff
- Basic event management
- Contestant management
- Read-only reporting

## Security Considerations

### Best Practices Implemented

1. **Input Validation** - All user inputs are validated and sanitized
2. **SQL Injection Prevention** - Using prepared statements
3. **XSS Protection** - Output escaping with `htmlspecialchars()`
4. **CSRF Protection** - Token-based form protection
5. **Authentication** - Secure password hashing with `password_hash()`
6. **Authorization** - Role-based access control
7. **Audit Logging** - Complete activity tracking

### Recommended Security Measures

1. **Use HTTPS** in production
2. **Regular backups** of database and files
3. **Keep PHP updated** to latest stable version
4. **Monitor logs** for suspicious activity
5. **Implement rate limiting** for API endpoints
6. **Use strong passwords** for database and admin accounts

## Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Database connection failed
```
**Solution:** Check database credentials in `config/config.php`

#### File Upload Issues
```
Error: Failed to upload file
```
**Solution:** Check directory permissions for `public/uploads/`

#### Missing Classes
```
Error: Class not found
```
**Solution:** Ensure autoloader is working and file paths are correct

#### Session Issues
```
Error: Session not working
```
**Solution:** Check PHP session configuration and directory permissions

## Development

### Adding New Features

1. **Create Model** in `src/Models/`
2. **Create Controller** in `src/Controllers/`
3. **Add Routes** in `src/Core/Application.php`
4. **Create Views** in `views/`
5. **Update Database** schema if needed

### Database Migrations

When making database changes:
1. Create migration SQL file
2. Update `smartcast.sql` with new schema
3. Document changes in changelog

## Contributing

1. Fork the repository
2. Create feature branch
3. Make changes with proper documentation
4. Test thoroughly
5. Submit pull request

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Support

For support and questions:
- Create an issue on GitHub
- Check documentation
- Review troubleshooting guide

## Changelog

### Version 1.0.0
- Initial release
- Core voting functionality
- Multi-tenant support
- Admin dashboard
- Real-time results
- Security features
