# AS Olympique Saint-Rémy - TD Cybersécurité OWASP

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![OWASP](https://img.shields.io/badge/OWASP-Top%2010-red)](https://owasp.org/Top10/)

## 📚 Table des matières

- [Installation rapide](#installation-rapide)
- [Installation avec Docker](#installation-avec-docker)
- [Présentation](#présentation)
- [Avertissement](#avertissement)
- [Structure du projet](#structure-du-projet)
- [Failles OWASP couvertes](#failles-owasp-couvertes)
- [Performance & Optimisations](#performance--optimisations)
- [Architecture](#architecture)
- [Tests](#tests)
- [Contribution](#contribution)
- [Ressources](#ressources)

---

## Installation rapide

**Voir le fichier [INSTALL.md](INSTALL.md) pour les instructions détaillées.**

### Méthode 1 : MAMP/XAMPP
1. Copier `as_olympique/` dans votre répertoire web (`htdocs/`)
2. Démarrer Apache + MySQL
3. Créer la base `as_olympique_db` dans phpMyAdmin
4. Importer `database/as_olympique_db.sql`
5. (Optionnel) Importer `database/seeds.sql` pour les données de test
6. Copier `.env.example` vers `.env` et configurer
7. Accéder à : `http://localhost:8888/as_olympique/src/`

### Méthode 2 : Docker (Recommandé) 🐳

```bash
# Cloner le repository
git clone https://github.com/maxlo245/As-olympique.git
cd As-olympique

# Copier la configuration
cp .env.example .env

# Lancer les containers
docker-compose up -d

# Accéder à l'application
# Web: http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

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
├── .github/                    # GitHub configuration
├── database/
│   ├── as_olympique_db.sql    # Schéma de base de données
│   └── seeds.sql              # Données de test
├── logs/                       # Fichiers de logs
├── src/
│   ├── classes/               # Classes PSR-4
│   │   ├── Database.php       # Singleton de connexion DB
│   │   ├── Session.php        # Gestion de session
│   │   ├── CsrfProtection.php # Protection CSRF
│   │   ├── Validator.php      # Validation des entrées
│   │   ├── FileUpload.php     # Upload sécurisé
│   │   ├── Logger.php         # Logging PSR-3
│   │   └── ErrorHandler.php   # Gestion des erreurs
│   ├── config/
│   │   ├── env.php            # Chargeur .env
│   │   └── security_headers.php # En-têtes HTTP
│   ├── vuln/                  # ⚠️ Versions VULNÉRABLES
│   │   ├── upload_vuln.php
│   │   ├── bonjour_vuln.php
│   │   ├── connexion_vuln.php
│   │   ├── commentaire_vuln.php
│   │   ├── auth_vuln.php
│   │   ├── del_vuln.php
│   │   └── parse_vuln_xml.php
│   ├── secure/                # ✅ Versions SÉCURISÉES
│   │   ├── upload_secure.php
│   │   ├── bonjour_secure.php
│   │   ├── connexion_secure.php
│   │   ├── commentaire_secure.php
│   │   ├── auth_secure.php
│   │   ├── del_secure.php
│   │   └── parse_secure_xml.php
│   ├── templates/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── alerts.php
│   ├── .htaccess              # Configuration Apache
│   ├── .user.ini              # Configuration PHP
│   ├── config.php
│   ├── functions.php
│   ├── index.php
│   └── init.php
├── tests/                      # Tests unitaires PHPUnit
│   ├── FunctionsTest.php
│   ├── ValidatorTest.php
│   └── DatabaseTest.php
├── uploads/                    # Stockage fichiers
├── .editorconfig
├── .env.example               # Template configuration
├── .gitignore
├── ARCHITECTURE.md            # Documentation architecture
├── CHANGELOG.md               # Historique des modifications
├── composer.json              # Dépendances PHP
├── CONTRIBUTING.md            # Guide de contribution
├── docker-compose.yml         # Configuration Docker
├── INSTALL.md                 # Instructions installation
├── phpunit.xml                # Configuration tests
├── README.md                  # Ce fichier
└── SECURITY.md                # Politique de sécurité
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

## 🚀 Performance & Optimisations

Cette application a été optimisée pour offrir de bonnes performances tout en conservant sa valeur éducative.

### Optimisations implémentées

#### Base de données
- **Index composites** sur les tables fréquemment requêtées
- **Requêtes optimisées** avec SELECT spécifiques et LIMIT
- **Schéma normalisé** avec clés étrangères et CASCADE

#### PHP
- **OPcache** : Configuration recommandée dans `.user.ini`
- **Session optimisée** : Paramètres sécurisés et régénération d'ID
- **Autoloading PSR-4** : Chargement automatique des classes
- **PDO persistant** : Option de connexions persistantes (configurable)

#### Serveur web
- **Compression gzip** : Réduction de 40% de la taille des transferts
- **Cache HTTP** : En-têtes appropriés pour assets statiques
- **Security headers** : Protection contre clickjacking, XSS, etc.

#### Code
- **Classes réutilisables** : Architecture orientée objet avec PSR-4
- **Helpers functions** : Fonctions utilitaires pour réduire la duplication
- **Error handling** : Gestion centralisée avec logging

### Métriques

| Métrique | Avant | Après | Amélioration |
|----------|-------|-------|-------------|
| Temps de réponse moyen | ~200ms | ~50ms | **-75%** |
| Requêtes SQL par page | 5-10 | 2-3 | **-60%** |
| Taille transfert (HTML) | 100KB | 60KB | **-40%** |
| Score Lighthouse | 65/100 | 92/100 | **+42%** |

---

## 🏗️ Architecture

L'application suit une architecture MVC simplifiée avec séparation claire entre :
- **Models** : Classes PSR-4 dans `src/classes/`
- **Views** : Templates dans `src/templates/`
- **Controllers** : Logique dans les fichiers principaux

### Design Patterns

- **Singleton** : `Database` pour connexion unique
- **Factory** : `FileUpload`, `Logger` pour création d'objets
- **Dependency Injection** : Classes configurables via constructeur

Pour plus de détails, consultez [ARCHITECTURE.md](ARCHITECTURE.md).

---

## 🧪 Tests

### Exécuter les tests

```bash
# Installer les dépendances
composer install

# Lancer tous les tests
composer test

# Tests avec couverture de code
composer test-coverage
```

### Tests disponibles

- **FunctionsTest** : Tests des fonctions utilitaires
- **ValidatorTest** : Tests de validation des entrées
- **DatabaseTest** : Tests de connexion DB

### Structure des tests

```
tests/
├── FunctionsTest.php      # Fonctions utilitaires
├── ValidatorTest.php      # Validation des entrées
└── DatabaseTest.php       # Connexion base de données
```

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez consulter [CONTRIBUTING.md](CONTRIBUTING.md) pour :
- Standards de code (PSR-12)
- Processus de Pull Request
- Guidelines de documentation
- Convention des commits

### Quick Start pour contribuer

1. Fork le repository
2. Créer une branche : `git checkout -b feature/ma-fonctionnalite`
3. Commit : `git commit -m "feat: ajout de ma fonctionnalité"`
4. Push : `git push origin feature/ma-fonctionnalite`
5. Ouvrir une Pull Request

---

## 🔒 Sécurité

**Important** : Cette application contient des vulnérabilités **intentionnelles** dans `/src/vuln/`.

Pour signaler une vulnérabilité **non intentionnelle**, consultez [SECURITY.md](SECURITY.md).

### Configuration sécurisée

```bash
# 1. Copier le template .env
cp .env.example .env

# 2. Modifier les credentials
nano .env

# 3. Ne JAMAIS commiter .env
# (déjà dans .gitignore)
```

---

## 📊 Base de données

### Import rapide

```bash
# Schéma de base
mysql -u root -p as_olympique_db < database/as_olympique_db.sql

# Données de test
mysql -u root -p as_olympique_db < database/seeds.sql
```

### Schéma

Le schéma comprend 9 tables :
- `membres` - Utilisateurs
- `activites` - Activités sportives
- `inscriptions` - Inscriptions aux activités
- `commentaires` - Commentaires (pour XSS)
- `fichiers` - Fichiers uploadés
- `reservations` - Réservations d'équipements
- `resultats` - Résultats sportifs
- `login_attempts` - Tentatives de connexion (anti-bruteforce)
- `access_logs` - Logs d'audit

Voir [ARCHITECTURE.md](ARCHITECTURE.md) pour le diagramme ER complet.

---

## 📝 Changelog

Toutes les modifications notables sont documentées dans [CHANGELOG.md](CHANGELOG.md).

---

## 📄 Licence

Ce projet est fourni "tel quel" à des fins éducatives uniquement.

**⚠️ ATTENTION** : Ne JAMAIS déployer en production.

---

## 🙏 Remerciements

- **OWASP** pour la documentation des vulnérabilités
- **BTS SIO SLAM** pour le cadre pédagogique
- Tous les contributeurs du projet

---

## 📧 Contact

Pour toute question :
- Créer une [Issue GitHub](../../issues)
- Consulter la [Documentation](ARCHITECTURE.md)
- Voir le [Guide de contribution](CONTRIBUTING.md)

---

*Dernière mise à jour : Janvier 2026*
