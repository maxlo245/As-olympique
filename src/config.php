<?php 
// config.php - paramètres de connexion et config globale 
return (object)[ 
'db' => (object)[ 
'host' => 'localhost', 
'dbname' => 'as_olympique_db', 
'user' => 'root', 
'pass' => 'root', 
'charset' => 'utf8mb4' 
],
// chemin vers uploads (préférer hors webroot) 
'upload_dir' => __DIR__ . '/../uploads/',    // stockage hors du dossier public pour  
// empêcher un accès direct 
'max_upload_size' => 2 * 1024 * 1024 // 2MB   // taille maximal supportée 
];
