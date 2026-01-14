# Security Policy

## 🔒 Contexte important

AS Olympique est un **projet éducatif** contenant **intentionnellement des vulnérabilités** à des fins pédagogiques. Le dossier `/src/vuln/` contient des exemples de code vulnérable pour démontrer les risques de sécurité OWASP.

## ⚠️ Avertissement

> **Ce projet ne doit JAMAIS être déployé en production.**
> 
> Il est conçu uniquement pour :
> - L'enseignement de la cybersécurité
> - L'apprentissage des vulnérabilités web
> - La pratique des contre-mesures
> - L'utilisation en environnement local contrôlé

## 🎯 Vulnérabilités intentionnelles

Les vulnérabilités suivantes sont **intentionnelles** et **documentées** :

### Fichiers vulnérables (`/src/vuln/`)

| Fichier | Vulnérabilité | Objectif pédagogique |
|---------|---------------|----------------------|
| `upload_vuln.php` | Upload de fichiers malveillants | Démontrer les risques d'upload non sécurisé |
| `bonjour_vuln.php` | XSS reflétée | Montrer l'importance de l'échappement des sorties |
| `connexion_vuln.php` | Injection SQL | Illustrer les dangers des requêtes non préparées |
| `commentaire_vuln.php` | XSS stockée | Expliquer la persistance des attaques XSS |
| `auth_vuln.php` | Session hijacking | Démontrer la fixation de session |
| `del_vuln.php` | CSRF | Montrer l'absence de protection CSRF |
| `parse_vuln_xml.php` | XXE (XML External Entity) | Illustrer les attaques XML |

**Ces vulnérabilités ne doivent PAS être signalées comme des bugs.**

## 🛡️ Vulnérabilités non intentionnelles

Si vous découvrez une vulnérabilité **réelle** (non pédagogique) dans :
- Les versions sécurisées (`/src/secure/`)
- Les classes utilitaires (`/src/classes/`)
- La configuration de base
- Les fichiers de configuration

Merci de suivre notre processus de divulgation responsable.

## 📢 Comment signaler une vulnérabilité réelle

### 1. NE PAS créer d'issue publique

Pour protéger les utilisateurs, **ne créez pas d'issue publique** sur GitHub pour les vulnérabilités réelles.

### 2. Contact privé

Contactez les mainteneurs via :
- **Email** : [Créez une issue privée ou contactez via GitHub]
- **GitHub Security Advisory** : Utilisez l'onglet "Security" du repository

### 3. Informations à fournir

Incluez dans votre rapport :

```markdown
## Description de la vulnérabilité
[Description claire et concise]

## Type de vulnérabilité
- [ ] Injection SQL (non intentionnelle)
- [ ] XSS (non intentionnelle)
- [ ] CSRF (dans version sécurisée)
- [ ] Upload malveillant (dans version sécurisée)
- [ ] Autre : [préciser]

## Localisation
- **Fichier** : /src/secure/[fichier].php
- **Ligne** : [numéro de ligne si applicable]

## Impact
- **Sévérité** : Critique / Haute / Moyenne / Basse
- **Scope** : [Qui est affecté]

## Preuve de concept (PoC)
[Étapes pour reproduire la vulnérabilité]

1. [Étape 1]
2. [Étape 2]
3. [Résultat observé]

## Environnement
- PHP version : [ex: 8.1]
- Serveur : [ex: Apache 2.4]
- Système : [ex: Ubuntu 22.04]

## Contre-mesure suggérée (optionnel)
[Vos suggestions pour corriger la vulnérabilité]
```

### 4. Ce qu'il se passe ensuite

1. **Confirmation** : Nous accusons réception sous 48h
2. **Analyse** : Nous évaluons la vulnérabilité (1-7 jours)
3. **Correction** : Nous développons un patch si nécessaire
4. **Crédit** : Nous vous créditerons dans le CHANGELOG (si vous le souhaitez)
5. **Publication** : Nous publions le correctif et un advisory

