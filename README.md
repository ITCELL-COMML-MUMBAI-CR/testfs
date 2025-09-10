# SAMPARK - Support and Mediation Portal for All Rail Cargo

SAMPARK is a comprehensive freight customer support portal designed for Indian Railways, enabling freight customers to effectively communicate their needs and bottlenecks while providing administration with insights into root causes of customer concerns.

## 🚀 Features

### 🔐 Multi-Role Authentication System
- **Customer Portal**: Freight customers can register, create tickets, and track their issues
- **Controller Interface**: Department controllers can manage tickets within their division
- **Controller Nodal**: Commercial department controllers with cross-division authority
- **Admin Interface**: Administrative users with content and user management capabilities
- **Super Admin**: System administrators with complete access

### 🎫 Advanced Ticket Management
- **Smart Routing**: Tickets automatically assigned to relevant controller_nodal based on location
- **Priority Escalation**: Automatic priority escalation (Normal → Medium → High → Critical)
- **Status Tracking**: Complete lifecycle tracking from creation to closure
- **Communication History**: Full audit trail of all interactions
- **Evidence Upload**: Secure file upload with compression and validation
- **Feedback System**: Customer satisfaction rating and feedback collection

### 🎨 Apple-Inspired Design
- **Modern UI/UX**: Clean, minimalist interface following Apple's design principles
- **Glassmorphism Effects**: Modern glass-like effects on navigation and cards
- **Mobile-First**: Fully responsive design optimized for all devices
- **Accessibility**: WCAG 2.1 AA compliant with proper focus states
- **Dark/Light Mode**: User preference themes (planned)

### 🔧 Technical Features
- **MVC Architecture**: Clean separation of concerns with custom PHP framework
- **REST API**: Comprehensive API endpoints for all operations
- **File Management**: Secure upload, compression, and storage of evidence files
- **Real-time Updates**: Live ticket status updates and notifications
- **Security**: Role-based access control, CSRF protection, SQL injection prevention
- **Performance**: Optimized queries, caching, and lazy loading

## 📋 System Requirements

- **Web Server**: Apache 2.4+ with mod_rewrite enabled
- **PHP**: 7.4+ or 8.0+ with extensions:
  - mysqli
  - gd (for image processing)
  - fileinfo (for file validation)
  - curl (for external API calls)
  - openssl (for encryption)
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Storage**: Minimum 500MB for application files and uploads

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-repo/sampark.git
cd sampark
```

### 2. Web Server Configuration

#### Apache (.htaccess already configured)
- Ensure `mod_rewrite` is enabled
- Document root should point to the `public/` directory
- Update the `RewriteBase` in `public/.htaccess` if needed

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/sampark/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. Database Setup

#### Create Database
```sql
CREATE DATABASE sampark_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sampark_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON sampark_db.* TO 'sampark_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Schema
```bash
mysql -u sampark_user -p sampark_db < src/config/database_schema.sql
```

#### Load Sample Data (Optional)
```bash
mysql -u sampark_user -p sampark_db < src/config/sample_data.sql
```

### 4. Configuration

#### Update Database Configuration
Edit `src/config/database.php`:
```php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'sampark_db',
    'username' => 'sampark_user',
    'password' => 'your_secure_password',
    'charset' => 'utf8mb4'
];
```

#### Update Application Configuration
Edit `src/config/Config.php`:
```php
return [
    'base_url' => 'https://your-domain.com',
    'app_name' => 'SAMPARK',
    'timezone' => 'Asia/Kolkata',
    // ... other settings
];
```

### 5. File Permissions
```bash
chmod -R 755 public/
chmod -R 777 public/uploads/
chmod -R 777 logs/
```

### 6. Security Setup
- Change default passwords in sample data
- Update CSRF tokens and session security settings
- Configure SSL certificate for HTTPS
- Set up firewall rules and security headers

## 🎯 Usage Guide

### For Customers

