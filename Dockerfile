# Gunakan image PHP resmi dengan FPM
FROM php:8.4-fpm

# Install dependencies sistem yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dibutuhkan Laravel dan fitur spreadsheet
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_pgsql \
    pgsql \
    zip

# Ambil Composer terbaru dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tentukan folder kerja di dalam container
WORKDIR /var/www

# Salin source code project (opsional jika menggunakan volume di docker-compose)
COPY . .

# Berikan izin akses folder storage dan cache agar tidak error di Linux
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
