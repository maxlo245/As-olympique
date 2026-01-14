<?php
/**
 * Point d'entrée JSON pour vérifier l'état de phpMyAdmin local.
 * Utilisé dans le header pour afficher un indicateur online/offline.
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache');

$status = 'offline';

// Vérifier si phpMyAdmin est accessible sur le port 8081
$connection = @fsockopen('127.0.0.1', 8081, $errno, $errstr, 1);
if ($connection) {
    fclose($connection);
    $status = 'online';
}

echo json_encode(['status' => $status]);
