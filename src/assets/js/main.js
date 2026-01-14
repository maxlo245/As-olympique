/**
 * AS Olympique - Main JavaScript
 * Theme toggle and phpMyAdmin status checker
 */

(function() {
    'use strict';
    
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    if (themeToggle && themeIcon) {
        // Get stored theme preference
        const storedTheme = localStorage.getItem('theme');
        
        // Set initial theme
        if (storedTheme === 'dark' || (!storedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.innerHTML = '&#9728;'; // Sun icon
        } else {
            themeIcon.innerHTML = '&#9790;'; // Moon icon
        }
        
        // Toggle theme on click
        themeToggle.addEventListener('click', function() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeIcon.innerHTML = '&#9790;'; // Moon icon
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.innerHTML = '&#9728;'; // Sun icon
            }
        });
    }
    
    // phpMyAdmin status checker
    function checkPHPMyAdmin() {
        const statusDot = document.getElementById('pma-status');
        const link = document.getElementById('phpmyadmin-link');
        
        if (!statusDot || !link) {
            return;
        }
        
        // Check if phpMyAdmin is accessible
        fetch('/check_pma.php?' + Date.now())
            .then(response => response.json())
            .then(data => {
                if (data.status === 'online') {
                    statusDot.className = 'status-dot online';
                    link.title = 'phpMyAdmin est accessible';
                } else {
                    statusDot.className = 'status-dot offline';
                    link.title = 'phpMyAdmin non accessible';
                }
            })
            .catch(() => {
                statusDot.className = 'status-dot offline';
                link.title = 'phpMyAdmin non accessible';
            });
        
        // Check again in 5 seconds
        setTimeout(checkPHPMyAdmin, 5000);
    }
    
    // Start checking phpMyAdmin status
    if (document.getElementById('pma-status')) {
        checkPHPMyAdmin();
    }
    
})();
