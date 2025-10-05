<?php
/**
 * Plugin Name: URL Content Mapper
 * Plugin URI: https://wajahatmubashir.netlify.app/
 * Description: A WordPress plugin to dynamically add content groups in GA4 and inject code before GA4/GTM in the head tag.
 * Version: 1.3
 * Author: Wajahat Mubashir
 * Author URI: https://www.linkedin.com/in/wajahatwritescode/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: url-content-mapper
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define plugin constants
define('URLCOMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('URLCOMA_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Register Uninstall Hook
 */
function urlcoma_uninstall() {
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        exit;
    }
    delete_option('urlcoma_mapper_data');
}
register_uninstall_hook(__FILE__, 'urlcoma_uninstall');

/**
 * Enqueue Admin Scripts and Styles
 */
function urlcoma_enqueue_admin_assets($hook) {
    if ($hook !== 'tools_page_url-content-mapper') {
        return;
    }

    wp_enqueue_script(
        'urlcoma-admin-script',
        URLCOMA_PLUGIN_URL . 'assets/admin-script.js',
        array('jquery'),
        '1.3',
        true
    );

    wp_enqueue_style(
        'urlcoma-admin-style',
        URLCOMA_PLUGIN_URL . 'assets/admin-style.css',
        array(),
        '1.3'
    );
}
add_action('admin_enqueue_scripts', 'urlcoma_enqueue_admin_assets');

// Frontend script is enqueued in functions.php to avoid duplicate enqueuing

// Include necessary files
$files_to_include = [
    'admin-settings.php',
    'functions.php'
];

foreach ($files_to_include as $file) {
    $file_path = URLCOMA_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        include_once $file_path;
    }
}
