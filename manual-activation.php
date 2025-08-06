<?php
/**
 * Manual Activation Script
 * Run this to manually insert the default tarot card interpretations
 * Place this file in your WordPress root directory and access it via browser
 */

// Prevent direct access if not in WordPress
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_load_path = dirname(__FILE__) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        echo "WordPress not found. Please place this file in your WordPress root directory.";
        exit;
    }
}

// Include our data file
$plugin_path = dirname(__FILE__) . '/wp-content/plugins/tarot_yes_no_reading/includes/tarot-card-data.php';
if (file_exists($plugin_path)) {
    require_once $plugin_path;
} else {
    echo "Plugin data file not found. Please ensure the plugin is installed.";
    exit;
}

echo "<h1>Tarot Yes/No Reading - Manual Data Insertion</h1>";

// Check if interpretations already exist
$existing_interpretations = get_option('tynr_interpretations', array());
if (!empty($existing_interpretations)) {
    echo "<p><strong>Warning:</strong> Interpretations already exist in the database.</p>";
    echo "<p>Current count: " . count($existing_interpretations) . " interpretation keys</p>";
    
    // Show a sample
    $sample_key = array_keys($existing_interpretations)[0];
    echo "<p>Sample key: " . $sample_key . "</p>";
    
    echo "<p>If you want to reinsert the data, please delete the 'tynr_interpretations' option from the wp_options table first.</p>";
} else {
    echo "<p>No existing interpretations found. Proceeding with insertion...</p>";
    
    try {
        $cards_inserted = Tarot_Card_Data::insert_default_interpretations();
        echo "<p><strong>Success!</strong> Inserted default interpretations for " . $cards_inserted . " cards.</p>";
        
        // Verify the insertion
        $new_interpretations = get_option('tynr_interpretations', array());
        echo "<p>Verified: " . count($new_interpretations) . " interpretation keys now in database.</p>";
        
        // Show a sample
        if (!empty($new_interpretations)) {
            $sample_key = array_keys($new_interpretations)[0];
            echo "<p>Sample key: " . $sample_key . "</p>";
            echo "<p>Sample data structure:</p>";
            echo "<pre>" . print_r($new_interpretations[$sample_key], true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='/wp-admin/plugins.php'>Return to WordPress Admin</a></p>"; 