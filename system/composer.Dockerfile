FROM php:8.3-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions bcmath xdebug

COPY --from=composer/composer:2-bin /composer /bin/composer

ENTRYPOINT ["composer"]

CMD ["install"]
