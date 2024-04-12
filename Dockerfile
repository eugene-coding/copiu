FROM yiisoftware/yii2-php:7.4-apache

# Project source-code
WORKDIR /app

# Define an argument for migration flag
ARG RUN_MIGRATIONS=false
ENV RUN_MIGRATIONS=${RUN_MIGRATIONS}

# Copy composer files
ADD ./ /app
# Install cron
RUN apt-get update && apt-get install -y cron && /usr/local/bin/composer install --prefer-dist
# Add cron job file
ADD cronjob /etc/cron.d/cronjob
# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cronjob
# Apply cron job
RUN crontab /etc/cron.d/cronjob
# Run migrations only if RUN_MIGRATIONS is set to true
RUN if [ "$RUN_MIGRATIONS" = "true" ] ; then \
        php yii migrate --interactive=0 ; \
    fi

# Copy the rest of the project files

ENV PATH /app/vendor/bin:${PATH}

# Start cron service
CMD ["cron", "-f"]