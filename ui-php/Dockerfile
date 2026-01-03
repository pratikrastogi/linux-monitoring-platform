FROM php:8.2-apache-bullseye

RUN docker-php-ext-install mysqli

EXPOSE 80

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

