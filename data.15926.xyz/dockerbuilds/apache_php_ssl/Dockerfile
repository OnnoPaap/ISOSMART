# Use an official PHP image as a parent image
FROM php:7.1-apache

# Set environment variable to avoid interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive

# Install apt-utils first to avoid the debconf error
RUN apt-get update && apt-get install -y apt-utils

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libzip-dev \
    zip \
    unzip

RUN docker-php-ext-install -j$(nproc) gd

# Enable Apache modules
RUN a2enmod rewrite ssl

# Enable headers for allowing Cross-Origin Resource Sharing (CORS) in the conf file
RUN a2enmod headers

# Copy the Apache configuration file
COPY default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# Copy website files
COPY ./website/ /var/www/html/

# Start Apache
CMD ["apache2-foreground"]
