<?php
/**
 * Plugin Name: Tarot Yes/No Reading
 * Description: Interactive tarot yes/no reading system with card selection and interpretations
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: tarot-yes-no
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TYNR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TYNR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TYNR_VERSION', '1.0.0');

// Include required files
require_once TYNR_PLUGIN_PATH . 'includes/class-tarot-yes-no.php';
require_once TYNR_PLUGIN_PATH . 'includes/class-tarot-yes-no-db.php';
require_once TYNR_PLUGIN_PATH . 'includes/class-tarot-yes-no-migration.php';

// Initialize the plugin
function tynr_init() {
    new Tarot_Yes_No();
}
add_action('plugins_loaded', 'tynr_init');

// Activation hook
register_activation_hook(__FILE__, 'tynr_activate');
function tynr_activate() {
    // Create database tables
    require_once TYNR_PLUGIN_PATH . 'includes/class-tarot-yes-no-activator.php';
    Tarot_Yes_No_Activator::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'tynr_deactivate');
function tynr_deactivate() {
    // Cleanup if needed
} 