## 🏆 Hall of Fame

Nous remercions les personnes suivantes pour leurs contributions à la sécurité :

*[Liste à venir]*

## ✅ Bonnes pratiques pour les contributeurs

### Si vous modifiez les versions sécurisées

- Testez avec des outils comme :
  - [OWASP ZAP](https://www.zaproxy.org/)
  - [Burp Suite](https://portswigger.net/burp)
  - [SQLMap](https://sqlmap.org/) (pour tester les protections SQL)
- Vérifiez que les contre-mesures sont toujours effectives
- Documentez tout changement de sécurité

### Si vous ajoutez de nouvelles vulnérabilités

- Créez **toujours** une version vulnérable ET sécurisée
- Documentez clairement la vulnérabilité et sa contre-mesure
- Ajoutez des commentaires pédagogiques dans le code
- Référencez les ressources OWASP pertinentes

## 📚 Ressources de sécurité

### Documentation OWASP

- [OWASP Top 10](https://owasp.org/Top10/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)

### Bonnes pratiques PHP

- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [Paragon Initiative - PHP Security](https://paragonie.com/blog/2015/08/gentle-introduction-application-security)

## 🔐 Configuration sécurisée

### Pour utiliser AS Olympique de manière sûre

1. **Environnement isolé** : Utilisez une VM ou Docker
2. **Réseau local uniquement** : Ne jamais exposer sur Internet
3. **Base de données dédiée** : Ne pas utiliser avec d'autres applications
4. **Utilisateur MySQL dédié** : Avec privilèges limités à cette DB
5. **Journalisation activée** : Pour suivre les tentatives d'exploitation

### Exemple de configuration MySQL sécurisée

```sql
-- Créer un utilisateur avec privilèges minimaux
CREATE USER 'as_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';

-- Donner uniquement les privilèges nécessaires
GRANT SELECT, INSERT, UPDATE, DELETE 
ON as_olympique_db.* 
TO 'as_user'@'localhost';

-- Ne PAS donner : DROP, CREATE, ALTER, FILE, PROCESS, etc.
FLUSH PRIVILEGES;
```

## 🚫 Interdictions absolues

**Ne jamais :**
- Déployer cette application sur un serveur public
- Utiliser des données réelles/sensibles dans la base de données
- Laisser accessible sur un réseau non sécurisé
- Utiliser pour autre chose que l'éducation
- Tester sur des systèmes tiers sans autorisation

## 📝 Versions supportées

| Version | Support | Notes |
|---------|---------|-------|
| main    | ✅ Oui  | Branche principale |
| develop | ⚠️ Partiel | Version de développement |
| < 1.0   | ❌ Non  | Versions obsolètes |

## 🆘 En cas d'incident

Si vous avez **accidentellement déployé** AS Olympique en production :

1. **Arrêter immédiatement** le serveur
2. **Changer tous les mots de passe** liés
3. **Auditer les logs** pour détecter des accès non autorisés
4. **Notifier** les parties concernées
5. **Contacter** un expert en sécurité si nécessaire

## 📧 Contact

Pour toute question de sécurité :
- **GitHub Security Advisory** (recommandé)
- **Issues privées** sur GitHub
- **Email** : [via le profil GitHub du mainteneur]

## 📄 Licence et responsabilité

Ce projet est fourni "tel quel" à des fins éducatives uniquement. Les auteurs ne sont pas responsables de toute utilisation malveillante ou déploiement inapproprié de ce code.

En utilisant AS Olympique, vous acceptez :
- De l'utiliser uniquement à des fins éducatives
- De ne jamais le déployer en production
- De respecter les lois locales sur la cybersécurité
- De ne pas utiliser les techniques apprises pour nuire

---

*Dernière mise à jour : Janvier 2026*

**⚠️ Rappelez-vous : La sécurité est l'affaire de tous. Utilisez ces connaissances de manière responsable et éthique.**
