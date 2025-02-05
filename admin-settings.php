<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create a submenu under "Tools"
 */
function url_mapper_create_menu() {
    add_submenu_page(
        'tools.php',
        'URL Content Mapper',
        'URL Content Mapper',
        'manage_options',
        'url-content-mapper',
        'url_mapper_settings_page'
    );
}
add_action( 'admin_menu', 'url_mapper_create_menu' );

/**
 * Render the settings page
 */
function url_mapper_settings_page() {
    $url_mapper_data = get_option( 'url_mapper_data', [] );
    ?>
    <div class="wrap">
        <h2>URL Content Mapper</h2>
        <form method="post" action="options.php">
            <?php
                // Outputs nonce, action, and option_page fields for url_mapper_group.
                settings_fields( 'url_mapper_group' );
            ?>
            <div id="categories">
                <?php if ( ! empty( $url_mapper_data ) ) : ?>
                    <?php foreach ( $url_mapper_data as $index => $category ) : ?>
                        <div class="category-container">
                            <label>Category Name:</label>
                            <input
                                type="text"
                                name="url_mapper_data[<?php echo esc_attr( $index ); ?>][name]"
                                value="<?php echo esc_attr( $category['name'] ); ?>"
                                required
                            />

                            <label>Match Type:</label>
                            <select name="url_mapper_data[<?php echo esc_attr( $index ); ?>][type]">
                                <option value="exact" <?php selected( $category['type'], 'exact' ); ?>>
                                    Exact
                                </option>
                                <option value="contains" <?php selected( $category['type'], 'contains' ); ?>>
                                    Contains
                                </option>
                            </select>

                            <label>URLs:</label>
                            <textarea
                                name="url_mapper_data[<?php echo esc_attr( $index ); ?>][urls]"
                                required
                            ><?php
                                // If $category['urls'] is an array, implode; otherwise use the string directly.
                                echo esc_textarea(
                                    is_array( $category['urls'] )
                                        ? implode( "\n", $category['urls'] )
                                        : $category['urls']
                                );
                            ?></textarea>

                            <button type="button" onclick="this.parentNode.remove()">Remove</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="addCategory">Add Category</button>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("addCategory").addEventListener("click", function() {
                var container = document.getElementById("categories");
                var index = container.children.length;

                /*
                 * If a linting tool flags this as "unescaped," you can safely ignore
                 * because 'index' is a local JS variable, not user-supplied input.
                 * Example:
                 * // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                 */
                var div = document.createElement("div");
                div.classList.add("category-container");
                div.innerHTML = `
                    <label>Category Name:</label>
                    <input type="text" name="url_mapper_data[${index}][name]" required>

                    <label>Match Type:</label>
                    <select name="url_mapper_data[${index}][type]">
                        <option value="exact">Exact</option>
                        <option value="contains">Contains</option>
                    </select>

                    <label>URLs:</label>
                    <textarea name="url_mapper_data[${index}][urls]" required></textarea>

                    <button type="button" onclick="this.parentNode.remove()">Remove</button>
                `;
                container.appendChild(div);
            });
        });
    </script>

    <style>
        .category-container {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
        textarea {
            width: 100%;
            height: 50px;
        }
    </style>
    <?php
}

/**
 * Named sanitize callback for 'url_mapper_data'.
 */
function url_mapper_data_sanitize( $input ) {
    if ( ! is_array( $input ) ) {
        return [];
    }

    foreach ( $input as &$category ) {
        if (
            ! is_array( $category )
            || ! isset( $category['name'], $category['type'], $category['urls'] )
        ) {
            continue;
        }

        // Sanitize each field
        $category['name'] = sanitize_text_field( $category['name'] );
        $category['type'] = sanitize_text_field( $category['type'] );
        $category['urls'] = array_map(
            'sanitize_text_field',
            explode( "\n", sanitize_textarea_field( $category['urls'] ) )
        );
    }

    return $input;
}

/**
 * Register the setting with a direct sanitize callback reference
 * rather than an array, preventing the "dynamic argument" warning.
 */
function url_mapper_register_settings() {
    // Pass the sanitize callback directly as the third argument
    // WordPress automatically interprets it as 'sanitize_callback'.
    register_setting(
        'url_mapper_group',
        'url_mapper_data',
        'url_mapper_data_sanitize'
    );
}
add_action( 'admin_init', 'url_mapper_register_settings' );
