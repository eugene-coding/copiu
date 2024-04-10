FROM yiisoftware/yii2-php:7.4-apache

# Project source-code
WORKDIR /app

ADD composer.* /app/
# Install packages.
# TODO Мы не добавляли запуск миграций

RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /app
ENV PATH /app/vendor/bin:${PATH}
# Копирование файла с задачами cron в контейнер
COPY cronjobs /etc/cron.d/cronjobs

# Установка разрешений и перезапуск cron
RUN chmod 0644 /etc/cron.d/cronjobs && \
    crontab /etc/cron.d/cronjobs

# Запуск cron в фоновом режиме
CMD ["cron", "-f"]
