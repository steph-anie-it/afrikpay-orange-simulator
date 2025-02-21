FROM composer:2.4.2 as composer

# Utilise l'image officielle PHP avec Apache
FROM php:8.1-fpm-alpine3.16

# Installation des paquets système et des extensions PHP
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    autoconf \
    libzip-dev \
    unzip \
    build-base \
    libxml2-dev \
    oniguruma-dev 

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    intl

# Installe APCu pour la mise en cache
RUN pecl install apcu \
    && docker-php-ext-enable apcu \
    && rm -rf /tmp/pear

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Définit le répertoire de travail
WORKDIR /usr/src/app

# Copie des fichiers composer pour optimiser le cache Docker
COPY composer.json composer.lock /usr/src/app/

# Copie de Composer depuis l'image temporaire
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Ajout de vendor/bin au PATH pour exécuter Symfony et Composer
ENV PATH="/usr/src/app/vendor/bin:${PATH}"

# Installation des dépendances PHP avec Composer
RUN composer install --no-scripts --no-dev --optimize-autoloader

# Copie du reste du projet après installation des dépendances (optimisation du cache)
COPY . /usr/src/app/

# Ajuste les droits et passe à un utilisateur non-root pour plus de sécurité
RUN chown -R 1000:1000 /usr/src/app
USER 1000:1000