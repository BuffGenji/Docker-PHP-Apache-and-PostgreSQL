# An official Hub image to start with
FROM php:8.2-apache

# This is a recommendation, but may not be needed - depending on if your framework already does this out of the box
# and/or allows you to use an element or inner tool to configure it.
RUN a2enmod rewrite

# This installs on a Debian distribution ( and maybe others like it, but compatibility is not guaranteed ) the necessary libraries that PostgreSQl
# will need to work properly - the first 2 lines - and then there are the  actual PHP extensions needed to run a postgres dtabase connection.
# and that is the pdo and the pdo_pgsql extension.
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
# Sets the working directory to /var/www/html ( Default in Apache) - which just means the server's root directory (where it serves pages from)
WORKDIR /var/www/html

# All of the content that is your source code will be placed in the /var/www/html
# which is the default root directory in Apache, meaning the entry point will be the index file found in app/ 
COPY ../app/ .