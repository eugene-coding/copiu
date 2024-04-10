FROM yiisoftware/yii2-php:7.4-apache

# Project source-code
WORKDIR /app

ADD composer.* /app/
# Install packages.
# TODO Мы не добавляли запуск миграций

RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /app
ENV PATH /app/vendor/bin:${PATH}