#### 1. Registration
- Visit the portal and click "Sign Up"
- Fill in personal and company details
- Wait for admin approval (status will be updated via email)

#### 2. Creating Support Tickets
- Log in to your account
- Click "Create New Ticket"
- Fill in all required details:
  - Issue category, type, and subtype
  - Location (shed/terminal) details
  - Wagon information
  - Detailed description
  - Upload evidence files if needed
- Submit and receive ticket ID

#### 3. Tracking Tickets
- View all tickets in "My Tickets" section
- Click on any ticket to see detailed view
- Monitor status changes and priority escalations
- Respond when additional information is requested
- Provide feedback when ticket is resolved

### For Railway Staff

#### 1. Controllers
- Log in with your staff credentials
- View assigned tickets in your division
- Add replies and take actions
- Forward tickets to nodal controllers when needed
- Add internal remarks (not visible to customers)

#### 2. Controller Nodal (Commercial)
- Access all tickets in your zone/division
- Forward tickets across divisions
- Approve/reject replies from controllers
- Revert tickets to customers for additional info
- Final authority on ticket resolution

#### 3. Admins
- Manage user registrations and approvals
- Configure system settings and content
- Manage categories and shed information
- View reports and analytics
- Send bulk communications

## 🔧 API Documentation

### Authentication
```javascript
// Login
POST /api/auth/login
{
    "login_type": "customer|staff",
    "identifier": "email_or_phone_or_login_id",
    "password": "password"
}

// Get current user info
GET /api/auth/user
Authorization: Bearer {token}
```

### Tickets
```javascript
// Create ticket
POST /api/tickets
Content-Type: multipart/form-data
{
    "category_id": "1",
    "shed_id": "5",
    "description": "Issue description...",
    "evidence[]": [file1, file2]
}

// Get tickets
GET /api/tickets?status=pending&priority=high

// Get ticket details
GET /api/tickets/{ticket_id}

// Add reply
POST /api/tickets/{ticket_id}/reply
{
    "message": "Reply content...",
    "internal": false
}
```

### File Upload
```javascript
// Upload evidence
POST /api/tickets/{ticket_id}/evidence
Content-Type: multipart/form-data
{
    "files[]": [file1, file2, file3]
}

// Get file
GET /api/tickets/{ticket_id}/evidence/{filename}
```

## 🏗️ Architecture

### Directory Structure
```
sampark/
├── public/                 # Web-accessible files
│   ├── index.php          # Application entry point
│   ├── .htaccess          # Apache rewrite rules
│   ├── assets/            # CSS, JS, images, fonts
│   ├── uploads/           # User uploaded files
│   └── api/               # API endpoints
├── src/                   # Application source code
│   ├── config/            # Configuration files
│   ├── controllers/       # MVC Controllers
│   ├── models/            # Data models
│   ├── views/             # View templates
│   └── utils/             # Utility classes
├── logs/                  # Application logs
├── designs.md             # Design specifications
└── README.md              # This file
```

### Core Components

#### 1. Router (`src/utils/Router.php`)
- Custom URL routing system
- Middleware support
- RESTful route definitions

#### 2. Base Controller (`src/controllers/BaseController.php`)
- Common controller functionality
- View rendering and data passing
- JSON response methods

#### 3. Session Management (`src/utils/Session.php`)
- Secure session handling
- Flash message support
- CSRF token generation

#### 4. File Upload (`src/utils/FileUploader.php`)
- Secure file validation
- Image compression
- Malware scanning

#### 5. Notifications (`src/utils/NotificationService.php`)
- Email and SMS notifications
- Template processing
- Bulk messaging support

## 🔒 Security Features

### Authentication & Authorization
- **Multi-factor Authentication**: Email verification for new registrations
- **Role-Based Access Control**: Granular permissions for different user types
- **Session Security**: Secure session management with timeout and regeneration
- **Password Security**: Strong password requirements with hashing

