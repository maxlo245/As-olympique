<?php
/**
 * Alert Templates
 *
 * Reusable alert components for consistent user feedback.
 * These functions are now in functions.php for easier access.
 *
 * @package AsOlympique
 */

// This file can contain additional alert variants if needed

/**
 * Render info message
 *
 * @param string $message Info message
 * @return string HTML alert box
 */
function info_message(string $message): string
{
    return sprintf(
        '<div class="alert" style="background: var(--gray-100); border-left: 6px solid var(--gray-500);">ℹ️ %s</div>',
        e($message)
    );
}
