# Rotiseri Bakery Management System (BSMS)

A comprehensive web-based bakery management system built with PHP, MySQL, and Bootstrap. This system provides role-based access control for different user types including administrators, sales managers, bakery staff, and customers.

## 🚀 Features

### User Management
- **Administrator**: Complete system control, user management, and system oversight
- **Sales Manager**: Inventory management, sales analytics, promotions, and raw materials tracking
- **Bakery Staff**: Order management, customer service, and feedback handling
- **Customer**: Order placement, profile management, and order tracking

### Core Functionality
- **Authentication System**: Secure login/registration with role-based access
- **Order Management**: Complete order lifecycle from placement to completion
- **Inventory Management**: Real-time inventory tracking and management
- **Sales Analytics**: Comprehensive sales reporting and analytics
- **Promotion Management**: Create and manage promotional offers
- **Customer Feedback**: Feedback collection and management system
- **Receipt Generation**: Print-ready receipts for orders

### Security Features
- Password hashing with salt
- Session management and CSRF protection
- Input sanitization and validation
- Secure session handling with timeouts
- SQL injection prevention

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server (XAMPP recommended)
- Modern web browser

## 🛠️ Installation

### 1. Clone or Download
```bash
# If using Git
git clone [repository-url] Bakery-Sales-Management-System

# Or download and extract the files to your web server directory
```

### 2. Database Setup
1. Start your MySQL server (via XAMPP Control Panel if using XAMPP)
2. Create a new database named `sales_db`
3. Import the database structure:
   ```sql
   # Navigate to phpMyAdmin or MySQL command line
   # Import the file: config/sales_db.sql
   ```

### 3. Configuration
1. Update database connection settings in `config/db_connection.php`:
   ```php
   $host = 'localhost';
   $username = 'root';        // Your MySQL username
   $password = '';            // Your MySQL password
   $database = 'sales_db';    // Database name
   ```

### 4. Web Server Setup
1. Place the project folder in your web server directory:
   - XAMPP: `C:\xampp\htdocs\Bakery-Sales-Management-System`
   - WAMP: `C:\wamp\www\Bakery-Sales-Management-System`
   - Linux: `/var/www/html/Bakery-Sales-Management-System`

2. Start your web server (Apache)

### 5. Access the Application
Open your web browser and navigate to:
```
http://localhost/Bakery-Sales-Management-System
```

## 👥 User Types & Access

### Administrator (Type: 0)
- **Employee ID Format**: AD-0001, AD-0002, etc.
- **Capabilities**:
  - Complete user management (create, edit, delete users)
  - System oversight and administration
  - Access to all system areas

### Sales Manager (Type: 1)
- **Employee ID Format**: SV-0001, SV-0002, etc.
- **Capabilities**:
  - Dashboard with sales analytics
  - Inventory management
  - Raw materials tracking
  - Sales reporting and analytics
  - Promotion management
  - Export functionality

### Bakery Staff (Type: 2)
- **Employee ID Format**: BS-0001, BS-0002, etc.
- **Capabilities**:
  - Dashboard overview
  - Create and manage orders
  - Process customer orders
  - Handle customer feedback
  - Print receipts
  - Order history management

### Customer (Type: 3)
- **No Employee ID**: Regular customer accounts
- **Capabilities**:
  - Place orders
  - Track order status
  - View order history
  - Manage profile
  - Submit feedback
  - Print receipts

## 📁 Project Structure

