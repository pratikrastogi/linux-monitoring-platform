FROM php:8.2-apache

# Enable mysqli
RUN docker-php-ext-install mysqli

# Apache listens on 80 by default
EXPOSE 80

# Copy UI code
COPY src/ /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html
