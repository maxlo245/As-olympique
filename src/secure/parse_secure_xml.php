<?php
/**
 * PARTIE G - PARSING XML SECURISE (Protection XXE)
 * 
 * [SECURE] VERSION SECURISEE
 * 
 * Contre-mesures implementees :
 * - Dsactivation des entits externes
 * - Dsactivation du chargement DTD
 * - Utilisation de SimpleXML ou json comeme alternative
 * - Validation du contenu XML
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml_data'])) {
    
    // [SECURE] Verification CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = error_message('Token CSRF invalide.');
    } else {
        $xml_data = $_POST['xml_data'];
        
        // [SECURE] CRUCIAL : Desactiver les entits externes AVANT le parsing
        // Pour PHP < 8.0
        if (function_exists('libxml_disable_entity_loader')) {
            libxml_disable_entity_loader(true);
        }
        
        // [SECURE] Utiliser libxml_use_internal_errors pour eviter les messages d'erreur revelateurs
        libxml_use_internal_errors(true);
        
        // [SECURE] Options securisees : PAS de LIBXML_NOENT ni LIBXML_DTDLOAD
        $doc = new DOMDocument();
        $doc->loadXML($xml_data, LIBXML_NONET);  // LIBXML_NONET = pas d'accs rseau
        
        // Verifier les erreurs de parsing
        $errors = libxml_get_errors();
        if (!empty($errors)) {
            $message = error_message('Erreur de parsing XML.');
            // [SECURE] Ne pas afficher les details d'erreur en production
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                foreach ($errors as $error) {
                    $message .= '<br>' . e($error->message);
                }
            }
            libxml_clear_errors();
        } else {
            // [SECURE] Extraction securisee du contenu
            $result = $doc->textContent;
            $message = success_message('XML pars avec succs !');
        }
    }
}

// Exemple XML sr
$exemple_xml = '<?xml version="1.0" encoding="UTF-8"?>
<membre>
    <nom>Jean Dupont</nom>
    <email>jean@exemple.com</email>
    <activite>Football</activite>
</membre>';
?>

<h2> Partie G - XML (VERSION SECURISEE)</h2>

<div class="alert alert-success">
    <strong>[SECURE] VERSION SECURISEE</strong><br>
    Entits externes desactivees, pas de chargement DTD.
</div>

<?= $message ?>

<h3>Parser XML (securise)</h3>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <label for="xml_data">Donnes XML :</label>
    <textarea name="xml_data" id="xml_data" rows="10"><?= e($exemple_xml) ?></textarea>
    
    <button type="submit"> Parser XML</button>
</form>

<?php if ($result): ?>
    <h3>Resultat du parsing</h3>
    <div class="code-block">
        <?= e($result) ?>
    </div>
<?php endif; ?>

<h3>[SECURE] Contre-mesures implementees</h3>
<table>
    <tr><th>Faille</th><th>Contre-mesure</th></tr>
    <tr><td>XXE - Lecture fichiers</td><td>libxml_disable_entity_loader(true)</td></tr>
    <tr><td>XXE - SSRF</td><td>LIBXML_NONET (pas d'accs rseau)</td></tr>
    <tr><td>XXE - DTD externe</td><td>Pas de LIBXML_DTDLOAD</td></tr>
    <tr><td>Billion Laughs (DoS)</td><td>Entits desactivees</td></tr>
    <tr><td>Information disclosure</td><td>Erreurs non affiches</td></tr>
</table>

<h3> Code securise - Points cles</h3>
<div class="code-block">
// [SECURE] PHP < 8.0 : Desactiver le chargement d'entits externes
libxml_disable_entity_loader(true);

// [SECURE] Cacher les erreurs de parsing
libxml_use_internal_errors(true);

// [SECURE] Options securisees (PAS de LIBXML_NOENT)
$doc = new DOMDocument();
$doc->loadXML($xml, LIBXML_NONET);  // Pas d'accs rseau

// [SECURE] Alternative : SimpleXML avec options securisees
$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NONET);

// [SECURE] Meilleure alternative : utiliser JSON au lieu de XML
$data = json_decode($json_input, true);
</div>

<h3> Bonnes pratiques XML</h3>
<ol>
    <li><strong>Preferer JSON</strong> - Pas de vulnrabilit XXE avec JSON</li>
    <li><strong>Valider le schma</strong> - Utiliser XSD pour valider la structure</li>
    <li><strong>Limiter la taille</strong> - Refuser les XML trop volumineux</li>
    <li><strong>Sandbox</strong> - Parser XML dans un environnement isol si possible</li>
</ol>

<h3>Test de securite</h3>
<p>Essayez de parser ce XML malveillant :</p>
<div class="code-block">
&lt;?xml version="1.0"?&gt;
&lt;!DOCTYPE foo [
    &lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;
]&gt;
&lt;membre&gt;&amp;xxe;&lt;/membre&gt;
</div>
<p>L'entit externe sera ignore et le contenu restera vide ou contiendra "&xxe;" en texte brut.</p>

<p><a href="../vuln/parse_vuln_xml.php"> Voir la version vulnerable</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
