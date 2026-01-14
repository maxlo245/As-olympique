<?php

/**
 * Application Initialization
 * 
 * This file:
 * - Loads autoloader and dependencies
 * - Initializes configuration from environment
 * - Establishes database connection with retry logic
 * - Configures secure session management
 * - Sets security headers
 * - Provides backward compatibility with legacy code
 * 
 * @package AsOlympique
 * @author Florence PEYRATAUD
 */

declare(strict_types=1);

// Performance: Start output buffering for better response compression
ob_start();

// Check if Composer autoloader is available
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$useNewStructure = file_exists($autoloadPath);

if ($useNewStructure) {
    // Load Composer autoloader (for new class structure)
    require_once $autoloadPath;
    
    // Load helper functions
    require_once __DIR__ . '/helpers.php';

    try {
        // Initialize configuration (supports both .env and legacy config.php)
        use AsOlympique\Core\Config;
        use AsOlympique\Core\Database;
        use AsOlympique\Core\Security;

        $config = Config::getInstance(__DIR__ . '/../.env');
        
        // Initialize security
        $security = new Security($config);
        
        // Set security headers (for secure versions)
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/secure/') !== false) {
            $security->setSecurityHeaders();
        }
        
        // Configure and start secure session
        $security->configureSecureSession();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize database connection with singleton pattern
        $db = Database::getInstance($config->getDatabase());
        $pdo = $db->getConnection();
        
        // Log successful initialization in debug mode
        if ($config->isDebug()) {
            error_log('[Init] Application initialized successfully (new structure)');
        }

    } catch (Exception $e) {
        // Handle initialization errors gracefully
        http_response_code(500);
        
        // In production, show generic error; in development, show details
        if (isset($config) && $config->isDebug()) {
            echo '<h1>Initialization Error</h1>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>Service Unavailable</h1>';
            echo '<p>The application is temporarily unavailable. Please try again later.</p>';
        }
        
        // Log error
        error_log('[Init] Initialization failed: ' . $e->getMessage());
        exit(1);
    }

    // Backward compatibility: Create legacy $config object for old code
    if (!isset($config) || !($config instanceof \AsOlympique\Core\Config)) {
        $legacyConfig = (object)[
            'db' => (object)[
                'host' => config('db.host', 'localhost'),
                'dbname' => config('db.dbname', 'as_olympique_db'),
                'user' => config('db.user', 'root'),
                'pass' => config('db.pass', 'root'),
                'charset' => config('db.charset', 'utf8mb4'),
            ],
            'upload_dir' => config('upload.dir', __DIR__ . '/../uploads/'),
            'max_upload_size' => config('upload.max_size', 2097152),
        ];
    } else {
        // Create legacy config object for backward compatibility
        $legacyConfig = (object)[
            'db' => (object)[
                'host' => $config->get('db.host', 'localhost'),
                'dbname' => $config->get('db.dbname', 'as_olympique_db'),
                'user' => $config->get('db.user', 'root'),
                'pass' => $config->get('db.pass', 'root'),
                'charset' => $config->get('db.charset', 'utf8mb4'),
            ],
            'upload_dir' => $config->get('upload.dir', __DIR__ . '/../uploads/'),
            'max_upload_size' => $config->get('upload.max_size', 2097152),
        ];
    }
    
} else {
    // Fallback to legacy config.php if Composer not installed
    error_log('[Init] Using legacy configuration (Composer not installed)');
    
    $legacyConfig = require __DIR__ . '/config.php';
    
    // Session configuration (compatible PHP < 7.3)
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(0, '/', '', $secure, true);
    session_start();
    
    // PDO connection with error handling
    try {
        $dsn = "mysql:host={$legacyConfig->db->host};dbname={$legacyConfig->db->dbname};charset={$legacyConfig->db->charset}";
        $pdo = new PDO($dsn, $legacyConfig->db->user, $legacyConfig->db->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo "Erreur de connexion DB: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Set $config to legacy format for backward compatibility with existing code
$config = $legacyConfig;
