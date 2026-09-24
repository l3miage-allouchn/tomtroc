-- Structure des tables

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'unavailable', 'exchanged') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Données de test
-- Mot de passe en clair pour les 3 comptes : password123

INSERT INTO users (pseudo, email, password, bio) VALUES
('Alice', 'alice@tomtroc.fr', '$2y$10$W2DncNKoR4Vg41BBD0Z.fOK9PBAMVTCTOuvlaikA/WlvtWul2ZlIa', 'Passionnée de romans policiers.'),
('Bob', 'bob@tomtroc.fr', '$2y$10$W2DncNKoR4Vg41BBD0Z.fOK9PBAMVTCTOuvlaikA/WlvtWul2ZlIa', 'Amateur de science-fiction.'),
('Chloe', 'chloe@tomtroc.fr', '$2y$10$W2DncNKoR4Vg41BBD0Z.fOK9PBAMVTCTOuvlaikA/WlvtWul2ZlIa', 'Grande lectrice de classiques.');

INSERT INTO books (user_id, title, author, description, status) VALUES
(1, 'Le Chien des Baskerville', 'Arthur Conan Doyle', 'Une enquête de Sherlock Holmes sur fond de légende maudite.', 'available'),
(1, 'Ils étaient dix', 'Agatha Christie', 'Dix invités, une île isolée, un meurtrier parmi eux.', 'available'),
(2, 'Dune', 'Frank Herbert', 'Sur la planète Arrakis, une lutte de pouvoir autour de l\'épice.', 'available'),
(2, 'Fondation', 'Isaac Asimov', 'Un mathématicien prédit la chute d\'un empire galactique.', 'unavailable'),
(3, '1984', 'George Orwell', 'Un régime totalitaire sous surveillance permanente.', 'available');

INSERT INTO messages (sender_id, receiver_id, content, is_read) VALUES
(2, 1, 'Bonjour, ton livre "Le Chien des Baskerville" est-il toujours disponible ?', 1),
(1, 2, 'Oui tout à fait, on peut se voir cette semaine si tu veux.', 0),
(3, 2, 'Salut, je suis intéressée par "Dune", tu l\'échanges contre quoi ?', 0);
