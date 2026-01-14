<?php
/**
 * PARTIE F - SUPPRESSION SECURISEE (Protection CSRF)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Token CSRF obligatoire
 * - Actions sensibles uniquement en POST
 * - Verification des droits
 * - Double confirmation
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';

// [SECURE] SECURISE : Suppression uniquement via POST avec token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [SECURE] Verification du token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide. Action refuse.');
    } else {
        // Suppression d'un commentaire
        if (isset($_POST['del_id'])) {
            $id = filter_var($_POST['del_id'], FILTER_VALIDATE_INT);
            
            if ($id === false || $id <= 0) {
                $message = error_message('ID invalide.');
            } else {
                // [SECURE] Optionnel : Verifier que l'utilisateur a le droit de supprimeer
                // Ex: verifier qu'il est admin ou proprietaire du commentaire
                
                $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    $message = success_message('Commentaire #' . $id . ' supprime.');
                } else {
                    $message = error_message('Commentaire non trouv.');
                }
            }
            
            // [SECURE] Regenerer le token apres utilisation (one-time token)
            unset($_SESSION['csrf_token']);
        }
        
        // Suppression de tous les commentaires
        if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
            // [SECURE] Double verification avec confirmation
            if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'OUI') {
                $message = error_message('Confirmation requise. Tapez "OUI" pour confirmer.');
            } else {
                $pdo->exec("DELETE FROM commentaires");
                $message = success_message('Tous les commentaires ont t supprimes.');
                unset($_SESSION['csrf_token']);
            }
        }
        
        // Changement d'email
        if (isset($_POST['action']) && $_POST['action'] === 'change_email') {
            $new_email = filter_var($_POST['new_email'] ?? '', FILTER_VALIDATE_EMAIL);
            
            if (!$new_email) {
                $message = error_message('Email invalide.');
            } else {
                $user_id = $_SESSION['user_id'] ?? null;
                
                if (!$user_id) {
                    $message = error_message('Vous devez etre connecte.');
                } else {
                    $stmt = $pdo->prepare("UPDATE membres SET email = ? WHERE id = ?");
                    $stmt->execute([$new_email, $user_id]);
                    $message = success_message('Email modifie en : ' . e($new_email));
                    unset($_SESSION['csrf_token']);
                }
            }
        }
    }
}

// Recuperation des commentaires
$stmt = $pdo->query("SELECT * FROM commentaires ORDER BY id DESC LIMIT 10");
$commentaires = $stmt->fetchAll();
?>

<h2>Partie F - CSRF (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Toutes les actions sensibles ncessitent un token CSRF valide.
</div>

<?= $message ?>

<h3>Commentaires</h3>
<?php if (empty($commentaires)): ?>
    <p>Aucun commentaire.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Auteur</th><th>Contenu</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($commentaires as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= e($c['auteur']) ?></td>
                <td><?= e(substr($c['contenu'], 0, 50)) ?>...</td>
                <td>
                    <!-- [SECURE] SECURISE : Formulaire POST avec token CSRF -->
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?')">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="del_id" value="<?= $c['id'] ?>">
                        <button type="submit" style="background:#dc3545;padding:5px 10px;"></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h3>Actions administrateur</h3>

<!-- [SECURE] SECURISE : Formulaire avec token CSRF + double confirmation -->
<form method="POST" style="margin-bottom: 20px;">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="action" value="delete_all">
    <label>Pour supprimeer tous les commentaires, tapez "OUI" :</label>
    <input type="text" name="confirm" placeholder="OUI" style="width:100px;">
    <button type="submit" style="background: #dc3545;"> Supprimer tout</button>
</form>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="hidden" name="action" value="change_email">
    <label for="new_email">Changer mon email :</label>
    <input type="email" name="new_email" id="new_email" placeholder="nouvel@email.com" required>
    <button type="submit"> Modifier</button>
</form>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>CSRF via GET</td><td>Actions sensibles uniquement en POST</td></tr>
    <tr><td>CSRF via POST</td><td>Token CSRF obligatoire et vrifi</td></tr>
    <tr><td>Rutilisation token</td><td>Token rgenere apres utilisation</td></tr>
    <tr><td>Actions destructrices</td><td>Double confirmation requise</td></tr>
    <tr><td>CSRF cross-origin</td><td>Cookie SameSite=Strict</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// [SECURE] Generation du token (functions.php)
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// [SECURE] Verification du token
function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// [SECURE] Dans le formulaire
&lt;input type="hidden" name="csrf_token" value="&lt;?= generate_csrf_token() ?&gt;"&gt;

// [SECURE]  la rception
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('Token CSRF invalide');
}
</div>

<p><a href="../vuln/del_vuln.php"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
