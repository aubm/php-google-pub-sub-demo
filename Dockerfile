FROM php:7.4-cli
COPY vendor /usr/src/app/vendor
COPY app.php /usr/src/app/app.php
WORKDIR /usr/src/app
CMD [ "php", "./app.php" ]