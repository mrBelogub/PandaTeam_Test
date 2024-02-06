# Використовуємо образ PHP
FROM php:8.2.0-apache

# Встановлюємо додаткові залежності
RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libzip-dev \
        unzip \
        sendmail \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip

# Встановлюємо та активуємо pcov
RUN pecl install pcov; docker-php-ext-enable pcov;

# Копіюємо код в контейнер
COPY . /var/www/html

# Налаштовуємо Apache
RUN a2enmod rewrite

# Виконуємо composer install
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN cd /var/www/html && composer install

# Запускаємо Apache
CMD ["apache2-foreground"]
