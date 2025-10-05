<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create a submenu under "Tools"
 */
function urlcoma_create_menu() {
    add_submenu_page(
        'tools.php',
        'URL Content Mapper',
        'URL Content Mapper',
        'manage_options',
        'url-content-mapper',
        'urlcoma_settings_page'
    );
}
add_action('admin_menu', 'urlcoma_create_menu');

/**
 * Render the settings page
 */
function urlcoma_settings_page() {
    $url_mapper_data = get_option('urlcoma_mapper_data', []);

    ?>
    <div class="wrap">
        <h2>URL Content Mapper</h2>
        
        <!-- Import/Export Section -->
        <div class="urlcoma-import-export-section">
            <h3><?php esc_html_e('Import/Export Settings', 'url-content-mapper'); ?></h3>
            
            <!-- Export Section -->
            <div class="urlcoma-export-section">
                <h4><?php esc_html_e('Export Settings', 'url-content-mapper'); ?></h4>
                <p><?php esc_html_e('Download your current URL Content Mapper settings as a JSON file.', 'url-content-mapper'); ?></p>
                <form method="post" action="" style="display: inline;">
                    <?php wp_nonce_field('urlcoma_export_action', 'urlcoma_export_nonce'); ?>
                    <input type="submit" name="urlcoma_export" class="button button-secondary" value="<?php esc_attr_e('Export Settings', 'url-content-mapper'); ?>" />
                </form>
            </div>
            
            <!-- Import Section -->
            <div class="urlcoma-import-section">
                <h4><?php esc_html_e('Import Settings', 'url-content-mapper'); ?></h4>
                <p><?php esc_html_e('Upload a previously exported JSON file to restore your settings.', 'url-content-mapper'); ?></p>
                <form method="post" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field('urlcoma_import_action', 'urlcoma_import_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="urlcoma_import_file"><?php esc_html_e('Select File', 'url-content-mapper'); ?></label>
                            </th>
                            <td>
                                <input type="file" name="urlcoma_import_file" id="urlcoma_import_file" accept=".json" required />
                                <p class="description"><?php esc_html_e('Select a JSON file exported from URL Content Mapper (Max size: 1MB)', 'url-content-mapper'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="urlcoma_import_mode"><?php esc_html_e('Import Mode', 'url-content-mapper'); ?></label>
                            </th>
                            <td>
                                <select name="urlcoma_import_mode" id="urlcoma_import_mode">
                                    <option value="replace"><?php esc_html_e('Replace existing settings', 'url-content-mapper'); ?></option>
                                    <option value="merge"><?php esc_html_e('Merge with existing settings', 'url-content-mapper'); ?></option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Replace: Completely replace current settings with imported data. Merge: Add imported categories to existing ones.', 'url-content-mapper'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <input type="submit" name="urlcoma_import" class="button button-secondary" value="<?php esc_attr_e('Import Settings', 'url-content-mapper'); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to import these settings? Current settings will be backed up.', 'url-content-mapper')); ?>')" />
                </form>
            </div>
        </div>
        
        <hr />
        
        <!-- Main Settings Form -->
        <h3><?php esc_html_e('URL Content Mapping Configuration', 'url-content-mapper'); ?></h3>
        <form method="post" action="options.php">
            <?php 
                settings_fields('urlcoma_mapper_group'); 
                do_settings_sections('url-content-mapper');
            ?>
            <div id="urlcoma-categories">
                <?php if (!empty($url_mapper_data)) : ?>
                    <?php foreach ($url_mapper_data as $category_id => $category) : ?>
                        <?php 
                        // Ensure each category has a unique ID
                        $category_id = isset($category['category_id']) ? sanitize_text_field($category['category_id']) : uniqid();
                        ?>
                        <div class="urlcoma-category-container" data-category-id="<?php echo esc_attr($category_id); ?>">
                            <input type="hidden" name="urlcoma_mapper_data[<?php echo esc_attr($category_id); ?>][category_id]" value="<?php echo esc_attr($category_id); ?>" />

                            <label>Category Name:</label>
                            <input 
                                type="text" 
                                name="urlcoma_mapper_data[<?php echo esc_attr($category_id); ?>][name]" 
                                value="<?php echo esc_attr($category['name']); ?>" 
                                required
                            />

                            <label>Match Type:</label>
                            <select name="urlcoma_mapper_data[<?php echo esc_attr($category_id); ?>][type]">
                                <option value="exact" <?php selected($category['type'], 'exact'); ?>>Exact</option>
                                <option value="contains" <?php selected($category['type'], 'contains'); ?>>Contains</option>
                            </select>

                            <label>URLs:</label>
                            <div class="urlcoma-url-list">
                                <?php 
                                if (!empty($category['urls']) && is_array($category['urls'])) :
                                    foreach ($category['urls'] as $url_index => $url) :
                                ?>
                                    <div class="urlcoma-url-item">
                                        <input 
                                            type="text" 
                                            name="urlcoma_mapper_data[<?php echo esc_attr($category_id); ?>][urls][<?php echo esc_attr($url_index); ?>]" 
                                            value="<?php echo esc_url($url); ?>" 
                                            required
                                        />
                                        <button type="button" class="urlcoma-remove-url">Remove</button>
                                    </div>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>

                            <button type="button" class="urlcoma-add-url">Add URL</button>
                            <button type="button" class="urlcoma-remove-category">Remove Category</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="urlcoma-addCategory">Add Category</button>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Sanitize callback for 'urlcoma_mapper_data'
 *
 * Important: Does NOT use esc_url_raw() to preserve relative URL patterns
 * like "/products/" and query parameter patterns like "/?wizard=true"
 *
 * @since 1.3
 * @param mixed $input The input data to sanitize
 * @return array Sanitized data array
 */
function urlcoma_data_sanitize($input) {
    if (!is_array($input)) {
        return array();
    }

    $sanitized_data = array();

    foreach ($input as $category_id => $category) {
        if (!is_array($category) || !isset($category['name'], $category['type'], $category['urls'])) {
            continue;
        }

        // Use existing category_id or the key
        if (!isset($category['category_id']) || empty($category['category_id'])) {
            $category_id = sanitize_key($category_id);
        } else {
            $category_id = sanitize_text_field($category['category_id']);
        }

        $sanitized_category = array();
        $sanitized_category['category_id'] = $category_id;
        $sanitized_category['name'] = sanitize_text_field($category['name']);

        // Validate type is either 'exact' or 'contains'
        $type = sanitize_text_field($category['type']);
        $sanitized_category['type'] = in_array($type, array('exact', 'contains'), true) ? $type : 'contains';

        // Sanitize URLs while preserving patterns
        $sanitized_category['urls'] = array();
        if (is_array($category['urls'])) {
            foreach ($category['urls'] as $url) {
                // Trim whitespace
                $url = trim($url);

                // Skip empty URLs
                if (empty($url)) {
                    continue;
                }

                // Remove dangerous characters but preserve URL structure
                // DO NOT use esc_url_raw() as it breaks relative patterns like /products/
                $url = wp_strip_all_tags($url);

                // Remove null bytes and other control characters
                $url = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $url);

                // Store the sanitized URL
                if (!empty($url)) {
                    $sanitized_category['urls'][] = $url;
                }
            }
        }

        // Only add category if it has at least one URL
        if (!empty($sanitized_category['urls'])) {
            $sanitized_data[$category_id] = $sanitized_category;
        }
    }

    return $sanitized_data;
}

/**
 * Additional sanitization wrapper to ensure array input
 */
function urlcoma_ensure_array_sanitize($input) {
    if (!is_array($input)) {
        return array();
    }
    return urlcoma_data_sanitize($input);
}

/**
 * Register the setting with explicit argument structure to prevent dynamic argument warning.
 */
function urlcoma_register_settings() {
    register_setting(
        'urlcoma_mapper_group',
        'urlcoma_mapper_data',
        'urlcoma_ensure_array_sanitize'
    );
}
add_action('admin_init', 'urlcoma_register_settings');
