<?php 
// init.php - démarre session et PDO 
$config = require __DIR__ . '/config.php'; 
// session cookie params (compatible PHP < 7.3)
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params(0, '/', '', $secure, true);
session_start();  // s'il faut associer un upload à un utilisateur, garder la session de l'utilisateur 
// PDO  en mode exceptions try/catch est meilleur que reporting 
try { 
    $dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
    $pdo = new PDO($dsn, $config->db->user, $config->db->pass, [  
    //  
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    PDO::ATTR_EMULATE_PREPARES => false 
    // PDO::ATTR_EMULATE_PREPARES => false force l'utilisation de vraies requêtes préparées 
    ]); 
} catch (Exception $e) { 
http_response_code(500); 
echo "Erreur de connexion DB: " . htmlspecialchars($e->getMessage());    
// htmlspecialchars encode <,>,",',&. 
exit; 
}
