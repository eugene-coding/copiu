FROM yiisoftware/yii2-php:7.4-apache

# Project source-code
WORKDIR /app
ENV PATH /app/vendor/bin:${PATH}
# Define an argument for migration flag
ARG RUN_MIGRATIONS=false
ENV RUN_MIGRATIONS=${RUN_MIGRATIONS}

# Copy  files
ADD ./ /app
ADD cronjob /etc/cron.d/cronjob
# Install cron
RUN apt-get update && apt-get install -y cron \
    && /usr/local/bin/composer install --prefer-dist && \
    chmod 0644 /etc/cron.d/cronjob && \
    crontab /etc/cron.d/cronjob
# Add cron job file

# Give execution rights on the cron job


RUN chown -R www-data:www-data ./ && \
    chmod -R 777 web/assets && \
    chmod -R 777 runtime && \
    chmod -R 777 web/uploads

# Run migrations only if RUN_MIGRATIONS is set to true
RUN if [ "$RUN_MIGRATIONS" = "true" ]; then \
    php yii migrate --interactive=0 ; \
    fi

# Start cron service
# CMD ["cron", "-f"]