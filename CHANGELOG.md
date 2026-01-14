# Changelog

All notable changes to AS Olympique will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added - Comprehensive Optimization (January 2026)

#### 🚀 Performance Optimizations
- Added comprehensive database indexes (inscriptions, commentaires, fichiers tables)
- Created `.htaccess` with gzip compression and caching configuration
- Created `.user.ini` with OPcache recommendations for improved PHP performance
- Optimized `src/init.php` with better PDO configuration and connection handling
- Added HTTP security headers for better performance and security

#### 💎 Code Quality Improvements
- **PSR-12 Compliance**: Refactored all core PHP files to follow PSR-12 standards
- **Type Hints**: Added PHP 7.4+ type hints to all functions in `functions.php`
- **PHPDoc**: Added comprehensive documentation blocks for all functions and classes
- **Helper Functions**: Added utility functions:
  - `success_message()` - Generate styled success alerts
  - `error_message()` - Generate styled error alerts
  - `warning_message()` - Generate styled warning alerts
  - `redirect()` - Safe HTTP redirect with validation
  - `is_logged_in()` - Check authentication status
  - `require_auth()` - Force authentication with redirect
  - `get_user_role()` - Get current user role
  - `get_user_id()` - Get current user ID
  - `get_user_name()` - Get current user name
  - `log_security_event()` - Log security-related events
  - `format_bytes()` - Human-readable file size formatting
  - `truncate()` - String truncation with ellipsis
  - `random_string()` - Cryptographically secure random string generation

#### 🏗️ Architecture & Structure
- **PSR-4 Autoloading**: Created `composer.json` with PSR-4 namespace configuration
- **Class-Based Architecture**: Created comprehensive PSR-4 classes:
  - `AsOlympique\Database` - Singleton database connection manager
  - `AsOlympique\Session` - Secure session management wrapper
  - `AsOlympique\CsrfProtection` - CSRF token generation and validation
  - `AsOlympique\Validator` - Comprehensive input validation utilities
  - `AsOlympique\FileUpload` - Secure file upload handler with magic byte verification
  - `AsOlympique\Logger` - PSR-3 compatible logging system
  - `AsOlympique\ErrorHandler` - Centralized error and exception handling

#### 🔒 Security Enhancements
- **Environment Variables**: 
  - Created `.env.example` template for configuration
  - Created `src/config/env.php` for loading environment variables
  - Refactored `src/config.php` to use `env()` function with fallbacks
- **Security Headers**: Created `src/config/security_headers.php` with:
  - X-Frame-Options (clickjacking protection)
  - X-Content-Type-Options (MIME sniffing protection)
  - X-XSS-Protection (XSS protection for older browsers)
  - Strict-Transport-Security (HTTPS enforcement)
  - Content-Security-Policy (CSP)
  - Referrer-Policy
  - Permissions-Policy
- **Enhanced Validation**: Comprehensive validation methods in `Validator` class:
  - Email validation with sanitization
  - Phone number validation (French format)
  - Date validation with format verification
  - String sanitization with HTML filtering options
  - Integer and float validation with range checking
  - URL validation with HTTPS requirement option
  - Username validation with pattern matching
  - Password strength validation
  - Filename sanitization
- **File Upload Security**: 
  - Magic byte verification (file signature checking)
  - Real MIME type detection (not just from HTTP headers)
  - Whitelist-based extension validation
  - Automatic secure filename generation
  - File size limit enforcement

#### 📁 Project Organization
- Created standardized directory structure:
  - `src/classes/` - PSR-4 autoloaded classes
  - `src/config/` - Configuration files
  - `logs/` - Application and security logs
  - `tmp/` - Temporary files
  - `tests/` - Unit tests (structure ready)
- Added `.gitignore` with comprehensive exclusions
- Added `.editorconfig` for consistent code formatting across editors

#### 📚 Documentation
- Created `ARCHITECTURE.md` with:
  - Complete project structure documentation
  - Mermaid ER diagram for database schema
  - Sequence diagrams for authentication and upload flows
  - Security principles explanation
  - OWASP vulnerability mapping
  - Performance metrics
- Created `CONTRIBUTING.md` with:
  - Contribution guidelines
  - PSR-12 coding standards
  - PHPDoc requirements
  - Testing requirements
  - Pull request process
  - Commit message conventions
- Created `SECURITY.md` with:
  - Security policy
  - Intentional vs unintentional vulnerabilities
  - Responsible disclosure process
  - Security configuration guidelines
- Created `CHANGELOG.md` (this file)

#### 🔧 Configuration
- **Apache Configuration**: Enhanced `.htaccess` with:
  - Security headers
  - Gzip compression for multiple MIME types
  - Browser caching configuration
  - MIME type definitions
  - Directory listing disabled
  - Protection for sensitive files (.env, composer.json, etc.)
  - PHP security settings
  - Upload directory PHP execution prevention
- **PHP Configuration**: Created `.user.ini` with:
  - OPcache settings for performance
  - Realpath cache configuration
  - Session security settings
  - Error handling configuration
  - File upload limits
  - Output buffering and compression

### Changed

- **src/config.php**: Refactored to use environment variables with fallback defaults
- **src/init.php**: Enhanced with:
  - PHP 7.3+ compatible session configuration
  - Improved error handling and logging
  - Development/production mode support
  - Session fixation protection (auto-regenerate on first visit)
  - Better PDO configuration with timeout and persistent connection options
- **src/functions.php**: Complete refactor with type hints and comprehensive PHPDoc
- **database/as_olympique_db.sql**: Added performance indexes:
  - Composite indexes on `inscriptions` (membre_statut, activite_statut, date_inscription)
  - Composite indexes on `commentaires` (membre_modere, modere_date)
  - Composite indexes on `fichiers` (membre_date, date_upload)

### Fixed

- Database schema indexes optimized for common query patterns
- Session security improved with proper cookie parameters
- File upload validation strengthened with multiple security layers

### Security

- ⚠️ **Note**: Intentional vulnerabilities in `/src/vuln/` directory remain for educational purposes
- All enhancements apply to secure versions and infrastructure
- No hardcoded credentials in version control (moved to environment variables)

## [1.0.0] - 2026-01-XX (Initial Release)

### Added
- Initial project structure with vulnerable and secure versions
- OWASP Top 10 vulnerability demonstrations:
  - A01: Broken Access Control (`auth_vuln.php`)
  - A03: Injection - SQL (`connexion_vuln.php`)
  - A03: Injection - XSS (`bonjour_vuln.php`, `commentaire_vuln.php`)
  - A04: Insecure Design - CSRF (`del_vuln.php`)
  - A08: Software Data Integrity - File Upload (`upload_vuln.php`)
  - A05: XXE - XML External Entity (`parse_vuln_xml.php`)
- Secure counterpart implementations in `/src/secure/`
- Basic database schema with tables for members, activities, inscriptions, comments, files
- Basic session and authentication system
- PHPMyAdmin connectivity check
- Modern brutalist UI design with light/dark theme support
- Installation guide (INSTALL.md)
- Project documentation (README.md)

---

## Legend

- **Added**: New features or files
- **Changed**: Changes to existing functionality
- **Deprecated**: Features that will be removed in future versions
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security improvements or vulnerability fixes

---

*For detailed information about each vulnerability and its countermeasure, see the [README.md](README.md) and [ARCHITECTURE.md](ARCHITECTURE.md)*
