<?php
/**
 * PARTIE D - COMMENTAIRES SECURISES (Protection XSS Stockee)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Echappement HTML  a l'affichage
 * - Validation des entrees
 * - Protection CSRF
 * - Content Security Policy
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';

// [SECURE] En-tetes de securite
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

include __DIR__ . '/../templates/header.php';

$message = '';

// Traitement de l'ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commentaire'])) {
    
    // [SECURE] Verification CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide.');
    } else {
        $auteur = trim($_POST['auteur'] ?? 'Anonyme');
        $commentaire = trim($_POST['commentaire'] ?? '');
        
        // [SECURE] Validation des entrees
        if (empty($commentaire)) {
            $message = error_message('Le commentaire ne peut pas etre vide.');
        } elseif (strlen($commentaire) > 1000) {
            $message = error_message('Le commentaire est trop long (max 1000 caracteres).');
        } elseif (strlen($auteur) > 50) {
            $message = error_message('Le nom est trop long (max 50 caracteres).');
        } else {
            // [SECURE] Stockage securise (requete preparee)
            // Note : On stocke le texte brut, l'Echappement se fait a l'affichage
            $stmt = $pdo->prepare("INSERT INTO commentaires (auteur, contenu, date_creation) VALUES (:auteur, :contenu, NOW())");
            $stmt->execute([
                'auteur' => $auteur,
                'contenu' => $commentaire
            ]);
            
            $message = success_message('Commentaire ajoute !');
            
            // [SECURE] Regeneration du token CSRF apres utilisation
            unset($_SESSION['csrf_token']);
        }
    }
}

// Recuperation des commentaires
$stmt = $pdo->query("SELECT * FROM commentaires ORDER BY date_creation DESC");
$commentaires = $stmt->fetchAll();
?>

<h2> Partie D - Commentaires (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Echappement HTML systmatique  a l'affichage + CSP + validation.
</div>

<?= $message ?>

<h3>Ajouter un commentaire</h3>
<form method="POST">
    <!-- [SECURE] Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <label for="auteur">Votre nom :</label>
    <input type="text" name="auteur" id="auteur" placeholder="Votre nom" maxlength="50">
    
    <label for="commentaire">Commentaire :</label>
    <textarea name="commentaire" id="commentaire" rows="4" placeholder="Votre commentaire..." maxlength="1000" required></textarea>
    <small>Maximum 1000 caracteres</small>
    
    <button type="submit"> Publier</button>
</form>

<h3>Commentaires</h3>
<?php if (empty($commentaires)): ?>
    <p>Aucun commentaire pour le moment.</p>
<?php else: ?>
    <?php foreach ($commentaires as $c): ?>
        <div class="comement-box">
            <strong><?= e($c['auteur']) ?></strong>
            <small>(<?= e($c['date_creation']) ?>)</small>
            <p><?= nl2br(e($c['contenu'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>XSS stockee</td><td>htmlspecialchars()  a l'affichage</td></tr>
    <tr><td>XSS via CSP</td><td>Content-Security-Policy header</td></tr>
    <tr><td>CSRF</td><td>Token CSRF valid</td></tr>
    <tr><td>Injection longue</td><td>Validation longueur (50/1000 chars)</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// [SECURE] echapper TOUJOURS  a l'affichage
echo e($commentaire['contenu']);
// ou
echo htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8');

// [SECURE] Conserver les retours  la ligne
echo nl2br(e($contenu));

// [SECURE] Content Security Policy (bloque scripts inline)
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

// [SECURE] Alternative : strip_tags() pour supprimeer le HTML
$clean = strip_tags($input); // Supprime tout le HTML
</div>

<h3>Test de securite</h3>
<p>Essayez de poster un commentaire avec du code malveillant :</p>
<pre>&lt;script&gt;alert('XSS')&lt;/script&gt;</pre>
<p>Vous verrez que le code est affiche en texte brut, pas execute !</p>

<p><a href="../vuln/commentaire_vuln.php">Voir la version vulnerable</a></p>

    </main>
    </main>\r\n</div>\r\n</body>\r\n</html>
