<?php
/**
 * Plugin Activation Class
 */

// Include the tarot card data
require_once plugin_dir_path(__FILE__) . 'tarot-card-data.php';

class Tarot_Yes_No_Activator {
    
    public static function activate() {
        self::create_tables();
        self::insert_default_data();
    }
    
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for settings
        $table_settings = $wpdb->prefix . 'tynr_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        

        
        // Table for card interpretations
        $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
        $sql_interpretations = "CREATE TABLE $table_interpretations (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            card_id int(11) NOT NULL,
            orientation enum('upright', 'reversed') NOT NULL,
            category enum('general', 'love', 'money', 'health', 'career') NOT NULL,
            interpretation text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY card_orientation_category (card_id, orientation, category)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_settings);
        dbDelta($sql_interpretations);
    }
    
    private static function insert_default_data() {
        self::insert_default_settings();
        self::insert_default_interpretations();
    }
    
    private static function insert_default_settings() {
        global $wpdb;
        
        $table_settings = $wpdb->prefix . 'tynr_settings';
        
        $default_settings = array(
            'num_cards' => 3,
            'enable_animations' => 1,
            'show_card_names' => 1,
            'back_image_url' => TYNR_PLUGIN_URL . 'assets/images/back-1.jpg',
            'back_image_name' => 'Classic Back',
            'num_cards_to_show' => 3
        );
        
        foreach ($default_settings as $key => $value) {
            $wpdb->replace(
                $table_settings,
                array(
                    'setting_key' => $key,
                    'setting_value' => is_array($value) ? json_encode($value) : $value
                ),
                array('%s', '%s')
            );
        }
    }
    

    
    private static function insert_default_interpretations() {
        error_log('TYNR: Starting insert_default_interpretations');
        
        // Only insert if no interpretations exist yet
        global $wpdb;
        $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_interpretations");
        
        if ($count == 0) {
            error_log('TYNR: No existing interpretations found, inserting defaults');
            $cards_inserted = Tarot_Card_Data::insert_default_interpretations();
            error_log('TYNR: Inserted default interpretations for ' . $cards_inserted . ' cards');
        } else {
            error_log('TYNR: Interpretations already exist, skipping insertion');
        }
    }
} 