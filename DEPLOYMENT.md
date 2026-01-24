# Deployment Guide

This guide covers deploying the Achievement Tools application to a production server.

## Overview

The **production-ready code** is located in the `production/` directory. This contains everything needed to run the application on a server.

## Pre-Deployment Checklist

- [ ] Have MySQL 8.0+ installed and accessible
- [ ] Have PHP 8.1+ installed
- [ ] Have a web server (Apache with mod_rewrite or Nginx)
- [ ] Have SSH/SFTP access to your server

## Deployment Steps

### 1. Copy Production Files

Copy the entire `production/` directory to your server. For example:

```bash
scp -r production/ user@example.com:/var/www/achievements
```

Or via SFTP, upload the `production/` folder contents to your web root.

### 2. Configure the Application

Update the configuration files on the server:

**`config/app.php`**
- Set `'mode' => 'production'`
- Configure authentication if needed:
  ```php
  'auth' => [
      'user' => 'your-username',
      'pass' => 'your-secure-password'
  ]
  ```

**`config/database.php`**
- Update MySQL host, username, password, and database name to match your server setup

### 3. Initialize the Database

The SQL schema is provided in the development repository. You have two options:

**Option A: Download schema from development folder**
- Get `development/data/sql/sql_tables.sql` from this repository
- Upload to your server and run:
  ```bash
  mysql -u root -p < sql_tables.sql
  ```

**Option B: Run SQL directly on server**
```bash
mysql -h your-host -u your-user -p your-database < sql_tables.sql
```

### 4. Configure Web Server

#### Apache Configuration

Ensure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
```

Create a VirtualHost pointing to the `public/` directory:
```apache
<VirtualHost *:80>
    ServerName example.com
    ServerAdmin admin@example.com
    DocumentRoot /var/www/achievements/public

    <Directory /var/www/achievements/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/achievements_error.log
    CustomLog ${APACHE_LOG_DIR}/achievements_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name example.com;

    root /var/www/achievements/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    error_log /var/log/nginx/achievements_error.log;
    access_log /var/log/nginx/achievements_access.log;
}
```

### 5. Set File Permissions

```bash
# Ensure web server can read files
chown -R www-data:www-data /var/www/achievements
chmod -R 755 /var/www/achievements
```

### 6. Enable HTTPS (Recommended)

Use Let's Encrypt for free SSL certificates:

```bash
sudo apt-get install certbot python3-certbot-apache
sudo certbot --apache -d example.com
```

### 7. Test the Installation

1. Visit your domain in a browser
2. If in production mode, you'll be prompted for authentication (if configured)
3. Test CRUD operations (Create, Read, Update, Delete) for achievements

### 8. Monitor Logs

Keep an eye on error logs:

```bash
# Apache
tail -f /var/log/apache2/achievements_error.log

# Nginx
tail -f /var/log/nginx/achievements_error.log
```

---

## Post-Deployment

### Backup Strategy
- Regularly backup your MySQL database:
  ```bash
  mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
  ```

### Updating the Application
1. Back up the current `production/` directory
2. Download and copy the updated `production/` folder
3. Restart the web server if needed:
   ```bash
   sudo systemctl restart apache2  # Apache
   # or
   sudo systemctl restart nginx    # Nginx
   ```

---

## Troubleshooting

### 404 Errors (Routes Not Working)
- Verify `mod_rewrite` is enabled (Apache)
- Check `.htaccess` permissions
- Verify `DocumentRoot` points to `production/public/`

### Database Connection Errors
- Verify credentials in `config/database.php`
- Test MySQL connectivity: `mysql -h host -u user -p database`
- Check firewall rules

### Permission Errors
- Ensure web server user owns the files
- Check PHP error logs for more details

### Authentication Not Working
- Verify `config/app.php` mode is set to `'production'`
- Check username and password in `config/app.php`

---

## Support

For detailed architecture information, see `docs/architecture.md` in the development repository.

For configuration details, see `docs/requirements.md`.
