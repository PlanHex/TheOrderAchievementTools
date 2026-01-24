FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy production code
COPY ../production /var/www/html

# Copy development data (for demo mode testing)
COPY ../development/data /var/www/html/../development/data

RUN chown -R www-data:www-data /var/www/html

# Configure Apache to serve from public/ directory
RUN { \
    echo "<VirtualHost *:80>"; \
    echo "    ServerAdmin admin@localhost"; \
    echo "    DocumentRoot /var/www/html/public"; \
    echo "    <Directory /var/www/html/public>"; \
    echo "        Options Indexes FollowSymLinks"; \
    echo "        AllowOverride All"; \
    echo "        Require all granted"; \
    echo "        <IfModule mod_rewrite.c>"; \
    echo "            RewriteEngine On"; \
    echo "            RewriteBase /"; \
    echo "            RewriteCond %{REQUEST_FILENAME} !-f"; \
    echo "            RewriteCond %{REQUEST_FILENAME} !-d"; \
    echo "            RewriteRule ^(.*)$ index.php [QSA,L]"; \
    echo "        </IfModule>"; \
    echo "    </Directory>"; \
    echo "    <FilesMatch \\.php$>"; \
    echo "        SetHandler application/x-httpd-php"; \
    echo "    </FilesMatch>"; \
    echo "    ErrorLog \${APACHE_LOG_DIR}/error.log"; \
    echo "    CustomLog \${APACHE_LOG_DIR}/access.log combined"; \
    echo "</VirtualHost>"; \
} > /etc/apache2/sites-available/000-default.conf

# Basic Xdebug defaults (adjust via XDEBUG_CONFIG env in docker-compose)
RUN { \
    echo "xdebug.mode=debug"; \
    echo "xdebug.start_with_request=yes"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
} > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80

# Install Composer (available inside the image)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
