FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Ensure uploads directory exists and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/uploads && \
    chmod +x /var/www/html/start.sh

# Expose port
EXPOSE 80

CMD ["/var/www/html/start.sh"]
