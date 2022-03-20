FROM php:8.0-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# default repertory 
WORKDIR /var/www/symfony_docker


# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# install symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# install npm package globally
RUN npm install i -g watch
RUN cd /var/www/symfony_docker
RUN npm install

RUN 1 0 1 1 * php bin/console app:remove:adherent > etc/cron.d/remove_adherent
CMD ["crontab", "-e", "etc/cron.d/remove_adherent"]