```
Bakery-Sales-Management-System/
├── index.php                 # Main landing page
├── README.md                 # This file
├── administrator/            # Administrator modules
│   ├── a_index.php          # Admin dashboard
│   └── a_user_management.php # User management
├── assets/                   # Static assets
│   ├── css/                 # Stylesheets
│   ├── img/                 # Images
│   └── js/                  # JavaScript files
├── auth/                    # Authentication modules
│   ├── login.php           # Login page
│   ├── logout.php          # Logout handler
│   ├── register_customer.php # Customer registration
│   └── register_staff.php  # Staff registration
├── bakery_staff/           # Bakery staff modules
│   ├── bs_index.php        # Staff dashboard
│   ├── bs_new_order.php    # Create new orders
│   ├── bs_orders.php       # Manage orders
│   └── [other staff files]
├── config/                 # Configuration files
│   ├── db_connection.php   # Database connection
│   ├── sales_db.sql        # Database structure
│   └── feedback_table.sql  # Feedback table structure
├── customer/               # Customer modules
│   ├── c_index.php         # Customer dashboard
│   ├── c_place_order.php   # Place orders
│   └── [other customer files]
├── includes/               # Shared includes
│   ├── header.php          # Common header
│   ├── footer.php          # Common footer
│   ├── sidebar.php         # Navigation sidebar
│   ├── security.php        # Security functions
│   └── functions/          # Function libraries
└── sales_manager/          # Sales manager modules
    ├── sm_index.php        # Manager dashboard
    ├── sm_inventory.php    # Inventory management
    ├── sm_analytics.php    # Sales analytics
    └── [other manager files]
```

## 🔧 Configuration Options

### Session Configuration
Located in `includes/security.php`:
- Session timeout: 1800 seconds (30 minutes)
- Session regeneration interval: 1800 seconds
- HTTP-only cookies enabled
- Secure cookies enabled

### Database Configuration
Located in `config/db_connection.php`:
- Connection charset: utf8mb4
- Error reporting enabled in development

## 📊 Database Tables

Key database tables include:
- `guest` - User accounts and authentication
- `orders` - Order management
- `daily_sales` - Sales tracking
- `inventory` - Product inventory
- `promotions` - Promotional offers
- `feedback` - Customer feedback

## 🔒 Security Features

1. **Password Security**:
   - Passwords are hashed using PHP's `password_hash()` with salt
   - Minimum 8 characters with complexity requirements

2. **Session Security**:
   - Secure session handling with automatic timeouts
   - Session regeneration to prevent fixation attacks
   - HTTP-only cookies

3. **Input Validation**:
   - All user inputs are sanitized and validated
   - CSRF token protection on forms
   - SQL injection prevention using prepared statements

4. **Access Control**:
   - Role-based access control (RBAC)
   - Page-level security checks
   - Automatic redirects for unauthorized access

## 🚀 Getting Started

### First Time Setup
1. After installation, access the system at `http://localhost/Bakery-Sales-Management-System`
2. Register your first administrator account via `auth/register_staff.php`
3. Log in with administrator credentials
4. Use the admin panel to create additional users

### Creating Users
- **Staff/Manager Registration**: Use `auth/register_staff.php`
- **Customer Registration**: Use `auth/register_customer.php`
- **Admin User Management**: Use administrator panel for bulk user creation

## 🔧 Maintenance

### Regular Tasks
1. **Database Backup**: Regularly backup the `sales_db` database
2. **Log Monitoring**: Check server logs for any errors
3. **Security Updates**: Keep PHP and MySQL updated
4. **Performance Monitoring**: Monitor system performance and optimize as needed

### Troubleshooting
1. **Database Connection Issues**: Check `config/db_connection.php` settings
2. **Login Problems**: Verify user credentials and database connectivity
3. **Permission Errors**: Ensure proper file permissions on the web directory
4. **Session Issues**: Clear browser cookies and check PHP session configuration

## 📝 Development Notes

### Code Standards
- PHP PSR-12 coding standards
- Consistent naming conventions
- Comprehensive error handling
- Security-first development approach

### Adding New Features
1. Follow the existing project structure
2. Implement proper security checks
3. Use prepared statements for database operations
4. Include proper input validation and sanitization

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the database logs for error messages
3. Ensure all prerequisites are met
4. Verify database connectivity and permissions


## 📄 License

This project is proprietary and intended for demonstration or internal use only. Copying, redistribution, or commercial use is strictly prohibited without explicit written permission from the author. Please ensure compliance with local regulations regarding bakery management systems.

---

**Note**: This system is designed to be deployed in a local environment (XAMPP), but it can also be used on a standard web hosting server that supports PHP and MySQL. For production deployment, additional security hardening and configuration may be required.
