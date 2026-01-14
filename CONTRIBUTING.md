# Guide de contribution

Merci de votre intérêt pour contribuer à AS Olympique ! Ce guide vous aidera à contribuer efficacement au projet.

## 📋 Table des matières

- [Code de conduite](#code-de-conduite)
- [Comment contribuer](#comment-contribuer)
- [Standards de code](#standards-de-code)
- [Tests](#tests)
- [Documentation](#documentation)
- [Process de Pull Request](#process-de-pull-request)

## 🤝 Code de conduite

Ce projet suit un code de conduite simple :
- Soyez respectueux et professionnel
- Accueillez les nouvelles idées et les nouveaux contributeurs
- Concentrez-vous sur ce qui est meilleur pour la communauté éducative
- Rappelez-vous : c'est un projet **éducatif** sur la sécurité

## 💡 Comment contribuer

### Types de contributions

Nous accueillons plusieurs types de contributions :

1. **Corrections de bugs** (non-sécuritaires)
2. **Nouvelles vulnérabilités éducatives** avec contre-mesures
3. **Améliorations de documentation**
4. **Optimisations de performance**
5. **Tests unitaires**
6. **Traductions**

### Avant de commencer

1. **Vérifiez les issues existantes** pour éviter les doublons
2. **Créez une issue** pour discuter des changements majeurs
3. **Forkez le repository** pour travailler sur votre contribution
4. **Créez une branche** descriptive : `feature/nouvelle-vuln-xss` ou `fix/typo-readme`

## 📝 Standards de code

### PSR-12 Coding Standard

Nous suivons strictement [PSR-12](https://www.php-fig.org/psr/psr-12/) :

```php
<?php

namespace AsOlympique;

/**
 * Class description
 *
 * Detailed description if needed
 */
class ExampleClass
{
    /**
     * Method description
     *
     * @param string $param Parameter description
     * @return bool Result description
     */
    public function exampleMethod(string $param): bool
    {
        // 4 spaces indentation
        if ($condition) {
            return true;
        }
        
        return false;
    }
}
```

### Conventions de nommage

- **Classes** : `PascalCase` (ex: `CsrfProtection`)
- **Méthodes** : `camelCase` (ex: `validateEmail`)
- **Variables** : `camelCase` (ex: `$userName`)
- **Constantes** : `UPPER_SNAKE_CASE` (ex: `MAX_FILE_SIZE`)
- **Fichiers** : `PascalCase.php` pour classes, `snake_case.php` pour scripts

### Documentation du code

#### PHPDoc obligatoire

Toutes les fonctions et méthodes doivent avoir un PHPDoc :

```php
/**
 * Brief description of what the function does
 *
 * Longer description if needed explaining the logic,
 * algorithms, or important considerations.
 *
 * @param string $input Input description
 * @param int $limit Maximum value
 * @return string|false Result or false on failure
 * @throws InvalidArgumentException If input is invalid
 */
function processData(string $input, int $limit = 100)
{
    // Implementation
}
```

#### Commentaires éducatifs

Pour les fichiers vulnérables et sécurisés, ajoutez des commentaires explicatifs :

```php
// [VULNERABLE] Cette ligne est vulnérable à l'injection SQL
$sql = "SELECT * FROM users WHERE login = '$login'";

// [SECURE] Utilisation de requêtes préparées pour prévenir l'injection SQL
$stmt = $pdo->prepare("SELECT * FROM users WHERE login = :login");
$stmt->execute(['login' => $login]);
```

### Structure des fichiers

#### Versions vulnérables (`src/vuln/`)

```php
<?php
/**
 * PARTIE X - [TITRE DE LA VULNÉRABILITÉ]
 * 
 * [VULNERABLE] VERSION VULNÉRABLE
 * 
 * Cette version contient intentionnellement les vulnérabilités suivantes :
 * - [Liste des vulnérabilités]
 * 
 * ⚠️ NE JAMAIS UTILISER CE CODE EN PRODUCTION
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

// Code vulnérable avec commentaires explicatifs
```

#### Versions sécurisées (`src/secure/`)

```php
<?php
/**
 * PARTIE X - [TITRE] SÉCURISÉ
 * 
 * [SECURE] VERSION SÉCURISÉE
 * 
 * Contre-mesures implémentées :
 * - [Liste des contre-mesures]
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

// Code sécurisé avec commentaires explicatifs des protections
```

## 🧪 Tests

### Tests obligatoires

Toute nouvelle fonctionnalité doit inclure des tests :

```php
<?php

namespace AsOlympique\Tests;

use PHPUnit\Framework\TestCase;
use AsOlympique\Validator;

class ValidatorTest extends TestCase
{
    public function testValidateEmail(): void
    {
        $this->assertEquals(
            'test@example.com',
            Validator::validateEmail('test@example.com')
        );
        
        $this->assertFalse(
            Validator::validateEmail('invalid-email')
        );
    }
}
```

### Exécution des tests

```bash
# Installer PHPUnit
composer install

# Lancer les tests
composer test

# Avec couverture de code
composer test-coverage
```

### Couverture de code

Visez au minimum 80% de couverture pour le nouveau code.

## 📚 Documentation

### Documentation à mettre à jour

Lors de l'ajout de fonctionnalités :

1. **README.md** : Mise à jour de la table des matières si nécessaire
2. **ARCHITECTURE.md** : Documentation de l'architecture si changement structurel
3. **CHANGELOG.md** : Ajout de votre contribution
4. **Commentaires inline** : Documentation du code

### Format Markdown

- Utilisez des titres clairs et hiérarchisés
- Incluez des exemples de code
- Ajoutez des diagrammes Mermaid si pertinent
- Testez le rendu sur GitHub

## 🔄 Process de Pull Request

### 1. Préparation

```bash
# Créer une branche
git checkout -b feature/ma-contribution

# Faire vos modifications
git add .
git commit -m "feat: description claire de la fonctionnalité"

# Pousser vers votre fork
git push origin feature/ma-contribution
```

### 2. Conventions de commit

Nous utilisons les [Conventional Commits](https://www.conventionalcommits.org/) :

- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation uniquement
- `style:` Formatage, pas de changement de code
- `refactor:` Refactorisation sans changement de fonctionnalité
- `test:` Ajout ou modification de tests
- `chore:` Maintenance (dépendances, config)

Exemples :
```
feat: add XSS stored vulnerability example
fix: correct SQL injection in secure version
docs: update installation instructions for MAMP
test: add unit tests for Validator class
```

### 3. Checklist avant PR

- [ ] Code suit PSR-12
- [ ] Tous les tests passent (`composer test`)
- [ ] Documentation mise à jour
- [ ] Pas de credentials hardcodés
- [ ] Commentaires éducatifs ajoutés
- [ ] CHANGELOG.md mis à jour
- [ ] Commit messages clairs

### 4. Description de la PR

```markdown
## Description
[Description claire de ce que fait la PR]

## Type de changement
- [ ] Nouvelle vulnérabilité éducative
- [ ] Correction de bug
- [ ] Amélioration de performance
- [ ] Documentation
- [ ] Tests

## Comment tester
1. [Étapes pour tester la fonctionnalité]
2. [...]

## Screenshots (si applicable)
[Ajoutez des captures d'écran]

## Checklist
- [ ] Tests ajoutés/mis à jour
- [ ] Documentation mise à jour
- [ ] Code suit PSR-12
```

### 5. Review et merge

- Un mainteneur reviewera votre PR
- Répondez aux commentaires et faites les ajustements nécessaires
- Une fois approuvée, votre PR sera mergée

## 🎓 Ajouter une nouvelle vulnérabilité

### Template de vulnérabilité

1. Créer `src/vuln/ma_vuln.php`
2. Créer `src/secure/ma_secure.php`
3. Documenter dans `README.md`
4. Ajouter dans `ARCHITECTURE.md`

### Structure recommandée

```php
// Version vulnérable
<?php
/**
 * PARTIE X - [NOM DE LA VULNÉRABILITÉ]
 * 
 * [VULNERABLE] Cette version démontre : [description]
 * 
 * Exploitation possible :
 * - [Comment exploiter]
 * 
 * Impact :
 * - [Conséquences]
 */

// Code avec vulnérabilité claire
```

```php
// Version sécurisée
<?php
/**
 * PARTIE X - [NOM] SÉCURISÉ
 * 
 * [SECURE] Contre-mesures :
 * - [Liste des protections]
 * 
 * Références OWASP :
 * - [Liens pertinents]
 */

// Code sécurisé avec explications
```

## 🐛 Signaler un bug

### Bugs de sécurité réels

Si vous trouvez une vulnérabilité **non intentionnelle** :
1. **NE PAS** créer une issue publique
2. Contactez les mainteneurs en privé
3. Voir [SECURITY.md](SECURITY.md) pour plus de détails

### Bugs fonctionnels

Créez une issue avec :
- Description claire du problème
- Étapes pour reproduire
- Comportement attendu vs réel
- Environnement (OS, PHP version, serveur)
- Screenshots si pertinent

## 📬 Questions

Si vous avez des questions :
- Créez une [Discussion GitHub](../../discussions)
- Consultez la documentation existante
- Vérifiez les issues fermées

## 🙏 Remerciements

Merci de contribuer à l'éducation en cybersécurité ! Votre contribution aide les étudiants à apprendre les bonnes pratiques de développement sécurisé.

---

*Dernière mise à jour : Janvier 2026*
