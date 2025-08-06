<?php
/**
 * Manual Activation Script for Tarot Yes/No Reading Plugin
 * Run this file to activate the plugin and create database tables
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this script.');
}

echo "<h1>Tarot Yes/No Reading Plugin Activation</h1>";

// Include the activator class
require_once plugin_dir_path(__FILE__) . 'includes/class-tarot-yes-no-activator.php';

try {
    // Run activation
    Tarot_Yes_No_Activator::activate();
    
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "<h3>✅ Plugin Activated Successfully!</h3>";
    echo "<p>The following tables have been created:</p>";
    echo "<ul>";
    echo "<li>wp_tynr_settings - Plugin settings</li>";
    echo "<li>wp_tynr_interpretations - Card interpretations</li>";
    echo "</ul>";
    echo "<p>Default data has been inserted into all tables.</p>";
    echo "</div>";
    
    // Check if tables exist
    global $wpdb;
    $tables = array(
        $wpdb->prefix . 'tynr_settings',
        $wpdb->prefix . 'tynr_interpretations'
    );
    
    echo "<h3>Database Tables Status:</h3>";
    foreach ($tables as $table) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
        $status = $exists ? "✅ Exists" : "❌ Missing";
        echo "<p><strong>$table:</strong> $status</p>";
    }
    
    // Check data
    echo "<h3>Data Status:</h3>";
    
    // Check settings
    $settings_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tynr_settings");
    echo "<p><strong>Settings:</strong> $settings_count records</p>";
    

    
    // Check interpretations
    $interpretations_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tynr_interpretations");
    echo "<p><strong>Interpretations:</strong> $interpretations_count records</p>";
    
    echo "<div style='margin-top: 20px; padding: 10px; background: #f0f0f0;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to your WordPress admin panel</li>";
    echo "<li>Navigate to 'Tarot Yes/No' in the admin menu</li>";
    echo "<li>Configure your settings and upload back card images</li>";
    echo "<li>Use the shortcode <code>[tarot_yes_no_reading]</code> on any page</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<h3>❌ Activation Failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<p><a href='" . admin_url() . "'>← Back to WordPress Admin</a></p>";
?> 