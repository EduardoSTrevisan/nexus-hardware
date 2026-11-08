FROM php:8.4-cli
WORKDIR /app
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
COPY . /app
RUN mkdir -p /app/storage && chown -R www-data:www-data /app/storage
USER www-data
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/router.php"]
