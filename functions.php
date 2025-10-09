<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct file access
}

/**
 * Enqueue Frontend JavaScript for DataLayer Injection
 *
 * Key changes for GA4 compatibility:
 * - Loads in HEAD (not footer) to execute before GA4/GTM
 * - Executes immediately (no DOMContentLoaded) to set content_category before page_view event
 * - Uses pathname-based matching for path patterns
 * - Properly handles query parameters in URL patterns
 *
 * @since 1.3
 */
function urlcoma_enqueue_script() {
    wp_enqueue_script(
        'urlcoma-frontend-script',
        URLCOMA_PLUGIN_URL . 'assets/frontend-script.js',
        array(),
        '1.3',
        false // Load in HEAD (not footer) - Critical for GA4 timing
    );

    $data = get_option('urlcoma_mapper_data', array());

    if (!is_array($data) || empty($data)) {
        return;
    }

    // Build inline script that executes immediately (before GA4)
    $inline_script = "(function(){\n";
    $inline_script .= "  'use strict';\n";
    $inline_script .= "  window.dataLayer = window.dataLayer || [];\n";
    $inline_script .= "  var matchedCategory = null;\n";
    $inline_script .= "  var matchedPriority = 999;\n"; // Lower number = higher priority

    // Helper function to check query parameters
    $inline_script .= "  function hasQueryParam(key, value) {\n";
    $inline_script .= "    if (!key) return false;\n";
    $inline_script .= "    var params = new URLSearchParams(window.location.search);\n";
    $inline_script .= "    if (!params.has(key)) return false;\n";
    $inline_script .= "    return value ? params.get(key) === value : true;\n";
    $inline_script .= "  }\n";

    // Helper function to set category (only if higher priority)
    $inline_script .= "  function setCategory(category, priority) {\n";
    $inline_script .= "    if (priority < matchedPriority) {\n";
    $inline_script .= "      matchedCategory = category;\n";
    $inline_script .= "      matchedPriority = priority;\n";
    $inline_script .= "    }\n";
    $inline_script .= "  }\n";

    $inline_script .= "  var pathname = window.location.pathname;\n";
    $inline_script .= "  var href = window.location.href;\n";

    // Priority levels:
    // 1 = Exact path with exact query params (most specific)
    // 2 = Exact path (no query)
    // 3 = Contains path with query params
    // 4 = Contains path (no query)
    // 5 = Exact URL
    // 6 = Contains URL

    foreach ($data as $category_data) {
        if (!isset($category_data['name'], $category_data['type'], $category_data['urls'])) {
            continue;
        }

        $category = esc_js(trim($category_data['name']));
        $type     = trim($category_data['type']);
        $urls     = is_array($category_data['urls']) ? $category_data['urls'] : array();

        // Validate type
        if (!in_array($type, array('exact', 'contains'), true)) {
            $type = 'contains';
        }

        foreach ($urls as $raw_url) {
            $raw_url = trim($raw_url);
            if (empty($raw_url)) {
                continue;
            }

            $pattern = esc_js($raw_url);

            // Determine if pattern is path-based (starts with /) or full URL
            $is_path_pattern = (strpos($raw_url, '/') === 0);

            // Check if pattern contains query parameters
            $has_query = (strpos($raw_url, '?') !== false);

            if ($is_path_pattern) {
                // Path-based matching
                if ($has_query) {
                    // Pattern has query parameters (e.g., "/?wizard=true" or "/products/?ref=ad")
                    list($path_part, $query_part) = explode('?', $raw_url, 2);

                    // Handle empty path (just "/?" means homepage with any query)
                    if (empty($path_part)) {
                        $path_part = '/';
                    }

                    $path_part = esc_js($path_part);

                    // Trim whitespace from query part to handle edge cases
                    $query_part = trim($query_part);

                    // Parse query parameters
                    parse_str($query_part, $query_params);

                    // Handle special case: "/?" or "/path/?" means "match when path has ANY query parameters"
                    // This is when the pattern ends with "?" but has no actual query params after it
                    if (empty($query_params) && $query_part === '') {
                        // This is just "path/?" - match when path matches AND has any query string
                        if ($type === 'exact') {
                            $inline_script .= "  if (pathname === '{$path_part}' && window.location.search !== '') { setCategory('{$category}', 1); }\n";
                        } else {
                            $inline_script .= "  if (pathname.indexOf('{$path_part}') !== -1 && window.location.search !== '') { setCategory('{$category}', 3); }\n";
                        }
                        continue; // Skip to next URL pattern
                    }

                    // Handle malformed query strings (e.g., "/?foo" or "/?=value")
                    // These should be skipped as invalid patterns
                    if (!empty($query_params)) {
                        $has_valid_params = false;
                        foreach ($query_params as $key => $value) {
                            // Valid param must have a non-empty key
                            if (!empty($key) || $key === '0') {
                                $has_valid_params = true;
                                break;
                            }
                        }

                        if (!$has_valid_params) {
                            // Skip this invalid pattern (e.g., "/?=value" results in empty key)
                            continue;
                        }
                    } else {
                        // If parse_str produced no params and query_part isn't empty, it's malformed
                        // Skip this invalid pattern (e.g., "/??" or "/?#")
                        continue;
                    }

                    if ($type === 'exact') {
                        // Exact path match + all query params must match (Priority 1 - most specific)
                        $inline_script .= "  if (pathname === '{$path_part}'";
                        foreach ($query_params as $key => $value) {
                            $key = esc_js($key);
                            $value = esc_js($value);
                            if (!empty($value)) {
                                $inline_script .= " && hasQueryParam('{$key}', '{$value}')";
                            } else {
                                $inline_script .= " && hasQueryParam('{$key}')";
                            }
                        }
                        $inline_script .= ") { setCategory('{$category}', 1); }\n";
                    } else {
                        // Contains: path contains + all query params must match (Priority 3)
                        $inline_script .= "  if (pathname.indexOf('{$path_part}') !== -1";
                        foreach ($query_params as $key => $value) {
                            $key = esc_js($key);
                            $value = esc_js($value);
                            if (!empty($value)) {
                                $inline_script .= " && hasQueryParam('{$key}', '{$value}')";
                            } else {
                                $inline_script .= " && hasQueryParam('{$key}')";
                            }
                        }
                        $inline_script .= ") { setCategory('{$category}', 3); }\n";
                    }
                } else {
                    // Pure path pattern (no query params)
                    if ($type === 'exact') {
                        // Special handling for homepage (Priority 2)
                        if ($raw_url === '/') {
                            $inline_script .= "  if (pathname === '/' || pathname === '' || pathname === '/index.php') { setCategory('{$category}', 2); }\n";
                        } else {
                            $inline_script .= "  if (pathname === '{$pattern}') { setCategory('{$category}', 2); }\n";
                        }
                    } else {
                        // Contains match (Priority 4)
                        $inline_script .= "  if (pathname.indexOf('{$pattern}') !== -1) { setCategory('{$category}', 4); }\n";
                    }
                }
            } else {
                // Full URL matching (for backwards compatibility)
                if ($type === 'exact') {
                    // Normalize trailing slashes for comparison (Priority 5)
                    $inline_script .= "  (function() {\n";
                    $inline_script .= "    var pattern = '{$pattern}'.replace(/\\/+$/, '');\n";
                    $inline_script .= "    var current = href.replace(/\\/+$/, '');\n";
                    $inline_script .= "    if (current === pattern) { setCategory('{$category}', 5); }\n";
                    $inline_script .= "  })();\n";
                } else {
                    // Contains match (Priority 6)
                    $inline_script .= "  if (href.indexOf('{$pattern}') !== -1) { setCategory('{$category}', 6); }\n";
                }
            }
        }
    }

    // Push the matched category to dataLayer (only if we have a matched category)
    $inline_script .= "  if (matchedCategory) {\n";
    $inline_script .= "    window.dataLayer.push({'content_category': matchedCategory});\n";
    $inline_script .= "  }\n";
    $inline_script .= "})();\n";

    wp_add_inline_script('urlcoma-frontend-script', $inline_script, 'before');
}
// Priority 1 ensures this runs early, before other scripts
add_action('wp_enqueue_scripts', 'urlcoma_enqueue_script', 1);

