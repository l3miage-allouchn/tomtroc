<?php

// configuration de la base de données pour Docker
// les valeurs viennent des variables d'environnement définies dans docker-compose.yml (et le fichier .env)
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'dbname' => getenv('DB_NAME') ?: 'tomtroc',
    'user' => getenv('DB_USER') ?: 'tomtroc',
    'password' => getenv('DB_PASSWORD') ?: '',
];
