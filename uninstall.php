<?php
/**
 * Uninstall script for URL Content Mapper plugin
 * 
 * This file is called when the plugin is deleted from the WordPress admin.
 * It removes all plugin data from the database to ensure a clean uninstall.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security check - ensure this is a legitimate uninstall request
if (!current_user_can('delete_plugins')) {
    exit;
}

/**
 * Remove all plugin options from the database
 */
function urlcoma_cleanup_options() {
    // Remove main plugin data
    delete_option('urlcoma_mapper_data');
    
    // For backup options, we'll use a simple approach since we know the pattern
    // This avoids performance issues with wp_load_alloptions()
    for ($i = 0; $i < 50; $i++) {
        $timestamp_start = time() - ($i * 86400); // Check last 50 days
        $timestamp_end = $timestamp_start + 86400;
        
        for ($j = $timestamp_start; $j < $timestamp_end; $j += 3600) { // Check every hour
            $option_name = 'urlcoma_mapper_data_backup_' . $j;
            if (get_option($option_name) !== false) {
                delete_option($option_name);
            }
        }
    }
}

/**
 * Remove any transients that might have been set by the plugin
 */
function urlcoma_cleanup_transients() {
    // Since the current plugin doesn't use transients, we'll keep this simple
    // and only clean up if we find any specific ones we might use in the future
    
    // Common transient names the plugin might use
    $possible_transients = array(
        'urlcoma_cache',
        'urlcoma_settings',
        'urlcoma_validation',
        'urlcoma_export_data'
    );
    
    foreach ($possible_transients as $transient) {
        delete_transient($transient);
    }
}

/**
 * Clean up any user meta data (if plugin stored user-specific settings)
 */
function urlcoma_cleanup_user_meta() {
    // Currently the plugin doesn't store user meta
    // We'll keep this minimal since it's not needed now
    
    // Only clean up if we find specific meta keys we might use
    $possible_meta_keys = array(
        'urlcoma_user_settings',
        'urlcoma_user_preferences'
    );
    
    $users = get_users(array('fields' => 'ID', 'number' => 100)); // Limit to avoid memory issues
    
    foreach ($users as $user_id) {
        foreach ($possible_meta_keys as $meta_key) {
            delete_user_meta($user_id, $meta_key);
        }
    }
}

/**
 * Main cleanup function
 */
function urlcoma_uninstall_cleanup() {
    // Remove plugin options
    urlcoma_cleanup_options();
    
    // Remove any transients
    urlcoma_cleanup_transients();
    
    // Remove any user meta (future-proofing)
    urlcoma_cleanup_user_meta();
    
    // Clear any cached data
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

// Execute the cleanup
urlcoma_uninstall_cleanup();