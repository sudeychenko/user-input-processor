FROM php:8.1-cli-alpine3.19

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug-stable > /dev/null
RUN rm /usr/bin/install-php-extensions

RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
