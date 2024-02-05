# Використовуємо образ PHP
FROM php:8.0-apache

# Встановюємо додаткові залежності
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

# Копіюємо код в контейнер
COPY . /var/www/html

# Налаштовуємо Apache
RUN a2enmod rewrite

# Запускаємо Apache
CMD ["apache2-foreground"]
