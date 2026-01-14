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

-- ============================================================
-- OPTIMIZATIONS: ADDITIONAL INDEXES FOR PERFORMANCE
-- ============================================================

-- Additional indexes for membres table
ALTER TABLE membres ADD INDEX idx_role_actif (role, actif);
ALTER TABLE membres ADD INDEX idx_actif (actif);
ALTER TABLE membres ADD INDEX idx_derniere_connexion (derniere_connexion);

-- Additional indexes for activites table
ALTER TABLE activites ADD INDEX idx_actif (actif);
ALTER TABLE activites ADD INDEX idx_jour_semaine (jour_semaine);

-- Additional indexes for inscriptions table
ALTER TABLE inscriptions ADD INDEX idx_statut (statut);
ALTER TABLE inscriptions ADD INDEX idx_date_inscription (date_inscription);
ALTER TABLE inscriptions ADD INDEX idx_membre_statut (membre_id, statut);

-- Additional indexes for commentaires table
ALTER TABLE commentaires ADD INDEX idx_modere (modere);
ALTER TABLE commentaires ADD INDEX idx_membre_date (membre_id, date_creation);

-- Additional indexes for fichiers table
ALTER TABLE fichiers ADD INDEX idx_membre_date (membre_id, date_upload);
ALTER TABLE fichiers ADD INDEX idx_mime_type (mime_type);

-- Additional indexes for reservations table
ALTER TABLE reservations ADD INDEX idx_date_reservation (date_reservation);
ALTER TABLE reservations ADD INDEX idx_statut (statut);
ALTER TABLE reservations ADD INDEX idx_membre_date (membre_id, date_reservation);

-- ============================================================
-- STORED PROCEDURES: COMMON OPERATIONS
-- ============================================================

-- Procedure to authenticate user
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_authenticate_user(
    IN p_login VARCHAR(50)
)
BEGIN
    SELECT id, login, password, nom, prenom, email, role, actif
    FROM membres
    WHERE login = p_login AND actif = 1
    LIMIT 1;
    
    -- Update last connection time
    UPDATE membres 
    SET derniere_connexion = NOW() 
    WHERE login = p_login;
END$$
DELIMITER ;

-- Procedure to get active activities
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_get_active_activities()
BEGIN
    SELECT id, nom, description, jour_semaine, heure_debut, heure_fin, 
           lieu, places_max, tarif
    FROM activites
    WHERE actif = 1
    ORDER BY jour_semaine, heure_debut;
END$$
DELIMITER ;

-- Procedure to register user to activity
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_register_activity(
    IN p_membre_id INT,
    IN p_activite_id INT,
    OUT p_result VARCHAR(100)
)
BEGIN
    DECLARE v_count INT;
    DECLARE v_places_max INT;
    DECLARE v_inscriptions INT;
    
    -- Check if already registered
    SELECT COUNT(*) INTO v_count
    FROM inscriptions
    WHERE membre_id = p_membre_id AND activite_id = p_activite_id;
    
    IF v_count > 0 THEN
        SET p_result = 'ALREADY_REGISTERED';
    ELSE
        -- Check if activity has available places
        SELECT places_max INTO v_places_max
        FROM activites
        WHERE id = p_activite_id AND actif = 1;
        
        SELECT COUNT(*) INTO v_inscriptions
        FROM inscriptions
        WHERE activite_id = p_activite_id AND statut = 'validee';
        
        IF v_inscriptions >= v_places_max THEN
            SET p_result = 'NO_PLACES_AVAILABLE';
        ELSE
            -- Register user
            INSERT INTO inscriptions (membre_id, activite_id, statut)
            VALUES (p_membre_id, p_activite_id, 'en_attente');
            
            SET p_result = 'SUCCESS';
        END IF;
    END IF;
END$$
DELIMITER ;

-- Procedure to clean old login attempts
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_clean_old_login_attempts()
BEGIN
    DELETE FROM login_attempts 
    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    SELECT ROW_COUNT() as deleted_rows;
END$$
DELIMITER ;

-- Procedure to get user statistics
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_get_user_stats(
    IN p_membre_id INT
)
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM inscriptions WHERE membre_id = p_membre_id) as total_inscriptions,
        (SELECT COUNT(*) FROM inscriptions WHERE membre_id = p_membre_id AND statut = 'validee') as inscriptions_validees,
        (SELECT COUNT(*) FROM commentaires WHERE membre_id = p_membre_id) as total_commentaires,
        (SELECT COUNT(*) FROM fichiers WHERE membre_id = p_membre_id) as total_fichiers;
END$$
DELIMITER ;

-- ============================================================
-- TRIGGERS: AUDIT LOGGING
-- ============================================================

-- Trigger to log when a member is deleted
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_membre_delete_log
BEFORE DELETE ON membres
FOR EACH ROW
BEGIN
    INSERT INTO access_logs (membre_id, action, details, ip_address)
    VALUES (OLD.id, 'MEMBER_DELETED', 
            CONCAT('Member ', OLD.login, ' (', OLD.nom, ') was deleted'), 
            'SYSTEM');
END$$
DELIMITER ;

-- Trigger to log successful inscriptions
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_inscription_created
AFTER INSERT ON inscriptions
FOR EACH ROW
BEGIN
    INSERT INTO access_logs (membre_id, action, details)
    VALUES (NEW.membre_id, 'INSCRIPTION_CREATED', 
            CONCAT('Inscribed to activity ID: ', NEW.activite_id));
END$$
DELIMITER ;

-- Trigger to log when inscription status changes
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_inscription_status_change
AFTER UPDATE ON inscriptions
FOR EACH ROW
BEGIN
    IF OLD.statut != NEW.statut THEN
        INSERT INTO access_logs (membre_id, action, details)
        VALUES (NEW.membre_id, 'INSCRIPTION_STATUS_CHANGED', 
                CONCAT('Activity ID: ', NEW.activite_id, 
                       ' - Status changed from ', OLD.statut, ' to ', NEW.statut));
    END IF;
END$$
DELIMITER ;

-- Trigger to log file uploads
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_fichier_uploaded
AFTER INSERT ON fichiers
FOR EACH ROW
BEGIN
    INSERT INTO access_logs (membre_id, action, details)
    VALUES (NEW.membre_id, 'FILE_UPLOADED', 
            CONCAT('File: ', NEW.nom_original, ' (', NEW.mime_type, ', ', NEW.taille, ' bytes)'));
END$$
DELIMITER ;

-- Trigger to prevent deletion of active activities with registrations
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_prevent_activity_delete
BEFORE DELETE ON activites
FOR EACH ROW
BEGIN
    DECLARE v_inscriptions_count INT;
    
    SELECT COUNT(*) INTO v_inscriptions_count
    FROM inscriptions
    WHERE activite_id = OLD.id AND statut IN ('validee', 'en_attente');
    
    IF v_inscriptions_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete activity with active registrations';
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- VIEWS: COMMON QUERIES
-- ============================================================

-- View for active members with their statistics
CREATE OR REPLACE VIEW v_membres_actifs AS
SELECT 
    m.id,
    m.login,
    m.nom,
    m.prenom,
    m.email,
    m.role,
    m.date_inscription,
    m.derniere_connexion,
    COUNT(DISTINCT i.id) as total_inscriptions,
    COUNT(DISTINCT c.id) as total_commentaires
FROM membres m
LEFT JOIN inscriptions i ON m.id = i.membre_id AND i.statut = 'validee'
LEFT JOIN commentaires c ON m.id = c.membre_id
WHERE m.actif = 1
GROUP BY m.id;

-- View for activities with registration count
CREATE OR REPLACE VIEW v_activites_disponibles AS
SELECT 
    a.id,
    a.nom,
    a.description,
    a.jour_semaine,
    a.heure_debut,
    a.heure_fin,
    a.lieu,
    a.places_max,
    a.tarif,
    COUNT(i.id) as inscriptions_count,
    (a.places_max - COUNT(i.id)) as places_disponibles
FROM activites a
LEFT JOIN inscriptions i ON a.id = i.activite_id AND i.statut = 'validee'
WHERE a.actif = 1
GROUP BY a.id;

-- View for recent login attempts
CREATE OR REPLACE VIEW v_recent_login_attempts AS
SELECT 
    la.login,
    la.ip_address,
    la.attempt_time,
    la.success,
    m.nom,
    m.prenom
FROM login_attempts la
LEFT JOIN membres m ON la.login = m.login
WHERE la.attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY la.attempt_time DESC;

-- ============================================================
-- DATABASE ENCRYPTION RECOMMENDATIONS
-- ============================================================

-- To enable SSL/TLS encryption for MySQL connections:
-- 1. Generate SSL certificates for MySQL server
-- 2. Configure MySQL server with SSL enabled:
--    [mysqld]
--    ssl-ca=/path/to/ca.pem
--    ssl-cert=/path/to/server-cert.pem
--    ssl-key=/path/to/server-key.pem
--    require_secure_transport=ON
--
-- 3. Grant privileges with SSL requirement:
--    GRANT ALL PRIVILEGES ON as_olympique_db.* TO 'as_user'@'localhost' REQUIRE SSL;
--
-- 4. In PHP PDO connection, add SSL options:
--    $options = [
--        PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
--        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
--    ];

-- ============================================================
-- PERFORMANCE TUNING RECOMMENDATIONS
-- ============================================================

-- Monitor slow queries
-- SET GLOBAL slow_query_log = 'ON';
-- SET GLOBAL long_query_time = 2;
-- SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';

-- Enable query cache (MySQL 5.7 and earlier)
-- SET GLOBAL query_cache_type = 1;
-- SET GLOBAL query_cache_size = 67108864; -- 64MB

-- Optimize InnoDB buffer pool
-- SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB (adjust based on available RAM)

-- Enable InnoDB file-per-table
-- SET GLOBAL innodb_file_per_table = 1;

-- ============================================================
-- MAINTENANCE SCHEDULE RECOMMENDATIONS
-- ============================================================

-- Daily: Clean old login attempts
-- CALL sp_clean_old_login_attempts();

-- Weekly: Optimize tables
-- OPTIMIZE TABLE membres, activites, inscriptions, commentaires, fichiers;

-- Monthly: Analyze tables for query optimization
-- ANALYZE TABLE membres, activites, inscriptions, commentaires, fichiers;

-- ============================================================
-- BACKUP RECOMMENDATIONS
-- ============================================================

-- Full backup (execute via shell):
-- mysqldump -u root -p --single-transaction --routines --triggers as_olympique_db > backup_$(date +%Y%m%d).sql

-- Incremental backup using binary logs:
-- Enable binary logging in my.cnf:
-- [mysqld]
-- log-bin=mysql-bin
-- binlog-format=ROW
-- expire_logs_days=7

-- ============================================================
-- SECURITY BEST PRACTICES
-- ============================================================

-- 1. Use separate database users for different operations:
-- CREATE USER 'as_read_only'@'localhost' IDENTIFIED BY 'strong_password';
-- GRANT SELECT ON as_olympique_db.* TO 'as_read_only'@'localhost';
--
-- CREATE USER 'as_app_user'@'localhost' IDENTIFIED BY 'strong_password';
-- GRANT SELECT, INSERT, UPDATE ON as_olympique_db.* TO 'as_app_user'@'localhost';
--
-- CREATE USER 'as_admin'@'localhost' IDENTIFIED BY 'strong_password';
-- GRANT ALL PRIVILEGES ON as_olympique_db.* TO 'as_admin'@'localhost';

-- 2. Enable binary logging for point-in-time recovery

-- 3. Regularly update MySQL server and apply security patches

-- 4. Limit database access by IP address:
-- CREATE USER 'as_user'@'192.168.1.%' IDENTIFIED BY 'password';

-- 5. Use strong passwords and rotate them regularly

-- 6. Monitor failed login attempts and unusual activity

-- ============================================================
-- END OF OPTIMIZATIONS
-- ============================================================

