FROM php:8.4-apache

# Install dependencies for PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (jika pakai routing)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy source code
COPY . .

# Set permissions (opsional tapi baik untuk keamanan)
RUN chown -R www-data:www-data /var/www/html

# Expose port (Railway biasanya otomatis, tapi ini baik untuk standar)
EXPOSE 80

# 👉 BARIS PENTING UNTUK MENJALANKAN APACHE
CMD ["apache2-foreground"]
