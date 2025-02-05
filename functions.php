<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct file access
}

function inject_url_mapper_code() {
	$data = get_option( 'url_mapper_data', [] );

	if ( ! is_array( $data ) || empty( $data ) ) {
		// It's fine to output a comment as a fallback
		echo "<!-- URL Mapper: No Data Found -->\n";
		return;
	}

	echo "<script>\n";
	echo "window.dataLayer = window.dataLayer || [];\n";
	echo "document.addEventListener('DOMContentLoaded', function() {\n";

	foreach ( $data as $category_data ) {
		if ( ! isset( $category_data['name'], $category_data['type'], $category_data['urls'] ) ) {
			continue; // Skip invalid categories
		}

		// We'll trim, but NOT esc_js() here, so we can do it inline below
		$category = trim( $category_data['name'] );
		$type     = trim( $category_data['type'] );
		$urls     = is_array( $category_data['urls'] )
			? $category_data['urls']
			: explode( "\n", $category_data['urls'] );

		foreach ( $urls as $raw_url ) {
			$raw_url = trim( $raw_url );
			if ( empty( $raw_url ) ) {
				continue;
			}

			// Handle homepage separately
			if ( '/' === $raw_url && 'exact' === $type ) {
				echo 'if (window.location.pathname === "/" || window.location.pathname === "" || window.location.pathname === "/index.php") {' . "\n";
			} elseif ( 'exact' === $type ) {
				// PHPCS wants to see esc_js() at the time of output
				echo 'if (window.location.href === \'' . esc_js( $raw_url ) . '\') {' . "\n";
			} elseif ( 'contains' === $type ) {
				echo 'if (window.location.href.includes(\'' . esc_js( $raw_url ) . '\')) {' . "\n";
			}

			// Push to dataLayer (again calling esc_js() inline)
			echo 'window.dataLayer.push({"content_category": "' . esc_js( $category ) . '"});' . "\n";
			echo "}\n";
		}
	}

	echo "});\n";
	echo "</script>\n";
}
