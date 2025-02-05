<?php
/**
 * Plugin Name: URL Content Mapper
 * Plugin URI: https://wajahatmubashir.netlify.app/
 * Description: A WordPress plugin to dynamically add content groups in GA4 and inject code before GA4/GTM in head tag.
 * Version: 1.0
 * Author: Wajahat Mubashir
 * Author URI: https://www.linkedin.com/in/wajahatwritescode/
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: url-content-mapper
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Register Uninstall Hook
 *
 * This hook will be called when the plugin is deleted from the WordPress Dashboard.
 */
register_uninstall_hook(__FILE__, 'wp_url_content_mapper_uninstall');

/**
 * Callback for the uninstall hook
 *
 * Remove any options or other data stored by the plugin to clean up after deletion.
 */
function wp_url_content_mapper_uninstall() {
    // Delete the plugin option
    delete_option('url_mapper_data');
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'admin-settings.php';
include_once plugin_dir_path(__FILE__) . 'functions.php';

// Hook to inject code before GA4/GTM
add_action('wp_head', 'inject_url_mapper_code', 1);