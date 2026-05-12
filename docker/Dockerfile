FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system packages and Node.js
RUN apt-get update && apt-get install -y \
    bash \
    git \
    unzip \
    curl \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    zlib1g-dev \
    libxml2-dev \
    libonig-dev \
    libzip-dev \
    libexif-dev \
    imagemagick \
    libmagickwand-dev \
    gcc \
    g++ \
    make \
    default-mysql-client \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        opcache \
        bcmath \
        mbstring \
        zip \
        exif \
        pcntl \
        intl \
    && docker-php-ext-enable exif intl

# Imagick
RUN pecl install imagick && docker-php-ext-enable imagick

# Enable Apache Rewrite
RUN a2enmod rewrite

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache config
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Startup script
COPY ./docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

ENTRYPOINT ["startup.sh"]
