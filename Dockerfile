FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install PDO MySQL extension & SQLite extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Ensure uploads directory exists and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/uploads

# Expose port 80
EXPOSE 80

# Configure Apache to listen on dynamic $PORT set by Railway
CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf && apache2-foreground
