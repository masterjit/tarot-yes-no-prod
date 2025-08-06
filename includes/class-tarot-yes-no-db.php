<?php
/**
 * Database Helper Class for Tarot Yes/No Reading Plugin
 */

class Tarot_Yes_No_DB {
    
    /**
     * Get a setting value from the database
     */
    public static function get_setting($key, $default = null) {
        global $wpdb;
        $table_settings = $wpdb->prefix . 'tynr_settings';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_settings'") == $table_settings;
        
        if (!$table_exists) {
            error_log('TYNR Debug - Settings table does not exist, returning default for: ' . $key);
            return $default;
        }
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_settings WHERE setting_key = %s",
            $key
        ));
        
        if ($value === null) {
            error_log('TYNR Debug - Setting not found: ' . $key . ', returning default: ' . $default);
            return $default;
        }
        
        // Try to decode JSON if it's an array
        $decoded = json_decode($value, true);
        $result = $decoded !== null ? $decoded : $value;
        
        error_log('TYNR Debug - Setting found: ' . $key . ' = ' . $result);
        
        return $result;
    }
    
    /**
     * Set a setting value in the database
     */
    public static function set_setting($key, $value) {
        global $wpdb;
        $table_settings = $wpdb->prefix . 'tynr_settings';
        
        $value_to_store = is_array($value) ? json_encode($value) : $value;
        
        return $wpdb->replace(
            $table_settings,
            array(
                'setting_key' => $key,
                'setting_value' => $value_to_store
            ),
            array('%s', '%s')
        );
    }
    
    /**
     * Get all settings as an array
     */
    public static function get_all_settings() {
        global $wpdb;
        $table_settings = $wpdb->prefix . 'tynr_settings';
        
        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM $table_settings");
        
        $settings = array();
        foreach ($results as $row) {
            $decoded = json_decode($row->setting_value, true);
            $settings[$row->setting_key] = $decoded !== null ? $decoded : $row->setting_value;
        }
        
        return $settings;
    }
    
    /**
     * Get back images from the database
     */
    public static function get_back_images() {
        // Get from settings
        $back_image_url = self::get_setting('back_image_url', '');
        $back_image_name = self::get_setting('back_image_name', 'Back Card');
        $num_cards_to_show = self::get_setting('num_cards_to_show', 3);
        
        if (!empty($back_image_url)) {
            error_log('TYNR Debug - Found back image in settings: ' . $back_image_url);
            
            // Create multiple images based on num_cards_to_show
            $images = array();
            for ($i = 0; $i < $num_cards_to_show; $i++) {
                $images[] = array(
                    'id' => $i + 1,
                    'image_url' => $back_image_url,
                    'image_name' => $back_image_name . ' ' . ($i + 1),
                    'is_active' => 1,
                    'sort_order' => $i + 1
                );
            }
            
            error_log('TYNR Debug - Returning ' . count($images) . ' back images from settings');
            return $images;
        }
        
        error_log('TYNR Debug - No back image found in settings');
        return array();
    }
    

    
    /**
     * Get interpretations for a specific card and orientation
     */
    public static function get_interpretations($card_id, $orientation = null) {
        global $wpdb;
        $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
        
        $where_clause = "WHERE card_id = %d";
        $params = array($card_id);
        
        if ($orientation) {
            $where_clause .= " AND orientation = %s";
            $params[] = $orientation;
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_interpretations $where_clause ORDER BY category ASC",
            $params
        ));
        
        $interpretations = array();
        foreach ($results as $row) {
            if (!isset($interpretations[$row->orientation])) {
                $interpretations[$row->orientation] = array();
            }
            $interpretations[$row->orientation][$row->category] = $row->interpretation;
        }
        
        return $interpretations;
    }
    
    /**
     * Save interpretations for a card
     */
    public static function save_interpretations($card_id, $interpretations) {
        global $wpdb;
        $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
        
        // First, delete existing interpretations for this card
        $wpdb->delete(
            $table_interpretations,
            array('card_id' => $card_id),
            array('%d')
        );
        
        $inserted = 0;
        
        foreach (['upright', 'reversed'] as $orientation) {
            if (isset($interpretations[$orientation])) {
                foreach ($interpretations[$orientation] as $category => $interpretation) {
                    $result = $wpdb->insert(
                        $table_interpretations,
                        array(
                            'card_id' => $card_id,
                            'orientation' => $orientation,
                            'category' => $category,
                            'interpretation' => $interpretation
                        ),
                        array('%d', '%s', '%s', '%s')
                    );
                    
                    if ($result) {
                        $inserted++;
                    }
                }
            }
        }
        
        return $inserted;
    }
    
    /**
     * Get all cards with their interpretations
     */
    public static function get_all_interpretations() {
        global $wpdb;
        $table_interpretations = $wpdb->prefix . 'tynr_interpretations';
        
        $results = $wpdb->get_results(
            "SELECT * FROM $table_interpretations ORDER BY card_id ASC, orientation ASC, category ASC"
        );
        
        $interpretations = array();
        foreach ($results as $row) {
            $key = $row->card_id . '_' . $row->orientation;
            if (!isset($interpretations[$key])) {
                $interpretations[$key] = array();
            }
            $interpretations[$key][$row->category] = $row->interpretation;
        }
        
        return $interpretations;
    }
    
    /**
     * Get random cards for a reading
     */
    public static function get_random_cards($num_cards) {
        global $wpdb;
        
        // Get random cards from the existing table
        $cards = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM wp_ac_three_tarot_cards 
            ORDER BY RAND() 
            LIMIT %d
        ", $num_cards));
        
        return $cards;
    }
} 