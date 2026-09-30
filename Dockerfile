# Image de l'application TomTroc : PHP 8.2 + Apache
# version précise de l'image (et non "latest") : les builds sont reproductibles
FROM php:8.2-apache

# pdo_mysql : connexion à MariaDB / MySQL
# mod_rewrite : le .htaccess de public/ fait passer toutes les URL par index.php
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# le site utilise des adresses en /tomtroc/public/... : on garde la même arborescence que sous XAMPP
WORKDIR /var/www/html/tomtroc
COPY . .

# configuration propre à Docker : la base est lue dans les variables d'environnement (aucun mot de passe dans l'image)
COPY docker/config.php config.php

# la racine du serveur redirige vers le site
RUN echo 'RedirectMatch ^/$ /tomtroc/public/' > /etc/apache2/conf-enabled/tomtroc.conf

# dossier des images envoyées par les membres : Apache (www-data) doit pouvoir y écrire
RUN mkdir -p public/uploads \
    && chown -R www-data:www-data public/uploads

EXPOSE 80
