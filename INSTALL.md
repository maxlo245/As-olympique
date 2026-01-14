# Guide d'installation - AS Olympique Saint-Rémy

## Configuration requise

- **MAMP** (Mac) ou **WAMP** (Windows) ou **XAMPP**
- PHP 7.4 ou supérieur
- MySQL 5.7 ou MariaDB 10.x

---

## Installation pas à pas

### Étape 1 : Copier le projet

1. Copiez le dossier `as_olympique` dans le répertoire web de MAMP :

   **Sur Mac (MAMP) :**
   ```
   /Applications/MAMP/htdocs/as_olympique
   ```

   **Sur Windows (WAMP) :**
   ```
   C:\wamp64\www\as_olympique
   ```

   **Sur Windows (XAMPP) :**
   ```
   C:\xampp\htdocs\as_olympique
   ```

### Étape 2 : Démarrer MAMP

1. Ouvrez **MAMP**
2. Cliquez sur **Start** (Démarrer les serveurs)
3. Vérifiez que les voyants Apache et MySQL sont au vert

### Étape 3 : Créer la base de données

1. Ouvrez **phpMyAdmin** :
   - Cliquez sur **Open WebStart page** dans MAMP
   - Cliquez sur **Tools > phpMyAdmin**
   - Ou accédez directement à : `http://localhost:8888/phpMyAdmin`

2. Créez la base de données :
   - Cliquez sur **Nouvelle base de données** (à gauche)
   - Nom : `as_olympique_db`
   - Interclassement : `utf8mb4_general_ci`
   - Cliquez sur **Créer**

3. Importez le script SQL :
   - Sélectionnez la base `as_olympique_db`
   - Cliquez sur l'onglet **Importer**
   - Cliquez sur **Choisir un fichier**
   - Sélectionnez : `as_olympique/database/as_olympique_db.sql`
   - Cliquez sur **Exécuter**

### Étape 4 : Configurer la connexion

Ouvrez le fichier `as_olympique/src/config.php` et vérifiez les paramètres :

```php
'db' => (object)[
    'host' => '127.0.0.1',
    'port' => '8889',           // Port MySQL MAMP (8889 par défaut)
    'dbname' => 'as_olympique_db',
    'user' => 'root',           // Utilisateur par défaut MAMP
    'pass' => 'root',           // Mot de passe par défaut MAMP
    'charset' => 'utf8mb4'
],
```

**Ports courants :**
| Logiciel | Port Apache | Port MySQL |
|----------|-------------|------------|
| MAMP     | 8888        | 8889       |
| WAMP     | 80          | 3306       |
| XAMPP    | 80          | 3306       |

Si vous utilisez WAMP ou XAMPP, changez le port en `3306`.

### Étape 5 : Accéder à l'application

Ouvrez votre navigateur et accédez à :

```
http://localhost:8888/as_olympique/src/
```

Pour WAMP/XAMPP (port 80) :
```
http://localhost/as_olympique/src/
```

---

## Comptes de test

| Login | Mot de passe | Rôle |
|-------|--------------|------|
| admin | admin123 | Administrateur |
| jean  | password123 | Membre |
| marie | marie2024 | Secrétaire |

> **Note :** Les mots de passe sont stockés en clair dans la version vulnérable. 
> La version sécurisée utilise `password_hash()`.

---

## Structure des URLs

| Page | URL |
|------|-----|
| Accueil | `/as_olympique/src/index.php` |
| Upload (vuln) | `/as_olympique/src/vuln/upload_vuln.php` |
| Upload (secure) | `/as_olympique/src/secure/upload_secure.php` |
| Connexion (vuln) | `/as_olympique/src/vuln/connexion_vuln.php` |
| Connexion (secure) | `/as_olympique/src/secure/connexion_secure.php` |

---

## Dépannage

### Erreur "Connection refused"

1. Vérifiez que MAMP est démarré
2. Vérifiez le port MySQL dans `config.php`
3. Testez la connexion dans phpMyAdmin

### Erreur "Unknown database"

La base de données n'existe pas :
1. Créez-la dans phpMyAdmin
2. Importez le fichier SQL

### Erreur "Access denied"

Mauvais identifiants :
1. Vérifiez user/pass dans `config.php`
2. Par défaut MAMP : `root` / `root`

### Page blanche

1. Activez l'affichage des erreurs PHP
2. Vérifiez les logs Apache dans MAMP

---

## Fichiers importants

```
as_olympique/
├── src/
│   ├── config.php      <- Configuration BDD (à modifier)
│   ├── init.php        <- Initialisation PDO + Session
│   ├── functions.php   <- Fonctions de sécurité
│   ├── index.php       <- Page d'accueil
│   ├── vuln/           <- Versions VULNÉRABLES
│   └── secure/         <- Versions SÉCURISÉES
├── database/
│   └── as_olympique_db.sql  <- Script SQL à importer
├── uploads/            <- Dossier pour les fichiers uploadés
└── INSTALL.md          <- Ce fichier
```

---

## Support

Ce TD a été réalisé dans le cadre du **BTS SIO SLAM - Bloc 3 Cybersécurité**.

**Auteur :** Florence PEYRATAUD

**Durée totale :** 9 heures

---

*Dernière mise à jour : Janvier 2026*
