# RedAlgae Framework

A lightweight, flexible PHP framework for building modern web applications with simplicity and performance.

[![GitHub](https://img.shields.io/badge/GitHub-mibnurizky%2Fredalgae--framework-blue)](https://github.com/mibnurizky/redalgae-framework)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4.svg)](https://www.php.net/)

## Features

- ⚡ **Lightweight & Fast** - Minimal overhead, maximum performance
- 🛣️ **Flexible Routing** - Simple and intuitive URL routing system
- 🧩 **Component-Based** - Organize code with reusable components
- ✅ **Built-in Validation** - Powerful form validation with Rakit
- 📧 **Email Support** - Send emails easily with PHPMailer
- 🔒 **CSRF Protection** - Built-in CSRF token handling
- 🌍 **Multi-language Support** - Built-in internationalization
- 💾 **Cache System** - Query and file-based caching
- 🗄️ **Database Abstraction** - Support for multiple database types

## Requirements

- **PHP 8.2** or higher
- MySQL/MariaDB or any PDO-supported database
- Apache with `mod_rewrite` enabled (or equivalent on other servers)

## Installation

### Using Composer (Recommended)

```bash
composer create-project redalgae/framework my-project
cd my-project
```

This automatically:
- Installs all dependencies
- Creates necessary directories
- Generates default configuration files
- Sets up `.htaccess` for URL rewriting
- Copies the complete framework structure

### Manual Setup

```bash
git clone https://github.com/mibnurizky/redalgae-framework.git my-project
cd my-project
composer install
php install.php
```

## Quick Start

1. **Configure Database**
   ```bash
   nano config/database.php
   ```

2. **Configure Application**
   ```bash
   nano config/app.php
   ```

3. **Run Development Server**
   ```bash
   php -S localhost:8000
   ```

4. **Visit** `http://localhost:8000` in your browser

## Project Structure

```
my-project/
├── index.php              # Application entry point
├── core/                  # Framework core files
│   ├── app.php           # Main application class
│   ├── autoload.php      # PSR-4 autoloader
│   ├── database.php      # Database handler
│   ├── model.php         # Base model class
│   ├── cache.php         # Caching system
│   └── ...
├── config/               # Configuration files (NOT in version control)
│   ├── app.php           # Application settings
│   └── database.php      # Database configuration
├── components/           # Reusable page components
│   ├── auth/            # Authentication components
│   ├── web/             # Web components
│   └── error/           # Error handlers
├── models/              # Data models
├── views/               # View templates
├── helpers/             # Helper functions
├── middleware/          # Custom middleware
├── writepath/           # Writable directory
│   ├── cache/          # Cache files
│   └── logs/           # Log files
├── .htaccess           # Apache rewrite rules
├── composer.json       # Composer configuration
└── install.php         # Setup script
```

## Usage Examples

### Creating a Component

Create `components/home.php`:

```php
<?php
class Home {
    public function index() {
        $data = [
            'title' => 'Welcome to RedAlgae Framework',
            'message' => 'Hello, World!'
        ];
        return view('home', $data);
    }
}
?>
```

Create `views/home.php`:

```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
</head>
<body>
    <h1><?= $message ?></h1>
</body>
</html>
```

Access via: `http://localhost:8000/home`

### Working with Database

Create a model in `models/User.php`:

```php
<?php
namespace RedAlgae\Models;

use RedAlgae\Model;

class User extends Model {
    protected $table = 'users';

    public function getAll() {
        return $this->db->get('users')->fetchAll();
    }
}
?>
```

### Using Built-in Helpers

Helpers are auto-loaded:

```php
<?php
// CSRF Protection
csrf_token();
verify_csrf_token($_POST['csrf_token']);

// General helpers
get_config('app_name');
is_https();
dd($variable); // Debug dump

// View helpers
view('template', $data);
?>
```

### Form Validation

```php
<?php
use Rakit\Validation\Validator;

$validator = new Validator;

$validation = $validator->make($_POST, [
    'name'  => 'required|min:3',
    'email' => 'required|email',
    'password' => 'required|min:6'
]);

if ($validation->fails()) {
    $errors = $validation->errors();
} else {
    // Process valid data
}
?>
```

## Configuration

### Database Configuration (`config/database.php`)

```php
<?php
$connection = array(
    'default' => array(
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => '3306',
        'dbname'   => 'your_database',
        'username' => 'your_username',
        'password' => 'your_password'
    )
);
?>
```

### Application Configuration (`config/app.php`)

Edit settings for your application including:
- Application name
- Timezone
- Debug mode
- Language settings
- And more...

⚠️ **Important**: Configuration files contain sensitive data and are NOT tracked in version control. Each environment (development, production) should have its own config.

## Security Considerations

1. **Never commit config files** - They're ignored by `.gitignore` and contain:
   - Database credentials
   - CSRF keys
   - Secret keys

2. **Generate secure keys** for production:
   ```bash
   # Generate CSRF key (32 characters)
   php -r "echo bin2hex(random_bytes(16));"
   
   # Generate encryption key
   php -r "echo bin2hex(random_bytes(32));"
   ```

3. **Disable debug in production**:
   ```php
   // config/app.php
   'debug' => false,
   'display_errors' => false,
   ```

4. **Set proper file permissions**:
   ```bash
   chmod 755 writepath/
   chmod 755 writepath/cache/
   chmod 755 writepath/logs/
   ```

5. **Use HTTPS** in production

6. **Keep dependencies updated**:
   ```bash
   composer update
   ```

## Troubleshooting

### 404 Error on All Pages

- Ensure `.htaccess` exists in project root
- Enable Apache `mod_rewrite`
- Or use URL format: `index.php?page=component-name`

### Database Connection Error

Check `config/database.php`:
- Verify hostname, port, username, password
- Ensure database exists
- Check user permissions

### Configuration Files Missing

Run the installer:
```bash
php install.php
```

## Testing

Run tests with PHPUnit:

```bash
composer install --dev
./vendor/bin/phpunit
```

PHPUnit is included as a dev dependency for testing your code.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- 🐛 Report bugs: [GitHub Issues](https://github.com/mibnurizky/redalgae-framework/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/mibnurizky/redalgae-framework/discussions)
- 📖 Documentation: Check the [Wiki](https://github.com/mibnurizky/redalgae-framework/wiki)

## Author

**Mohamad Ibnu Rizky**
- GitHub: [@mibnurizky](https://github.com/mibnurizky)
- Email: mohamadibnu.r@gmail.com

---

**Happy coding with RedAlgae Framework!** 🦠
