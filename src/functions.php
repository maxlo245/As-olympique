<?php 
// functions.php - utilitaires réutilisables 
function e($s) { 
return htmlspecialchars(isset($s) ? $s : '', ENT_QUOTES, 'UTF-8');  }     
// htmlspecialchars encode <,>,",',&. 
function generate_csrf_token() { 
if (empty($_SESSION['csrf_token'])) { 
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
// bin2hex(random_bytes(32)) convertit une chaîne encodée en hexadécimal vers du binaire. 
// Ici cela évite le token imprévisible 
} 
return $_SESSION['csrf_token']; 
} 
function verify_csrf_token($token) { 
$csrf = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
$tok = isset($token) ? $token : '';
return hash_equals($csrf, $tok); 
// hash_equals() évite le timing attacks ou attaque temporelle en vérifiant si deux chaînes de caractères sont égales sans divulguer d'informations sur le contenu 
} 
function secure_filename($name) { 
// conserve uniquement lettres chiffres et points, remplace espaces 
$name = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name); 
return $name;
}
