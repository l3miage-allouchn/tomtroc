# Image de l'application TomTroc : PHP 8.2 + Apache
# version précise de l'image (et non "latest") : les builds sont reproductibles
FROM php:8.2-apache

# mises à jour de sécurité des paquets Debian de l'image de base
# (sans elles, Trivy détecte des failles connues déjà corrigées, par exemple dans OpenSSL)
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

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

# le conteneur ne tourne pas en root (bonne pratique de sécurité, contrôle Trivy DS002)
# un utilisateur sans privilèges ne peut pas écouter sur le port 80 : Apache écoute sur 8080
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

USER www-data

EXPOSE 8080
