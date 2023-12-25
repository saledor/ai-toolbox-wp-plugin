FROM wordpress:latest

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Add Xdebug configuration
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Create Xdebug log directory and file, and set permissions
RUN mkdir -p /var/log/xdebug && touch /var/log/xdebug.log && chmod 777 /var/log/xdebug.log

# Ensure the log directory and file are owned by the www-data user
RUN chown www-data:www-data /var/log/xdebug.log
