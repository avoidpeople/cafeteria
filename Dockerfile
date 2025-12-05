FROM php:8.2-cli
WORKDIR /app
COPY . .
RUN apt-get update && apt-get install -y unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader || true
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
