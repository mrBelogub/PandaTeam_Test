# Використовуємо образ PHP
FROM php:8.2.0-apache

# Встановлюємо додаткові залежності
RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libzip-dev \
        unzip \
        cron \
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

# Копіюємо файл крону до cron.d
COPY crontab /etc/cron.d/crontab
 
# Видаємо права на виконання крону з файлу
RUN chmod 0644 /etc/cron.d/crontab

# Запускаємо роботу крону
RUN crontab /etc/cron.d/crontab

# Запускаємо крон та апач
CMD cron && apache2-foreground

