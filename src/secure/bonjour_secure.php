<?php
/**
 * PARTIE B - BONJOUR SECURISE (Protection XSS)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Echappement HTML avec htmlspecialchars()
 * - Validation de a l'entree
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

// [SECURE] SECURISE : Parametre chapp avant affichage
$nom = isset($_GET['nom']) ? e($_GET['nom']) : 'Visiteur';

// [SECURE] Optionnel : Validation supplementaire (longueur, caracteres)
$nom = substr($nom, 0, 50); // Limite la longueur
?>

<h2> Partie B - Transmission URL (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Le parametre GET est chapp avec htmlspecialchars() avant affichage.
</div>

<h3>Bienvenue !</h3>
<!-- [SECURE] SECURISE : Utilisation de la fonction e() qui applique htmlspecialchars() -->
<p>Bonjour, <strong><?= $nom ?></strong> !</p>

<h3> Testez vous-meme</h3>
<form method="GET">
    <label for="nom">Votre nom :</label>
    <input type="text" name="nom" id="nom" value="<?= $nom ?>" maxlength="50">
    <button type="submit">Envoyer</button>
</form>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>XSS reflte</td><td>htmlspecialchars() sur toutes les sorties</td></tr>
    <tr><td>Injection longue</td><td>Limitation de la longueur</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// Fonction d'Echappement (dans functions.php)
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Utilisation systmatique
$nom = e($_GET['nom']);
echo "Bonjour, " . e($nom);

// Les caracteres dangereux sont convertis :
// < devient &amp;lt;
// > devient &amp;gt;
// " devient &amp;quot;
// ' devient &amp;#039;
// & devient &amp;amp;
</div>

<h3>Test de securite</h3>
<p>Essayez d'injecter du code malveillant :</p>
<ul>
    <li><a href="?nom=<script>alert('XSS')</script>">Test XSS script</a></li>
    <li><a href="?nom=<img src=x onerror=alert('XSS')>">Test XSS img</a></li>
</ul>
<p>Vous verrez que le code est affiche en texte brut, pas execute !</p>

<p><a href="../vuln/bonjour_vuln.php?nom=Test"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
