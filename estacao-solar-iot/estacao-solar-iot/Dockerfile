# PHP 8.3 + Apache, com a extensão do MySQL e o mod_rewrite (usado pelo .htaccess)
FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html
COPY . /var/www/html
