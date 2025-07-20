FROM dunglas/frankenphp

# add additional extensions here:
RUN install-php-extensions \
	pcntl pdo_pgsql pgsql

ENV SERVER_NAME=:80

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . /app