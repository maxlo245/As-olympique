# AS Olympique Saint-Rémy - TD Cybersécurité OWASP

## Installation rapide (MAMP)

**Voir le fichier [INSTALL.md](INSTALL.md) pour les instructions détaillées.**

### Résumé :
1. Copier `as_olympique/` dans `/Applications/MAMP/htdocs/`
2. Démarrer MAMP (Apache + MySQL)
3. Créer la base `as_olympique_db` dans phpMyAdmin
4. Importer `database/as_olympique_db.sql`
5. Accéder à : `http://localhost:8888/as_olympique/src/`

---

## Présentation

Application de gestion pour l'association sportive **AS Olympique Saint-Rémy**, développée dans le cadre d'un TD de cybersécurité (Bloc 3 - BTS SIO SLAM).

Cette application contient **volontairement des failles de sécurité** à des fins pédagogiques.

## Avertissement

> **Ce projet est destiné uniquement à un usage éducatif en environnement local.**
> 
> - Ne JAMAIS déployer ce code en production
> - Ne JAMAIS tester ces attaques sur des systèmes réels
> - Ne JAMAIS utiliser sur un réseau public

## Objectifs du TD

1. Comprendre les principales failles OWASP
2. Exploiter les vulnérabilités (dans un cadre pédagogique)
3. Implémenter les contre-mesures appropriées
4. Adopter les bonnes pratiques de développement sécurisé

## Structure du projet

```
as_olympique/
├── INSTALL.md                  # Instructions d'installation MAMP
├── src/
│   ├── config.php              # Configuration BDD (modifier pour MAMP)
│   ├── init.php                # Initialisation session + PDO
│   ├── functions.php           # Fonctions de sécurité
│   ├── index.php               # Page d'accueil
│   ├── vuln/                   # Versions VULNÉRABLES
│   │   ├── upload_vuln.php     # Partie A - Upload
│   │   ├── bonjour_vuln.php    # Partie B - XSS reflétée
│   │   ├── connexion_vuln.php  # Partie C - Injection SQL
│   │   ├── commentaire_vuln.php# Partie D - XSS stockée
│   │   ├── auth_vuln.php       # Partie E - Session
│   │   ├── del_vuln.php        # Partie F - CSRF
│   │   └── parse_vuln_xml.php  # Partie G - XXE
│   ├── secure/                 # Versions SÉCURISÉES
│   │   ├── upload_secure.php
│   │   ├── bonjour_secure.php
│   │   ├── connexion_secure.php
│   │   ├── commentaire_secure.php
│   │   ├── auth_secure.php
│   │   ├── del_secure.php
│   │   └── parse_secure_xml.php
│   └── templates/
│       └── header.php          # Template HTML
├── uploads/                    # Stockage fichiers
├── database/
│   └── as_olympique_db.sql     # Script SQL
└── README.md
```

## Planning du TD (9 heures)

| Durée | Partie | Faille |
|-------|--------|--------|
| 1h | Préparation | Installation + configuration |
| 1h30 | A | Envoi de fichier (Upload) |
| 1h | B | Transmission URL (XSS reflétée) |
| 1h30 | C | Injection SQL |
| 1h30 | D | XSS (reflétée + stockée) |
| 1h | E | Piratage de session |
| 1h | F | CSRF |
| 0h30 | G | XXE (XML) |

## Installation

### Prérequis

- WAMP / XAMPP / MAMP / LAMP
- PHP 7.4+ (ou 8.x)
- MySQL / MariaDB
- phpMyAdmin

### Étapes

1. **Cloner/Copier le projet** dans votre répertoire web
   ```
   C:\wamp64\www\as_olympique\
   ```

2. **Créer la base de données**
   - Ouvrir phpMyAdmin
   - Importer `database/as_olympique_db.sql`

3. **Créer l'utilisateur MySQL**
   ```sql
   CREATE USER 'as_user'@'localhost' IDENTIFIED BY 'as_pwd';
   GRANT ALL PRIVILEGES ON as_olympique_db.* TO 'as_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **Accéder à l'application**
   ```
   http://localhost/as_olympique/src/
   ```

## Comptes de test

| Login | Mot de passe | Rôle |
|-------|--------------|------|
| admin | admin123 | Administrateur |
| jean | password123 | Membre |
| marie | marie2024 | Secrétaire |

## Failles OWASP couvertes

| # | Faille | Fichier vulnérable | Contre-mesure |
|---|--------|-------------------|---------------|
| A | Upload malveillant | upload_vuln.php | Validation MIME, whitelist, renommage |
| B | XSS reflétée | bonjour_vuln.php | htmlspecialchars() |
| C | Injection SQL | connexion_vuln.php | Requêtes préparées PDO |
| D | XSS stockée | commentaire_vuln.php | Échappement + CSP |
| E | Session hijacking | auth_vuln.php | Config session, regenerate_id |
| F | CSRF | del_vuln.php | Tokens CSRF |
| G | XXE | parse_vuln_xml.php | Désactiver entités externes |

## Ressources

- [OWASP Top 10 2021](https://owasp.org/Top10/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Manual](https://www.php.net/manual/en/security.php)
- [CWE - Common Weakness Enumeration](https://cwe.mitre.org/)

---

## Architecture et Optimisations (Janvier 2026)

### Nouvelles Fonctionnalités

Ce projet a été optimisé avec une architecture moderne tout en maintenant sa valeur éducative.

#### Structure PSR-4

```
src/
├── Core/                    # Classes principales
│   ├── Config.php          # Gestion configuration (.env)
│   ├── Database.php        # Singleton PDO avec retry
│   └── Security.php        # Fonctions de sécurité
├── assets/
│   ├── css/style.css       # Styles externalisés
│   └── js/main.js          # JavaScript externalisé
├── helpers.php             # Fonctions globales
├── init.php                # Initialisation améliorée
├── vuln/                   # Versions vulnérables
└── secure/                 # Versions sécurisées
```

#### Optimisations de Performance

- **Base de données** :
  - Singleton pattern avec connection pooling
  - Retry logic automatique
  - Indexes optimisés
  - Stored procedures pour opérations courantes
  - Triggers pour audit logging

- **Frontend** :
  - CSS et JS externalisés
  - Preloading et caching
  - Compression Gzip (.htaccess)

- **PHP** :
  - Opcode caching (OPcache)
  - Output buffering
  - Type hints PHP 8+
  - PSR-12 compliant

#### Sécurité Renforcée

- **Headers de sécurité** :
  - X-Content-Type-Options
  - X-Frame-Options
  - Content-Security-Policy
  - Referrer-Policy

- **Fonctionnalités** :
  - CSRF tokens avec expiration
  - Rate limiting pour connexions
  - Sessions sécurisées
  - Validation MIME pour uploads
  - Password hashing (bcrypt)

### Installation avec Docker

```bash
# Démarrer l'environnement complet
docker-compose up -d

# Accéder à l'application
http://localhost:8888

# Accéder à phpMyAdmin
http://localhost:8081
```

### Installation avec Composer

```bash
# Installer les dépendances
composer install

# Copier la configuration
cp .env.example .env

# Éditer .env avec vos paramètres
nano .env

# Lancer les tests
composer test
```

### Variables d'Environnement

Le projet supporte maintenant la configuration via `.env` :

```env
DB_HOST=localhost
DB_NAME=as_olympique_db
DB_USER=root
DB_PASS=root

APP_ENV=development
APP_DEBUG=true

RATE_LIMIT_LOGIN_ATTEMPTS=5
CSRF_TOKEN_LIFETIME=3600
```

### Tests Unitaires

Des tests PHPUnit sont fournis pour les classes principales :

```bash
# Exécuter tous les tests
./vendor/bin/phpunit

# Tests avec couverture de code
./vendor/bin/phpunit --coverage-html coverage
```

### Documentation Additionnelle

- [SECURITY.md](SECURITY.md) - Politique de sécurité
- [CONTRIBUTING.md](CONTRIBUTING.md) - Guide de contribution
- [INSTALL.md](INSTALL.md) - Instructions d'installation détaillées

### Compatibilité

- **PHP** : 7.4+ (recommandé: PHP 8.3+)
- **MySQL** : 5.7+ ou MariaDB 10.x+
- **Serveurs** : Apache 2.4+, Nginx 1.18+

### Outils de Développement

```bash
# Vérifier le style de code
composer cs-check

# Corriger le style de code
composer cs-fix

# Exécuter les tests
composer test
```

### Ressources Techniques

- **PSR-12** : [PHP Standards Recommendations](https://www.php-fig.org/psr/psr-12/)
- **OWASP** : [Top 10 2021](https://owasp.org/Top10/)
- **Docker** : [Documentation officielle](https://docs.docker.com/)
- **Composer** : [Dependency Manager](https://getcomposer.org/)

---

*Dernière mise à jour : Janvier 2026*

