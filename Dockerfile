# Використовуємо образ PHP
FROM php:8.2.0-apache

# Встановюємо додаткові залежності
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

# Копіюємо код в контейнер
COPY . /var/www/html

# Налаштовуємо Apache
RUN a2enmod rewrite

# Копіюємо файл крону до cron.d
COPY crontab /etc/cron.d/crontab
 
# Видаємо права на виконання крону з файлу
RUN chmod 0644 /etc/cron.d/crontab

# Запускаємо роботу крону
RUN crontab /etc/cron.d/crontab

# Запускаємо крон та апач
CMD cron && apache2-foreground

