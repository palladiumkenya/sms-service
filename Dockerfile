FROM php:7.3.0
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo mbstring  mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
WORKDIR /app
COPY . /app
RUN composer  install --ignore-platform-reqs --no-dev
EXPOSE 8181
CMD ["php", "-S", "0.0.0.0:8181", "-t", "public/"]
