FROM php:8.2-apache

# Install mysqli using OS packages (NOT docker-php-ext-install)
RUN apt-get update \
 && apt-get install -y php-mysql \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Apache already listens on port 80
EXPOSE 80

# Copy UI code
COPY src/ /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

