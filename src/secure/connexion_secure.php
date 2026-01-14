<?php
/**
 * PARTIE C - CONNEXION SECURISEE (Protection Injection SQL)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Requtes preparees avec parametres lis
 * - password_verify() pour la comparaison
 * - Messages d'erreur generiques
 * - Protection CSRF
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [SECURE] Verification CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide.');
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // [SECURE] Validation basique
        if (empty($login) || empty($password)) {
            $message = error_message('Veuillez remplir tous les champs.');
        } else {
            // [SECURE] SECURISE : Requte preparee avec parametres lis
            $stmt = $pdo->prepare("SELECT * FROM membres WHERE login = :login");
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch();
            
            // [SECURE] SECURISE : Verification du mot de passe hash
            if ($user && password_verify($password, $user['password'])) {
                // [SECURE] Regeneration de l'ID de session (anti fixation)
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['logged_in'] = true;
                
                $message = success_message('Connexion reussie ! Bienvenue ' . e($user['nom']));
            } else {
                // [SECURE] SECURISE : Message generique (ne rvle pas si le login existe)
                $message = error_message('Identifiants incorrects.');
                
                // [SECURE] Optionnel : Log de la tentative echouee
                error_log("Tentative de connexion echouee pour : " . $login);
            }
        }
    }
}
?>

<h2> Partie C - Connexion (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Requtes preparees, password_verify(), messages generiques.
</div>

<?= $message ?>

<form method="POST">
    <!-- [SECURE] Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <label for="login">Login :</label>
    <input type="text" name="login" id="login" placeholder="admin" required>
    
    <label for="password">Mot de passe :</label>
    <input type="password" name="password" id="password" placeholder="Mot de passe" required>
    
    <button type="submit"> Se connecteer</button>
</form>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>Injection SQL</td><td>Requtes preparees avec :parametres</td></tr>
    <tr><td>Mots de passe en clair</td><td>password_hash() / password_verify()</td></tr>
    <tr><td>Enumeration d'utilisateurs</td><td>Messages d'erreur generiques</td></tr>
    <tr><td>CSRF</td><td>Token CSRF valid</td></tr>
    <tr><td>Fixation de session</td><td>session_regenerate_id(true)</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// [SECURE] Requte preparee (injection SQL impossible)
$stmt = $pdo->prepare("SELECT * FROM membres WHERE login = :login");
$stmt->execute(['login' => $login]);
$user = $stmt->fetch();

// [SECURE] Verification du mot de passe hash
if ($user && password_verify($password, $user['password'])) {
    // Connexion reussie
}

// [SECURE] Hashage du mot de passe  l'inscription
$hash = password_hash($password, PASSWORD_DEFAULT);
// Generee : $2y$10$... (bcrypt, sal automatiquement)
</div>

<h3> Hashage des mots de passe</h3>
<p>Pour mettre  jour les mots de passe existants en clair :</p>
<div class="code-block">
// Genereer un hash pour "password123"
$hash = password_hash('password123', PASSWORD_DEFAULT);
echo $hash;
// Resultat : $2y$10$randomsaltXXXXXXXXXXXXXhashXXXXXXXXXXXXXXXXXXXXX

// SQL pour mettre  jour
UPDATE membres SET password = '$2y$10$...' WHERE login = 'admin';
</div>

<p><a href="../vuln/connexion_vuln.php"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
