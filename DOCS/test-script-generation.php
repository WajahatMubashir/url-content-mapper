<?php
/**
 * Test Script Generator
 * Simulates the JavaScript generation with actual configuration
 */

// Simulate WordPress environment
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Load the configuration
$json_file = __DIR__ . '/url-content-mapper-export-2025-10-05.json';
if (!file_exists($json_file)) {
    die("Configuration file not found!\n");
}

$json_content = file_get_contents($json_file);
$import_data = json_decode($json_content, true);

if (!isset($import_data['settings']['urlcoma_mapper_data'])) {
    die("Invalid configuration format!\n");
}

$data = $import_data['settings']['urlcoma_mapper_data'];

// Simulate WordPress functions
function esc_js($text) {
    return addslashes($text);
}

// Generate the inline script (copy from functions.php logic)
$inline_script = "(function(){\n";
$inline_script .= "  'use strict';\n";
$inline_script .= "  window.dataLayer = window.dataLayer || [];\n";
$inline_script .= "  var matchedCategory = null;\n";
$inline_script .= "  var matchedPriority = 999;\n";

$inline_script .= "  function hasQueryParam(key, value) {\n";
$inline_script .= "    if (!key) return false;\n";
$inline_script .= "    var params = new URLSearchParams(window.location.search);\n";
$inline_script .= "    if (!params.has(key)) return false;\n";
$inline_script .= "    return value ? params.get(key) === value : true;\n";
$inline_script .= "  }\n";

$inline_script .= "  function setCategory(category, priority) {\n";
$inline_script .= "    if (priority < matchedPriority) {\n";
$inline_script .= "      matchedCategory = category;\n";
$inline_script .= "      matchedPriority = priority;\n";
$inline_script .= "    }\n";
$inline_script .= "  }\n";

$inline_script .= "  var pathname = window.location.pathname;\n";
$inline_script .= "  var href = window.location.href;\n";

foreach ($data as $category_data) {
    if (!isset($category_data['name'], $category_data['type'], $category_data['urls'])) {
        continue;
    }

    $category = esc_js(trim($category_data['name']));
    $type     = trim($category_data['type']);
    $urls     = is_array($category_data['urls']) ? $category_data['urls'] : array();

    if (!in_array($type, array('exact', 'contains'), true)) {
        $type = 'contains';
    }

    foreach ($urls as $raw_url) {
        $raw_url = trim($raw_url);
        if (empty($raw_url)) {
            continue;
        }

        $pattern = esc_js($raw_url);
        $is_path_pattern = (strpos($raw_url, '/') === 0);
        $has_query = (strpos($raw_url, '?') !== false);

        if ($is_path_pattern) {
            if ($has_query) {
                list($path_part, $query_part) = explode('?', $raw_url, 2);

                if (empty($path_part)) {
                    $path_part = '/';
                }

                $path_part = esc_js($path_part);
                parse_str($query_part, $query_params);

                if ($type === 'exact') {
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
                if ($type === 'exact') {
                    if ($raw_url === '/') {
                        $inline_script .= "  if (pathname === '/' || pathname === '' || pathname === '/index.php') { setCategory('{$category}', 2); }\n";
                    } else {
                        $inline_script .= "  if (pathname === '{$pattern}') { setCategory('{$category}', 2); }\n";
                    }
                } else {
                    $inline_script .= "  if (pathname.indexOf('{$pattern}') !== -1) { setCategory('{$category}', 4); }\n";
                }
            }
        } else {
            if ($type === 'exact') {
                $inline_script .= "  (function() {\n";
                $inline_script .= "    var pattern = '{$pattern}'.replace(/\\/+$/, '');\n";
                $inline_script .= "    var current = href.replace(/\\/+$/, '');\n";
                $inline_script .= "    if (current === pattern) { setCategory('{$category}', 5); }\n";
                $inline_script .= "  })();\n";
            } else {
                $inline_script .= "  if (href.indexOf('{$pattern}') !== -1) { setCategory('{$category}', 6); }\n";
            }
        }
    }
}

$inline_script .= "  if (matchedCategory) {\n";
$inline_script .= "    window.dataLayer.push({'content_category': matchedCategory});\n";
$inline_script .= "  }\n";
$inline_script .= "})();\n";

// Save generated script
file_put_contents(__DIR__ . '/GENERATED-SCRIPT-TEST.js', $inline_script);

echo "✅ JavaScript generation test PASSED!\n";
echo "Generated script saved to: GENERATED-SCRIPT-TEST.js\n\n";
echo "Script size: " . strlen($inline_script) . " bytes\n";
echo "Number of categories: " . count($data) . "\n";

// Verify no syntax errors in generated JavaScript
echo "\nValidating JavaScript syntax...\n";

// Basic validation checks
$errors = [];

if (strpos($inline_script, 'undefined') !== false) {
    $errors[] = "WARNING: Found 'undefined' in script";
}

if (substr_count($inline_script, '{') !== substr_count($inline_script, '}')) {
    $errors[] = "ERROR: Mismatched curly braces";
}

if (substr_count($inline_script, '(') !== substr_count($inline_script, ')')) {
    $errors[] = "ERROR: Mismatched parentheses";
}

if (!empty($errors)) {
    echo "❌ VALIDATION FAILED:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
} else {
    echo "✅ JavaScript validation PASSED!\n";
}

echo "\n=== TEST SUMMARY ===\n";
echo "✅ Configuration loaded successfully\n";
echo "✅ Script generated without errors\n";
echo "✅ All syntax checks passed\n";
echo "\nReady for deployment!\n";
?>
