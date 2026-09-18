FROM php:8.2-apache
# Build version: 2026-09-18-v3

# Install required PHP extensions for MySQL (mysqli & pdo_mysql) and enable mod_rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql && a2enmod rewrite

# Copy project files to Apache root
COPY . /var/www/html/

# Set working directory & file permissions
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Expose web HTTP port 80
EXPOSE 80

CMD ["apache2-foreground"]
