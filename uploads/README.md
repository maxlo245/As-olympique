# Dossier uploads

Ce dossier est destiné au stockage des fichiers uploadés par les utilisateurs.

## [ATTENTION] Sécurité

En production, ce dossier devrait être :
1. **Placé hors du webroot** (pas accessible via URL)
2. **Protégé contre l'exécution PHP** (voir .htaccess ci-dessous)

## Configuration Apache (.htaccess)

Créer un fichier `.htaccess` dans ce dossier avec le contenu suivant :

```apache
# Désactiver l'exécution PHP
php_flag engine off

# Bloquer l'accès aux fichiers PHP
<FilesMatch "\.php$">
    Require all denied
</FilesMatch>

# Forcer le téléchargement (pas d'exécution)
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phps)$">
    ForceType application/octet-stream
    Header set Content-Disposition attachment
</FilesMatch>

# Désactiver le listing
Options -Indexes
```

## Permissions recommandées

```bash
# Linux
chmod 755 uploads/
chown www-data:www-data uploads/
```

## Fichiers de test

Pour tester l'upload, vous pouvez utiliser des fichiers images (jpg, png, gif) ou PDF.
