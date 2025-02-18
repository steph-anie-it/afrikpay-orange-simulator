# Utilise l'image officielle PHP avec Apache
FROM php:8.1.14-apache

# Mise à jour et installation des dépendances
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libxml2-dev \
    libonig-dev \
    libpq-dev \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installe APCu pour la mise en cache
RUN pecl install apcu && docker-php-ext-enable apcu

# Ajoute le ServerName dans la configuration Apache
RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définit le répertoire de travail
WORKDIR /var/www/html/

# Copie les fichiers de l'application Symfony
COPY . /var/www/html/

# Ajuste les droits
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 775 /var/www/html/var \
    && chmod -R 775 /var/www/html/public

# Expose le port 8000
EXPOSE 8000

# Lancement d'Apache
CMD ["apache2-foreground"]
