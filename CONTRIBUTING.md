# Contributing to AS Olympique

Thank you for your interest in contributing to the AS Olympique cybersecurity training project! This document provides guidelines for contributing to the project.

## 🎓 Project Purpose

AS Olympique is an **educational cybersecurity training application** designed to teach students about web security vulnerabilities and how to prevent them. The project intentionally contains vulnerable code for educational purposes.

## 📋 Table of Contents

- [Types of Contributions](#types-of-contributions)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Educational Guidelines](#educational-guidelines)

## Types of Contributions

We welcome the following types of contributions:

### ✅ Encouraged Contributions

1. **Security Improvements** (Secure versions only)
   - Enhancements to `src/secure/` implementations
   - Additional security best practices
   - Updated security headers or configurations

2. **Educational Enhancements**
   - Better explanatory comments
   - Additional vulnerability examples
   - OWASP references and documentation
   - Clearer educational content

3. **Documentation**
   - Installation guides
   - Tutorial improvements
   - Code comments and PHPDoc
   - Architecture documentation

4. **Testing**
   - Unit tests for secure implementations
   - Integration tests
   - Security testing examples

5. **Infrastructure**
   - Docker improvements
   - CI/CD enhancements
   - Development tools

### ⚠️ Special Consideration Required

1. **Vulnerable Code Modifications**
   - Changes to `src/vuln/` files must maintain educational value
   - Discuss with maintainers before modifying intentional vulnerabilities
   - Ensure changes align with OWASP curriculum

2. **Database Schema Changes**
   - Must maintain backward compatibility
   - Require thorough testing
   - Documentation updates required

### ❌ Not Accepted

1. "Fixing" intentional vulnerabilities in `src/vuln/` files
2. Removing educational content or warnings
3. Making the vulnerable versions "secure"
4. Breaking changes without discussion

## Getting Started

### Prerequisites

- PHP 7.4 or higher (PHP 8+ recommended)
- MySQL/MariaDB
- Git
- Composer
- Basic understanding of web security concepts

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/As-olympique.git
   cd As-olympique
   ```

3. Add upstream remote:
   ```bash
   git remote add upstream https://github.com/maxlo245/As-olympique.git
   ```

## Development Setup

### Using Docker (Recommended)

```bash
# Start the environment
docker-compose up -d

# Access the application
open http://localhost:8888

# Access phpMyAdmin
open http://localhost:8081
```

### Manual Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Update .env with your database credentials

# Import database
mysql -u root -p as_olympique_db < database/as_olympique_db.sql

# Start your local server (MAMP, XAMPP, etc.)
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit tests/Unit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

## Coding Standards

### PHP Standards

We follow **PSR-12** coding standards:

```bash
# Check code style
composer cs-check

# Fix code style automatically
composer cs-fix
```

### Code Style Guidelines

1. **Indentation**: 4 spaces (no tabs)
2. **Line Length**: Max 120 characters
3. **Naming Conventions**:
   - Classes: `PascalCase`
   - Methods/Functions: `camelCase`
   - Constants: `UPPER_SNAKE_CASE`
   - Variables: `snake_case` or `camelCase`

4. **File Structure**:
   ```php
   <?php
   /**
    * File description
    * 
    * @author Your Name
    * @license MIT
    */
   
   declare(strict_types=1);
   
   namespace AsOlympique\Core;
   
   // Code here
   ```

### Documentation Standards

All functions and classes must have PHPDoc comments:

```php
/**
 * Validates and sanitizes user input
 *
 * @param string $input The raw user input
 * @param string $type The type of validation to apply
 * @return string The sanitized input
 * @throws InvalidArgumentException If validation fails
 */
function sanitizeInput(string $input, string $type): string
{
    // Implementation
}
```

### Security Documentation

For secure implementations, add security annotations:

```php
// [SECURE] Using prepared statements prevents SQL injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
```

For vulnerable code, add vulnerability warnings:

```php
// [VULNERABLE] Direct concatenation enables SQL injection
// OWASP: A03:2021 – Injection
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

## Pull Request Process

### Before Submitting

1. **Update your fork**:
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

2. **Run tests**:
   ```bash
   composer test
   composer cs-check
   ```

3. **Update documentation** if needed

4. **Add/update tests** for new features

### Creating a Pull Request

1. Create a descriptive branch name:
   ```bash
   git checkout -b feature/add-rate-limiting
   # or
   git checkout -b fix/xss-in-secure-version
   # or
   git checkout -b docs/improve-installation-guide
   ```

2. Make your changes with clear commit messages:
   ```bash
   git commit -m "Add rate limiting to secure login implementation"
   ```

3. Push to your fork:
   ```bash
   git push origin feature/add-rate-limiting
   ```

4. Open a Pull Request on GitHub

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix (secure version)
- [ ] New feature (secure version)
- [ ] Educational enhancement
- [ ] Documentation update
- [ ] Infrastructure improvement

## Educational Impact
How does this enhance the learning experience?

## Testing
- [ ] Tests added/updated
- [ ] Manual testing completed
- [ ] Code style checked

## Checklist
- [ ] Code follows PSR-12 standards
- [ ] Documentation updated
- [ ] No breaking changes (or discussed with maintainers)
- [ ] Vulnerable code remains educational
```

### Review Process

1. Maintainers will review within 1 week
2. Address feedback with additional commits
3. Once approved, maintainers will merge
4. Credit will be given in release notes

## Educational Guidelines

### Maintaining Educational Value

When contributing, keep in mind:

1. **Clarity**: Code should be easy to understand for students
2. **Comments**: Explain WHY, not just WHAT
3. **Examples**: Include practical examples
4. **References**: Link to OWASP documentation
5. **Comparisons**: Show vulnerable vs. secure side-by-side

### Vulnerable Code Guidelines

If modifying `src/vuln/` files:

1. Maintain the educational vulnerability
2. Add clear warnings
3. Include exploitation examples
4. Reference the corresponding secure version
5. Add OWASP classification

Example:
```php
/**
 * VULNERABLE: SQL Injection Example
 * 
 * [VULNERABILITY] This code is intentionally vulnerable for educational purposes
 * OWASP: A03:2021 – Injection
 * 
 * Attack Vector: User can inject SQL through the 'id' parameter
 * Example: ?id=1 OR 1=1
 * 
 * See: src/secure/connexion_secure.php for the secure implementation
 */
```

### Secure Code Guidelines

When enhancing `src/secure/` files:

1. Follow current security best practices
2. Add comments explaining security measures
3. Reference CVEs or OWASP guidelines
4. Show defense-in-depth approach
5. Keep updated with latest PHP security features

## Code of Conduct

### Our Standards

- Be respectful and inclusive
- Focus on what is best for students
- Accept constructive criticism gracefully
- Show empathy toward other contributors

### Unacceptable Behavior

- Harassment or discrimination
- Trolling or insulting comments
- Publishing others' private information
- Malicious contributions

## Questions?

- Open a GitHub Discussion for questions
- Check existing issues and PRs
- Review documentation first

## License

By contributing, you agree that your contributions will be licensed under the same license as the project (MIT License).

---

Thank you for helping make cybersecurity education better! 🔒

*Last updated: January 2026*
