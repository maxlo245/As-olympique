<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AS Olympique Saint-Remy - TD Cybersecurite</title>
    
    <!-- Performance: Preload critical CSS -->
    <link rel="preload" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/css/style.css" as="style">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/css/style.css">
    
    <!-- Optional inline critical CSS for faster rendering -->
    <style>
        /* Reset et base */
        *, *::before, *::after { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        :root {
            --bg: #f5f5f5;
            --bg-alt: #fff;
            --text: #171717;
            --text-muted: #525252;
            --border: #000;
            --accent: #000;
            --accent-hover: #fff;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --gray-800: #262626;
            --gray-900: #171717;
            --black: #000;
            --white: #fff;
            --transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        [data-theme="dark"] {
            --bg: #0a0a0a;
            --bg-alt: #171717;
            --text: #fafafa;
            --text-muted: #a3a3a3;
            --border: #fff;
            --accent: #fff;
            --accent-hover: #000;
            --gray-100: #171717;
            --gray-200: #262626;
            --gray-300: #404040;
            --gray-400: #525252;
            --gray-500: #737373;
            --gray-600: #a3a3a3;
            --gray-700: #d4d4d4;
            --gray-800: #e5e5e5;
            --gray-900: #fafafa;
            --black: #fff;
            --white: #171717;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
            transition: background var(--transition), color var(--transition);
        }
        
        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            background: var(--accent);
            border: 2px solid var(--border);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            z-index: 1000;
        }
        
        .theme-toggle:hover {
            background: var(--accent-hover);
        }
        
        .theme-toggle:hover .theme-icon {
            color: var(--accent);
        }
        
        .theme-icon {
            font-size: 1.2rem;
            color: var(--accent-hover);
            transition: color var(--transition);
            font-style: normal;
        }
        
        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Header */
        .header {
            background: var(--black);
            color: var(--white);
            padding: 30px 40px;
            margin-bottom: 30px;
            border: 2px solid var(--black);
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-bottom: 5px;
        }
        
        .header-subtitle {
            font-size: 0.875rem;
            color: var(--gray-400);
            font-weight: 400;
        }
        
        /* Navigation */
        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            background: var(--black);
            padding: 2px;
            margin-bottom: 30px;
        }
        
        .nav a {
            flex: 1;
            min-width: 120px;
            padding: 14px 20px;
            background: var(--white);
            color: var(--black);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
            transition: all var(--transition);
            border: none;
        }
        
        .nav a:hover {
            background: var(--black);
            color: var(--white);
        }
        
        /* Content box */
        .content-box {
            background: var(--white);
            border: 2px solid var(--black);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        /* Headings */
        h1, h2, h3 {
            color: var(--black);
            font-weight: 600;
            letter-spacing: -0.02em;
        }
        
        h2 {
            font-size: 1.25rem;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--black);
        }
        
        h3 {
            font-size: 1rem;
            margin: 25px 0 15px 0;
        }
        
        p {
            margin-bottom: 15px;
            color: var(--gray-700);
        }
        
        /* Alerts */
        .alert {
            padding: 16px 20px;
            margin-bottom: 20px;
            border: 2px solid var(--black);
            font-size: 0.875rem;
        }
        
        .alert-danger {
            background: var(--white);
            border-left: 6px solid var(--black);
        }
        
        .alert-success {
            background: var(--gray-100);
            border-left: 6px solid var(--gray-500);
        }
        
        .alert-warning {
            background: var(--gray-200);
            border-left: 6px solid var(--gray-600);
        }
        
        /* Forms */
        form {
            margin: 25px 0;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--black);
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 20px;
            border: 2px solid var(--black);
            background: var(--white);
            font-size: 0.875rem;
            font-family: inherit;
            transition: all var(--transition);
        }
        
        input:focus, 
        textarea:focus, 
        select:focus {
            outline: none;
            background: var(--gray-100);
        }
        
        /* Buttons */
        button, 
        input[type="submit"] {
            background: var(--black);
            color: var(--white);
            padding: 14px 32px;
            border: 2px solid var(--black);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            transition: all var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        button:hover, 
        input[type="submit"]:hover {
            background: var(--white);
            color: var(--black);
        }
        
        button:active,
        input[type="submit"]:active {
            transform: scale(0.98);
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.875rem;
        }
        
        th, td {
            padding: 14px 16px;
            text-align: left;
            border: 1px solid var(--gray-300);
        }
        
        th {
            background: var(--black);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        tr {
            transition: background var(--transition);
        }
        
        tr:hover {
            background: var(--gray-100);
        }
        
        /* Links in tables */
        td a {
            color: var(--black);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid var(--black);
            transition: all var(--transition);
        }
        
        td a:hover {
            background: var(--black);
            color: var(--white);
            padding: 2px 6px;
            margin: -2px -6px;
        }
        
        /* Code blocks */
        .code-block, code {
            background: var(--gray-900);
            color: var(--gray-100);
            padding: 20px;
            overflow-x: auto;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.8125rem;
            line-height: 1.7;
            border: 2px solid var(--black);
        }
        
        code {
            padding: 3px 8px;
            font-size: 0.8125rem;
        }
        
        /* Warning/Danger boxes */
        .warning-box {
            background: var(--gray-200);
            border: 2px solid var(--black);
            border-left: 6px solid var(--gray-600);
            padding: 20px;
            margin: 25px 0;
        }
        
        .danger-box {
            background: var(--white);
            border: 2px solid var(--black);
            border-left: 6px solid var(--black);
            padding: 20px;
            margin: 25px 0;
        }
        
        /* Comement boxes */
        .comement-box {
            background: var(--gray-100);
            border: 2px solid var(--black);
            padding: 20px;
            margin: 15px 0;
        }
        
        .comement-box strong {
            color: var(--black);
        }
        
        .comement-box small {
            color: var(--gray-500);
            margin-left: 10px;
        }
        
        .comement-box p {
            margin-top: 10px;
            margin-bottom: 0;
        }
        
        /* Pre/code blocks */
        pre {
            background: var(--gray-900);
            color: var(--gray-100);
            padding: 15px 20px;
            border: 2px solid var(--black);
            overflow-x: auto;
            font-family: monospace;
            font-size: 0.875rem;
        }
        
        /* Lists */
        ul, ol {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        li {
            margin-bottom: 8px;
            color: var(--gray-700);
        }
        
        li a {
            color: var(--black);
            text-decoration: none;
            border-bottom: 1px solid var(--black);
            transition: all var(--transition);
        }
        
        li a:hover {
            background: var(--black);
            color: var(--white);
            padding: 2px 6px;
            margin: -2px -6px;
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .container {
                padding: 20px 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .nav {
                flex-direction: column;
            }
            
            .nav a {
                min-width: 100%;
            }
            
            .content-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<button class="theme-toggle" id="themeToggle" aria-label="Basculer le theme">
    <i class="theme-icon" id="themeIcon">&#9790;</i>
</button>

<div class="container">
    <header class="header">
        <h1>AS Olympique Saint-Remy</h1>
        <span class="header-subtitle">TD Cybersecurite OWASP - BTS SIO</span>
    </header>
    
    <nav class="nav">
        <?php 
        $basePath = defined('BASE_URL') ? BASE_URL : '';
        ?>
        <a href="<?= $basePath ?>/index.php">Accueil</a>
        <a href="<?= $basePath ?>/vuln/upload_vuln.php">Upload</a>
        <a href="<?= $basePath ?>/vuln/bonjour_vuln.php?nom=Test">XSS</a>
        <a href="<?= $basePath ?>/vuln/connexion_vuln.php">SQL</a>
        <a href="<?= $basePath ?>/vuln/commentaire_vuln.php">Commentaires</a>
    </nav>
    
    <nav class="nav" style="background: var(--gray-600);">
        <a href="<?= $basePath ?>/secure/upload_secure.php">Upload (S)</a>
        <a href="<?= $basePath ?>/secure/bonjour_secure.php?nom=Test">XSS (S)</a>
        <a href="<?= $basePath ?>/secure/connexion_secure.php">SQL (S)</a>
        <a href="<?= $basePath ?>/secure/commentaire_secure.php">Commentaires (S)</a>
        <a href="<?= $basePath ?>/secure/auth_secure.php">Session (S)</a>
        <a href="http://127.0.0.1:8081/" target="_blank" id="phpmyadmin-link" style="background: var(--gray-800); color: var(--white) !important; position: relative;">
            <span class="status-dot" id="pma-status"></span>
            phpMyAdmin
        </a>
    </nav>
    
    <!-- External JavaScript -->
    <script src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/js/main.js" defer></script>
    
    <main class="content-box">
