-- ============================================================
-- BASE DE DONNÉES AS OLYMPIQUE SAINT-RÉMY
-- TD Cybersécurité - BTS SIO SLAM
-- ============================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS as_olympique_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE as_olympique_db;

-- ============================================================
-- TABLE DES MEMBRES
-- ============================================================
CREATE TABLE IF NOT EXISTS membres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Pour password_hash()
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    telephone VARCHAR(20),
    adresse TEXT,
    date_naissance DATE,
    role ENUM('membre', 'admin', 'secretaire', 'tresorier') DEFAULT 'membre',
    actif TINYINT(1) DEFAULT 1,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME,
    INDEX idx_login (login),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES ACTIVITÉS
-- ============================================================
CREATE TABLE IF NOT EXISTS activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    jour_semaine ENUM('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
    heure_debut TIME,
    heure_fin TIME,
    lieu VARCHAR(200),
    places_max INT DEFAULT 20,
    tarif DECIMAL(10,2) DEFAULT 0.00,
    actif TINYINT(1) DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES INSCRIPTIONS AUX ACTIVITÉS
-- ============================================================
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    activite_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente',
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE,
    FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inscription (membre_id, activite_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES COMMENTAIRES (pour exercice XSS)
-- ============================================================
CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auteur VARCHAR(100) DEFAULT 'Anonyme',
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    membre_id INT,
    modere TINYINT(1) DEFAULT 0,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE SET NULL,
    INDEX idx_date (date_creation)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES FICHIERS UPLOADÉS
-- ============================================================
CREATE TABLE IF NOT EXISTS fichiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_original VARCHAR(255) NOT NULL,
    nom_serveur VARCHAR(255) NOT NULL UNIQUE,
    mime_type VARCHAR(100),
    taille INT,
    membre_id INT,
    date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE SET NULL,
    INDEX idx_nom_serveur (nom_serveur)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES RÉSERVATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    equipement VARCHAR(100),
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES RÉSULTATS SPORTIFS
-- ============================================================
CREATE TABLE IF NOT EXISTS resultats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activite_id INT NOT NULL,
    date_competition DATE NOT NULL,
    lieu VARCHAR(200),
    description TEXT,
    classement VARCHAR(50),
    score VARCHAR(100),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES TENTATIVES DE CONNEXION (anti-bruteforce)
-- ============================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) DEFAULT 0,
    INDEX idx_login_time (login, attempt_time),
    INDEX idx_ip_time (ip_address, attempt_time)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE DES LOGS D'ACCÈS (pour audit)
-- ============================================================
CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE SET NULL,
    INDEX idx_membre_date (membre_id, date_action),
    INDEX idx_action (action)
) ENGINE=InnoDB;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Membres (mots de passe hashés avec password_hash())
-- Mot de passe en clair pour les tests : "password123"
-- Hash généré : password_hash('password123', PASSWORD_DEFAULT)

INSERT INTO membres (login, password, nom, prenom, email, role) VALUES
-- Pour la version vulnérable (mots de passe en clair) :
('admin', 'admin123', 'Administrateur', 'Super', 'admin@as-olympique.fr', 'admin'),
('jean', 'password123', 'Dupont', 'Jean', 'jean.dupont@email.com', 'membre'),
('marie', 'marie2024', 'Martin', 'Marie', 'marie.martin@email.com', 'secretaire'),
('pierre', 'motdepasse', 'Bernard', 'Pierre', 'pierre.bernard@email.com', 'membre');

-- Pour la version sécurisée (mots de passe hashés) :
-- Exécuter ce UPDATE après avoir créé les utilisateurs :
-- UPDATE membres SET password = '$2y$10$YourHashedPasswordHere' WHERE login = 'admin';

-- Activités
INSERT INTO activites (nom, description, jour_semaine, heure_debut, heure_fin, lieu, places_max, tarif) VALUES
('Football', 'Entraînement de football tous niveaux', 'mercredi', '18:00', '20:00', 'Stade Municipal', 22, 150.00),
('Tennis', 'Cours de tennis adultes', 'samedi', '10:00', '12:00', 'Courts de tennis', 8, 200.00),
('Natation', 'Cours de natation', 'mardi', '19:00', '20:30', 'Piscine municipale', 15, 180.00),
('Yoga', 'Séances de yoga relaxation', 'jeudi', '18:30', '19:30', 'Salle polyvalente', 20, 120.00),
('Basketball', 'Entraînement de basket', 'vendredi', '19:00', '21:00', 'Gymnase', 12, 140.00);

-- Commentaires de test
INSERT INTO commentaires (auteur, contenu) VALUES
('Jean', 'Super association ! Les entraîneurs sont top.'),
('Marie', 'J\'ai adoré le cours de yoga cette semaine.'),
('Anonyme', 'Vivement la prochaine compétition !'),
('Pierre', 'Le terrain de foot est en excellent état.');

-- Inscriptions de test
INSERT INTO inscriptions (membre_id, activite_id, statut) VALUES
(2, 1, 'validee'),  -- Jean inscrit au Football
(2, 3, 'validee'),  -- Jean inscrit à la Natation
(3, 4, 'validee'),  -- Marie inscrite au Yoga
(4, 1, 'en_attente'); -- Pierre en attente pour Football

-- ============================================================
-- CRÉATION DE L'UTILISATEUR MYSQL
-- ============================================================

-- Créer l'utilisateur as_user avec le mot de passe as_pwd
-- À exécuter en tant que root :

-- CREATE USER IF NOT EXISTS 'as_user'@'localhost' IDENTIFIED BY 'as_pwd';
-- GRANT ALL PRIVILEGES ON as_olympique_db.* TO 'as_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================================
-- PROCÉDURE POUR HASHER LES MOTS DE PASSE EXISTANTS
-- ============================================================
-- Pour hasher les mots de passe en PHP :
-- 
-- $hash = password_hash('admin123', PASSWORD_DEFAULT);
-- // Résultat : $2y$10$...
-- 
-- Puis UPDATE :
-- UPDATE membres SET password = '$2y$10$...' WHERE login = 'admin';

-- ============================================================
-- NETTOYAGE DES ANCIENNES TENTATIVES DE CONNEXION
-- ============================================================
-- À exécuter périodiquement (cron job) :
-- DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR);
