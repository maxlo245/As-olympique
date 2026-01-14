<?php 
require __DIR__ . '/init.php'; 
require __DIR__ . '/functions.php'; 
include __DIR__ . '/templates/header.php';
?> 
<h2>Menu TD</h2> 
<ul> 
<li><a href="vuln/upload_vuln.php">Upload (vuln)</a>  
<li><a href="vuln/bonjour_vuln.php">Transmission GET (vuln)</a>  
<li><a href="vuln/connexion_vuln.php">Connexion (vuln)</a>  
<li><a href="vuln/commentaire_vuln.php">Commentaire (XSS vuln)</a>
<li><a href="vuln/auth_vuln.php">Auth session (vuln)</a>  
<li><a href="vuln/del_vuln.php">Suppression GET (CSRF vuln)</a>
<li><a href="vuln/parse_vuln_xml.php">XXE vuln (XML)</a>  
</ul> 
<?php echo '<p>Fichier source PPTX utilisé : /upload/21976203-f3c2-4002-b1a7-f7a28952c8d9.pptx</p>'; ?> 
</body> 
</html>
