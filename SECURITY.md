# Security Policy

## Overview

AS Olympique is an educational cybersecurity training application that **intentionally contains vulnerabilities** for learning purposes. This document outlines our security policy regarding the project.

## ⚠️ Important Notice

**This application is designed for educational purposes only and should NEVER be deployed in a production environment.**

The application contains two types of code:
- **Vulnerable versions** (`src/vuln/`) - Intentionally insecure for training
- **Secure versions** (`src/secure/`) - Demonstrates security best practices

## Supported Versions

| Version | Support Status |
| ------- | -------------- |
| 1.x     | Educational Use Only |

## Educational Vulnerabilities (By Design)

The following vulnerabilities are **intentionally included** for educational purposes:

### Vulnerable Versions (`src/vuln/`)

1. **SQL Injection** - `connexion_vuln.php`
   - Direct concatenation of user input in SQL queries
   - No prepared statements

2. **Cross-Site Scripting (XSS)** - `bonjour_vuln.php`, `commentaire_vuln.php`
   - Reflected XSS via GET parameters
   - Stored XSS in comments

3. **Insecure File Upload** - `upload_vuln.php`
   - No file type validation
   - No MIME type checking
   - Executable files allowed

4. **Session Hijacking** - `auth_vuln.php`
   - Weak session configuration
   - No session regeneration

5. **CSRF (Cross-Site Request Forgery)** - `del_vuln.php`
   - No CSRF token validation

6. **XXE (XML External Entity)** - `parse_vuln_xml.php`
   - External entity processing enabled

**These vulnerabilities are documented and are part of the training curriculum.**

## Reporting Real Security Issues

If you discover a **real security issue** (not one of the intentional educational vulnerabilities) in:
- The secure implementations (`src/secure/`)
- The infrastructure code (`src/init.php`, `src/config.php`)
- The core functionality or configuration
- The Docker setup or deployment scripts

Please report it responsibly:

### Reporting Process

1. **DO NOT** create a public GitHub issue
2. Send an email to: [security contact email - to be added]
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if available)
   - Your contact information

### What to Report

Please report issues such as:
- Security flaws in the "secure" implementations
- Infrastructure vulnerabilities (Docker, configuration)
- Authentication/authorization bypasses in secure code
- Information disclosure in error handling
- Dependency vulnerabilities

### What NOT to Report

Do NOT report:
- Vulnerabilities in `src/vuln/` files (these are intentional)
- Issues that are clearly documented as educational
- Standard educational attack vectors covered in the curriculum

## Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 1 week
- **Fix Timeline**: Depends on severity
  - Critical: 7 days
  - High: 14 days
  - Medium: 30 days
  - Low: 90 days

## Security Best Practices for Users

### For Instructors/Students

1. **Never deploy this application on a public server**
2. **Use only in isolated local environments** (MAMP, XAMPP, Docker)
3. **Do not use real passwords or sensitive data**
4. **Ensure firewall protection** when running locally
5. **Review the secure implementations** to understand proper security measures

### Environment Setup

- Run in an isolated virtual machine or container
- Use the provided Docker setup for isolation
- Keep the application on a private network
- Do not expose ports to the internet

## Secure Implementation Guidelines

The `src/secure/` directory demonstrates security best practices:

### Implemented Security Measures

✅ **SQL Injection Prevention**
- Prepared statements with parameter binding
- Input validation

✅ **XSS Prevention**
- Output encoding with `htmlspecialchars()`
- Content Security Policy headers

✅ **CSRF Protection**
- Token generation and validation
- SameSite cookie attribute

✅ **Secure File Upload**
- MIME type validation
- File extension whitelist
- Secure file naming
- Storage outside webroot

✅ **Session Security**
- Secure session configuration
- Session regeneration
- HTTPOnly and Secure flags

✅ **XXE Prevention**
- Disabled external entity loading
- Secure XML parser configuration

## Dependencies

This project uses minimal dependencies. When dependencies are added:

1. Dependencies are managed via Composer
2. Regular security audits using `composer audit`
3. Automated dependency scanning in CI/CD
4. Only stable, well-maintained packages

## Security Headers

The secure implementations include:
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- Referrer-Policy
- Permissions-Policy

## Disclosure Policy

When a real security issue is fixed:
1. Credit will be given to the reporter (if desired)
2. A security advisory will be published
3. The fix will be released promptly
4. Documentation will be updated

## Contact

For security concerns regarding the secure implementations or infrastructure:
- Email: [To be added]
- Project maintainer: Florence PEYRATAUD

---

**Remember: This is an educational project. The vulnerabilities in `src/vuln/` are intentional and documented.**

*Last updated: January 2026*
