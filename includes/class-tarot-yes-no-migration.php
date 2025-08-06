<?php
/**
 * Migration Class for Tarot Yes/No Reading Plugin
 * Handles migration from wp_options to custom database tables
 */

class Tarot_Yes_No_Migration {
    
    /**
     * Check if migration is needed
     */
    public static function needs_migration() {
        // Check if old options exist
        $old_settings = get_option('tynr_settings');
        $old_interpretations = get_option('tynr_interpretations');
        
        return !empty($old_settings) || !empty($old_interpretations);
    }
    
    /**
     * Perform migration from wp_options to database tables
     */
    public static function migrate() {
        global $wpdb;
        
        // Migrate settings
        self::migrate_settings();
        
        // Migrate interpretations
        self::migrate_interpretations();
        
        // Clean up old options
        self::cleanup_old_options();
        
        return true;
    }
    
    /**
     * Migrate settings from wp_options to database
     */
    private static function migrate_settings() {
        $old_settings = get_option('tynr_settings', array());
        
        if (!empty($old_settings)) {
            foreach ($old_settings as $key => $value) {
                Tarot_Yes_No_DB::set_setting($key, $value);
            }
        }
        
        // Migrate back image settings
        $old_back_image_settings = get_option('tynr_back_image_settings', array());
        if (!empty($old_back_image_settings)) {
            foreach ($old_back_image_settings as $key => $value) {
                Tarot_Yes_No_DB::set_setting($key, $value);
            }
        }
    }
    

    
    /**
     * Migrate interpretations from wp_options to database
     */
    private static function migrate_interpretations() {
        $old_interpretations = get_option('tynr_interpretations', array());
        
        if (!empty($old_interpretations)) {
            global $wpdb;
            $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
            
            foreach ($old_interpretations as $key => $interpretations) {
                // Parse key format: card_id_orientation
                $parts = explode('_', $key);
                if (count($parts) >= 2) {
                    $card_id = intval($parts[0]);
                    $orientation = $parts[1];
                    
                    if (is_array($interpretations)) {
                        foreach ($interpretations as $category => $interpretation) {
                            $wpdb->insert(
                                $table_interpretations,
                                array(
                                    'card_id' => $card_id,
                                    'orientation' => $orientation,
                                    'category' => $category,
                                    'interpretation' => $interpretation
                                ),
                                array('%d', '%s', '%s', '%s')
                            );
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Clean up old options after migration
     */
    private static function cleanup_old_options() {
        delete_option('tynr_settings');
        delete_option('tynr_back_image_settings');
        delete_option('tynr_interpretations');
    }
    
    /**
     * Get migration status
     */
    public static function get_migration_status() {
        $needs_migration = self::needs_migration();
        $tables_exist = self::check_tables_exist();
        
        return array(
            'needs_migration' => $needs_migration,
            'tables_exist' => $tables_exist,
            'can_migrate' => $needs_migration && $tables_exist
        );
    }
    
    /**
     * Check if required tables exist
     */
    private static function check_tables_exist() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'tynr_settings',
            $wpdb->prefix . 'tynr_interpretations'
        );
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                return false;
            }
        }
        
        return true;
    }
} 