/**
 * Export Settings Functionality
 */
function urlcoma_export_settings() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'url-content-mapper'));
    }

    // Verify nonce
    if (!isset($_POST['urlcoma_export_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['urlcoma_export_nonce'])), 'urlcoma_export_action')) {
        wp_die(esc_html__('Security check failed.', 'url-content-mapper'));
    }

    $data = get_option('urlcoma_mapper_data', []);
    
    $export_data = array(
        'plugin' => 'url-content-mapper',
        'version' => '1.2',
        'export_date' => current_time('c'),
        'settings' => array(
            'urlcoma_mapper_data' => $data
        )
    );

    $filename = 'url-content-mapper-export-' . gmdate('Y-m-d') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Handle Export Request
 */
function urlcoma_handle_export() {
    if (isset($_POST['urlcoma_export']) && isset($_POST['urlcoma_export_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['urlcoma_export_nonce'])), 'urlcoma_export_action')) {
        urlcoma_export_settings();
    }
}
add_action('admin_init', 'urlcoma_handle_export');

/**
 * Validate Import Data
 */
function urlcoma_validate_import_data($data) {
    $errors = array();

    // Check if data is valid JSON
    if (!is_array($data)) {
        $errors[] = __('Invalid JSON format.', 'url-content-mapper');
        return $errors;
    }

    // Check required fields
    if (!isset($data['plugin']) || $data['plugin'] !== 'url-content-mapper') {
        $errors[] = __('This file is not a valid URL Content Mapper export.', 'url-content-mapper');
    }

    if (!isset($data['version'])) {
        $errors[] = __('Export file is missing version information.', 'url-content-mapper');
    }

    if (!isset($data['settings']) || !isset($data['settings']['urlcoma_mapper_data'])) {
        $errors[] = __('Export file is missing settings data.', 'url-content-mapper');
    }

    // Validate settings structure
    if (empty($errors) && isset($data['settings']['urlcoma_mapper_data'])) {
        $settings_data = $data['settings']['urlcoma_mapper_data'];
        if (!is_array($settings_data)) {
            $errors[] = __('Invalid settings data format.', 'url-content-mapper');
        }
    }

    return $errors;
}

/**
 * Import Settings Functionality
 */
function urlcoma_import_settings($import_data, $import_mode = 'replace') {
    // Validate import data
    $validation_errors = urlcoma_validate_import_data($import_data);
    if (!empty($validation_errors)) {
        return array('success' => false, 'errors' => $validation_errors);
    }

    $new_data = $import_data['settings']['urlcoma_mapper_data'];
    
    // Sanitize the imported data
    $sanitized_data = urlcoma_data_sanitize($new_data);

    if ($import_mode === 'merge') {
        // Merge with existing data
        $existing_data = get_option('urlcoma_mapper_data', []);
        $merged_data = array_merge($existing_data, $sanitized_data);
        $final_data = $merged_data;
    } else {
        // Replace existing data
        $final_data = $sanitized_data;
    }

    // Backup current settings before import
    $backup_data = get_option('urlcoma_mapper_data', []);
    update_option('urlcoma_mapper_data_backup_' . time(), $backup_data);

    // Update with new data
    $result = update_option('urlcoma_mapper_data', $final_data);
    
    // Check if the data was actually saved by comparing what's in the database
    $saved_data = get_option('urlcoma_mapper_data', array());
    
    // update_option returns false if data is identical, so we need to check if data exists
    if (is_array($saved_data) && count($saved_data) > 0) {
        return array(
            'success' => true, 
            // translators: %d is the number of imported categories
            'message' => sprintf(__('Successfully imported %d categories.', 'url-content-mapper'), count($final_data))
        );
    } else {
        return array(
            'success' => false, 
            'errors' => array(__('Failed to save imported data.', 'url-content-mapper'))
        );
    }
}

/**
 * Handle Import Request
 */
function urlcoma_handle_import() {
    if (!isset($_POST['urlcoma_import']) || !isset($_POST['urlcoma_import_nonce'])) {
        return;
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('You do not have sufficient permissions to import settings.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['urlcoma_import_nonce'])), 'urlcoma_import_action')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    // Check if file was uploaded
    if (!isset($_FILES['urlcoma_import_file']) || !isset($_FILES['urlcoma_import_file']['error']) || $_FILES['urlcoma_import_file']['error'] !== UPLOAD_ERR_OK) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('Please select a valid file to import.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    $file = array(
        'name' => isset($_FILES['urlcoma_import_file']['name']) ? sanitize_file_name($_FILES['urlcoma_import_file']['name']) : '',
        'tmp_name' => isset($_FILES['urlcoma_import_file']['tmp_name']) ? sanitize_text_field($_FILES['urlcoma_import_file']['tmp_name']) : '',
        'size' => isset($_FILES['urlcoma_import_file']['size']) ? absint($_FILES['urlcoma_import_file']['size']) : 0,
        'error' => isset($_FILES['urlcoma_import_file']['error']) ? absint($_FILES['urlcoma_import_file']['error']) : UPLOAD_ERR_NO_FILE
    );
    
    // Validate file type
    $file_info = pathinfo($file['name']);
    if (strtolower($file_info['extension']) !== 'json') {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('Please upload a JSON file.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    // Check file size (limit to 1MB)
    if ($file['size'] > 1048576) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('File size is too large. Maximum size is 1MB.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    // Read and decode file content
    $file_content = file_get_contents($file['tmp_name']);
    $import_data = json_decode($file_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid JSON file format.', 'url-content-mapper') . '</p></div>';
        });
        return;
    }

    // Get import mode
    $import_mode = isset($_POST['urlcoma_import_mode']) ? sanitize_text_field(wp_unslash($_POST['urlcoma_import_mode'])) : 'replace';

    // Perform import
    $result = urlcoma_import_settings($import_data, $import_mode);

    if ($result['success']) {
        add_action('admin_notices', function() use ($result) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        });
    } else {
        add_action('admin_notices', function() use ($result) {
            $error_message = implode('<br>', array_map('esc_html', $result['errors']));
            echo '<div class="notice notice-error"><p>' . wp_kses_post($error_message) . '</p></div>';
        });
    }
}
add_action('admin_init', 'urlcoma_handle_import');
