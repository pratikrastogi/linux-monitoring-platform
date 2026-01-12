FROM php:8.2-apache-bullseye

RUN docker-php-ext-install mysqli \
 && a2enmod ssl \
 && a2enmod rewrite \
 && a2enmod headers

EXPOSE 80
EXPOSE 443

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
