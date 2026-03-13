FROM php:8.1-fpm

# ホストのUID/GIDと一致させるための引数（デフォルト値：1000）
ARG USER_ID=1000
ARG GROUP_ID=1000

COPY php.ini /usr/local/etc/php/

# Install PHP extensions and system dependencies
RUN apt update \
    && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip curl gnupg2 \
    && docker-php-ext-install pdo_mysql zip

# Install Node.js 18 LTS
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update

# ホストと同じUID/GIDでユーザーとグループを作成
RUN groupadd -g $GROUP_ID laravel \
    && useradd -u $USER_ID -g laravel -m -s /bin/bash laravel

# 作業ディレクトリを設定し、laravelユーザーに所有権を付与
WORKDIR /var/www
RUN chown -R laravel:laravel /var/www

# 以降の処理はlaravelユーザーで実行
USER laravel
