<?php
/**
 * PARTIE C - INJECTION SQL VULNERABLE
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Concatnation directe des entrees utilisateur dans la requete SQL
 * - Pas de requetes preparees
 * - Pas de validation des entrees
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // [VULNERABLE] VULNERABLE : Concatnation directe dans la requete SQL
    // Un attaquant peut injecter du SQL via le champ login ou password
    $sql = "SELECT * FROM membres WHERE login = '$login' AND password = '$password'";
    
    try {
        // [VULNERABLE] VULNERABLE : Excution directe de la requete non preparee
        $stmt = $pdo->query($sql);
        $user = $stmt->fetch();
        
        if ($user) {
            $message = '<div class="alert alert-success">[SECURE] Connexion reussie ! Bienvenue ' . $user['nom'] . '</div>';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
        } else {
            $message = '<div class="alert alert-danger">[VULNERABLE] Login ou mot de passe incorrect.</div>';
        }
    } catch (PDOException $e) {
        // [VULNERABLE] VULNERABLE : Affichage de l'erreur SQL (aide l'attaquant)
        $message = '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
    }
}
?>

<h2> Partie C - Injection SQL</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Ce formulaire utilise la concatnation directe dans les requetes SQL. Injection possible !
</div>

<?= $message ?>

<form method="POST">
    <label for="login">Login :</label>
    <input type="text" name="login" id="login" placeholder="admin">
    
    <label for="password">Mot de passe :</label>
    <input type="text" name="password" id="password" placeholder="password">
    
    <button type="submit"> Se connecteer</button>
</form>

<h3> Exploitation possible</h3>
<p>Testez ces injections dans le champ <strong>login</strong> :</p>

<div class="code-block">
<!-- Bypass d'authentification -->
' OR '1'='1' --
' OR '1'='1' #
admin'--
' OR 1=1 --

<!-- Extraction de donnes (UNION) -->
' UNION SELECT 1,2,3,4,5,6 --
' UNION SELECT id,login,password,nom,email,role FROM membres --

<!-- Injection avec sous-requete -->
' OR (SELECT COUNT(*) FROM membres) > 0 --

<!-- Time-based blind SQL injection -->
' OR SLEEP(5) --
</div>

<h3> Questions</h3>
<ol>
    <li>Quelle est la requete SQL execute si login = <code>' OR '1'='1' --</code> ?</li>
    <li>Comement l'attaquant peut-il extraire tous les mots de passe ?</li>
    <li>Quelle est la solution pour securiseer ce code ?</li>
    <li>Pourquoi afficher les erreurs SQL est-il dangereux ?</li>
</ol>

<p><a href="../secure/connexion_secure.php"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
