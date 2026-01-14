<?php
/**
 * PARTIE A - UPLOAD SECURISE
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Verification du type MIME rel (finfo)
 * - Liste blanche d'extensions autorises
 * - Limite de taille ct serveur
 * - Renommage du fichier (UUID)
 * - Stockage hors du webroot
 * - Token CSRF
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';

// Extensions et types MIME autoriss
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
$allowed_mimes = [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    
    // [SECURE] Verification du token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide.');
    } else {
        $fichier = $_FILES['fichier'];
        
        // [SECURE] Verification des erreurs d'upload
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $message = error_message('Erreur lors de l\'upload : code ' . $fichier['error']);
        } else {
            // [SECURE] Verification de la taille
            if ($fichier['size'] > $config->max_upload_size) {
                $message = error_message('Fichier trop volumineux (max 2 Mo).');
            } else {
                // [SECURE] Verification de l'extension
                $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowed_extensions)) {
                    $message = error_message('Extension non autorise. Extensions permises : ' . implode(', ', $allowed_extensions));
                } else {
                    // [SECURE] Verification du type MIME rel (pas juste le Content-Type envoye)
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime_type = $finfo->file($fichier['tmp_name']);
                    
                    if (!in_array($mime_type, $allowed_mimes)) {
                        $message = error_message('Type de fichier non autoris : ' . htmlspecialchars($mime_type));
                    } else {
                        // [SECURE] Generation d'un nom unique (empche l'Ecrasement et l'executeion)
                        $nouveau_nom = bin2hex(random_bytes(16)) . '.' . $extension;
                        
                        // [SECURE] Destination hors du webroot
                        $destination = $config->upload_dir . $nouveau_nom;
                        
                        // [SECURE] Cration du dossier si necessaire
                        if (!is_dir($config->upload_dir)) {
                            mkdir($config->upload_dir, 0755, true);
                        }
                        
                        if (move_uploaded_file($fichier['tmp_name'], $destination)) {
                            // [SECURE] Enregistrement en base de donnes
                            $stmt = $pdo->prepare("INSERT INTO fichiers (nom_original, nom_serveur, mime_type, taille, date_upload) VALUES (?, ?, ?, ?, NOW())");
                            $stmt->execute([
                                secure_filename($fichier['name']),
                                $nouveau_nom,
                                $mime_type,
                                $fichier['size']
                            ]);
                            
                            $message = success_message('Fichier upload avec succs : ' . e(secure_filename($fichier['name'])));
                        } else {
                            $message = error_message('Erreur lors du deplacement du fichier.');
                        }
                    }
                }
            }
        }
    }
}
?>

<h2> Partie A - Envoi de fichier (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Ce formulaire implementee toutes les contre-mesures necessaires.
</div>

<?= $message ?>

<form method="POST" enctype="multipart/form-data">
    <!-- [SECURE] Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <label for="fichier">Selectionner un fichier :</label>
    <input type="file" name="fichier" id="fichier" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" required>
    <small>Formats accepts : JPG, PNG, GIF, PDF, DOC, DOCX (max 2 Mo)</small>
    
    <button type="submit"> Envoyer</button>
</form>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>Upload de fichier PHP</td><td>Liste blanche d'extensions + verification MIME</td></tr>
    <tr><td>Excution de code</td><td>Stockage hors webroot</td></tr>
    <tr><td>Ecrasement de fichier</td><td>Renommage avec UUID</td></tr>
    <tr><td>CSRF</td><td>Token CSRF valid</td></tr>
    <tr><td>Path traversal</td><td>Fonction secure_filename()</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// 1. Verifier le type MIME rel avec finfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($fichier['tmp_name']);

// 2. Liste blanche d'extensions
$allowed = ['jpg', 'png', 'pdf'];
if (!in_array($extension, $allowed)) { die('Refus'); }

// 3. Renomemer le fichier
$nouveau_nom = bin2hex(random_bytes(16)) . '.' . $extension;

// 4. Stockeer hors du webroot
$destination = '/var/uploads/' . $nouveau_nom; // Pas dans /var/www/html/
</div>

<p><a href="../vuln/upload_vuln.php"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
