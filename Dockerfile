# Gunakan image PHP resmi dengan FPM
FROM php:8.2-fpm

# Install dependencies sistem yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP untuk PostgreSQL dan GD (untuk gambar)
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd

# Ambil Composer terbaru dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tentukan folder kerja di dalam container
WORKDIR /var/www

# Salin source code project (opsional jika menggunakan volume di docker-compose)
COPY . .

# Berikan izin akses folder storage dan cache agar tidak error di Linux
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
