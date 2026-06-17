FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev postgresql-client unzip libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install pdo_pgsql pgsql curl mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . /var/www/html/
RUN composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
