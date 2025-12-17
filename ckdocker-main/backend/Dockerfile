# File: backend/Dockerfile

FROM php:8.2-fpm

WORKDIR /var/www/html

# Cài đặt các extensions cần thiết cho Laravel (pdo, pdo_mysql)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql opcache \
    && rm -rf /var/lib/apt/lists/*

# BỎ QUA cài đặt Composer vì chưa có mã nguồn

EXPOSE 9000

CMD ["php-fpm"]