### Data Protection
- **SQL Injection Prevention**: Prepared statements and input validation
- **XSS Protection**: Output escaping and Content Security Policy
- **CSRF Protection**: Token-based CSRF prevention
- **File Upload Security**: Type validation, size limits, and malware scanning

### Infrastructure Security
- **HTTPS Enforcement**: SSL/TLS encryption for all communications
- **Input Validation**: Comprehensive server-side validation
- **Error Handling**: Secure error messages without information disclosure
- **Audit Logging**: Complete activity logs for security monitoring

## 🚀 Performance Optimization

### Frontend Optimization
- **Lazy Loading**: Images and content loaded on demand
- **Code Splitting**: JavaScript bundled by feature
- **Caching**: Browser caching for static assets
- **Compression**: Gzip compression for text files

### Backend Optimization
- **Database Indexing**: Optimized queries with proper indexes
- **Connection Pooling**: Efficient database connection management
- **Query Optimization**: Minimized N+1 queries and unnecessary data fetching
- **Caching**: Server-side caching for frequently accessed data

### Mobile Performance
- **Progressive Web App**: PWA features for mobile experience
- **Touch Optimization**: 44px minimum touch targets
- **Image Optimization**: Responsive images with multiple sizes
- **Network Optimization**: Optimized API calls and reduced payload

## 🐛 Troubleshooting

### Common Issues

#### 1. 404 Errors
- Check if `mod_rewrite` is enabled
- Verify `.htaccess` file exists in `public/` directory
- Ensure document root points to `public/` folder

#### 2. Database Connection Issues
- Verify database credentials in `src/config/database.php`
- Check if MySQL service is running
- Ensure database and user exist with proper permissions

#### 3. File Upload Issues
- Check `public/uploads/` directory permissions (should be 777)
- Verify PHP upload settings (`upload_max_filesize`, `post_max_size`)
- Ensure adequate disk space

#### 4. Permission Errors
- Set proper file permissions: `chmod -R 755 public/`
- Ensure web server can write to logs and uploads directories
- Check SELinux settings if applicable

### Debug Mode
Enable debug mode in `src/config/Config.php`:
```php
'debug' => true,
'log_level' => 'debug'
```

### Log Files
Check log files for detailed error information:
- `logs/app.log` - Application logs
- `logs/error.log` - PHP errors
- `logs/security.log` - Security events

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes following the coding standards
4. Test thoroughly with sample data
5. Commit: `git commit -m 'Add amazing feature'`
6. Push: `git push origin feature/amazing-feature`
7. Create a Pull Request

### Coding Standards
- Follow PSR-4 autoloading standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write PHPDoc for all public methods
- Follow the existing code style and indentation

### Testing
- Test all user roles and permissions
- Verify mobile responsiveness
- Check accessibility compliance
- Test file upload functionality
- Validate API endpoints

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Indian Railways** for the domain expertise and requirements
- **Apple Inc.** for the design inspiration and UI principles
- **Bootstrap Team** for the excellent CSS framework
- **Font Awesome** for the comprehensive icon library
- **SweetAlert2** for beautiful alert dialogs

## 📞 Support

For technical support or questions:

- **Email**: support@sampark.railway.gov.in
- **Phone**: 1800-XXX-XXXX
- **Documentation**: [Wiki](https://github.com/your-repo/sampark/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-repo/sampark/issues)

## 🗺️ Roadmap

### Upcoming Features
- [ ] Real-time chat support
- [ ] Mobile app development
- [ ] Advanced analytics dashboard
- [ ] Integration with external systems
- [ ] Multi-language support
- [ ] Voice/video call support
- [ ] AI-powered issue classification
- [ ] Blockchain-based audit trail

### Planned Improvements
- [ ] Performance optimization
- [ ] Enhanced security features
- [ ] Better accessibility support
- [ ] Advanced reporting capabilities
- [ ] Integration APIs for third parties

---

**Made with ❤️ for Indian Railways freight customers**
