<?php
/**
 * PARTIE E - AUTHENTIFICATION SECURISEE (Protection Session)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Configuration de session securisee
 * - Regeneration d'ID apres connexion
 * - Protection anti-bruteforce
 * - Mots de passe hashs
 * - Destruction propre de session
 */

// [SECURE] Configuration securisee de session AVANT session_start()
ini_set('session.cookie_httponly', 1);  // Pas accessible en JavaScript
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);  // HTTPS uniquement
ini_set('session.cookie_samesite', 'Strict');  // Protection CSRF
ini_set('session.use_strict_mode', 1);  // Refuse les session ID non generes par le serveur
ini_set('session.use_only_cookies', 1);  // Pas de session ID dans l'URL

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$config = require __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
    $pdo = new PDO($dsn, $config->db->user, $config->db->pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Erreur DB");
}

include __DIR__ . '/../templates/header.php';

$message = '';

// [SECURE] Protection anti-bruteforce
function check_bruteforce($pdo, $login) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                           WHERE login = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$login]);
    $result = $stmt->fetch();
    return $result['attempts'] >= 5;  // Max 5 tentatives en 15 minutes
}

function log_attempt($pdo, $login, $success) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (login, ip_address, attempt_time, success) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$login, $_SERVER['REMOTE_ADDR'] ?? 'unknown', $success ? 1 : 0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [SECURE] Verification CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide.');
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // [SECURE] Verification anti-bruteforce
        if (check_bruteforce($pdo, $login)) {
            $message = error_message('Trop de tentatives. Reessayez dans 15 minutes.');
        } else {
            // [SECURE] Requte preparee
            $stmt = $pdo->prepare("SELECT * FROM membres WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();
            
            // [SECURE] Verification du mot de passe hash
            if ($user && password_verify($password, $user['password'])) {
                // [SECURE] CRUCIAL : Regenerer l'ID de session (anti fixation)
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
                
                // [SECURE] Log de connexion reussie
                log_attempt($pdo, $login, true);
                
                $message = success_message('Connexion reussie !');
            } else {
                // [SECURE] Log de tentative echouee
                log_attempt($pdo, $login, false);
                $message = error_message('Identifiants incorrects.');
            }
        }
    }
}

// [SECURE] Dconnexion propre
if (isset($_GET['logout'])) {
    // [SECURE] Detruire toutes les donnes de session
    $_SESSION = [];
    
    // [SECURE] Detruire le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // [SECURE] Detruire la session
    session_destroy();
    
    header('Location: auth_secure.php');
    exit;
}

// [SECURE] Verification de cohrence de session (optionnel mais recommand)
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    // Verifier si l'IP ou le user-agent a chang (possible vol de session)
    if ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '') ||
        $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        // Session potentiellement vole, deconnecteeer
        session_destroy();
        header('Location: auth_secure.php');
        exit;
    }
}
?>

<h2> Partie E - Authentification (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Session securisee, anti-bruteforce, regeneration ID, destruction propre.
</div>

<?= $message ?>

<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    <div class="alert alert-success">
        <p>[SECURE] Connecte en tant que : <strong><?= e($_SESSION['user_nom'] ?? 'Utilisateur') ?></strong></p>
        <p>Session ID : <code><?= session_id() ?></code> (rgenere  chaque connexion)</p>
        <p>Connecte depuis : <?= date('H:i:s', $_SESSION['login_time']) ?></p>
        <p><a href="?logout=1">Se deconnecteeer proprement</a></p>
    </div>
<?php else: ?>
    <form method="POST">
        <!-- [SECURE] Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <label for="login">Login :</label>
        <input type="text" name="login" id="login" required>
        
        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>
        
        <button type="submit"> Se connecteer</button>
    </form>
<?php endif; ?>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>Session Fixation</td><td>session_regenerate_id(true) apres connexion</td></tr>
    <tr><td>Session Hijacking (XSS)</td><td>Cookie HttpOnly + Secure</td></tr>
    <tr><td>Session dans URL</td><td>use_only_cookies = 1</td></tr>
    <tr><td>CSRF via session</td><td>SameSite=Strict</td></tr>
    <tr><td>Brute Force</td><td>Limite de tentatives + dlai</td></tr>
    <tr><td>Vol de session</td><td>Verification IP + User-Agent</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// [SECURE] Configuration AVANT session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // En HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

// [SECURE] Aprs connexion reussie : regenerer l'ID
session_regenerate_id(true);

// [SECURE] Dconnexion propre
$_SESSION = [];
session_destroy();
// + supprimeer le cookie
</div>

<p><a href="../vuln/auth_vuln.php"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
