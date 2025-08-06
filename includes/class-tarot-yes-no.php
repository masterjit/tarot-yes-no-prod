<?php
/**
 * Main Tarot Yes/No Reading Plugin Class
 */
class Tarot_Yes_No {
    
    public function __construct() {
        $this->init_hooks();
        $this->check_migration();
    }
    
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('admin_init', array($this, 'register_settings'));
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('wp_ajax_tynr_get_reading', array($this, 'ajax_get_reading'));
        add_action('wp_ajax_nopriv_tynr_get_reading', array($this, 'ajax_get_reading'));
        
        // Admin AJAX handlers
        add_action('wp_ajax_tynr_get_interpretations', array($this, 'ajax_get_interpretations'));
        add_action('wp_ajax_tynr_save_interpretations', array($this, 'ajax_save_interpretations'));
        
        // Shortcode
        add_shortcode('tarot_yes_no_reading', array($this, 'reading_shortcode'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Tarot Yes/No Reading',
            'Tarot Yes/No',
            'manage_options',
            'tarot-yes-no',
            array($this, 'admin_page'),
            'dashicons-games',
            30
        );
    }
    
    public function admin_page() {
        require_once TYNR_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function admin_scripts($hook) {
        if ($hook !== 'toplevel_page_tarot-yes-no') {
            return;
        }
        
        // Enqueue WordPress media library
        wp_enqueue_media();
        
        // Enqueue our admin script with proper dependencies
        wp_enqueue_script('tynr-admin', TYNR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'media-upload'), TYNR_VERSION, true);
        wp_enqueue_style('tynr-admin', TYNR_PLUGIN_URL . 'assets/css/admin.css', array(), TYNR_VERSION);
        
        wp_localize_script('tynr-admin', 'tynr_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tynr_nonce')
        ));
    }
    
    public function frontend_scripts() {
        wp_enqueue_script('tynr-frontend', TYNR_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), TYNR_VERSION . '.' . time(), true);
        wp_enqueue_style('tynr-frontend', TYNR_PLUGIN_URL . 'assets/css/frontend.css', array(), TYNR_VERSION);
        
        wp_localize_script('tynr-frontend', 'tynr_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tynr_nonce')
        ));
    }
    
    public function reading_shortcode($atts) {
        $num_cards = Tarot_Yes_No_DB::get_setting('num_cards', 3);
        
        // Get back images from database
        $back_images = Tarot_Yes_No_DB::get_back_images();
        
        // If no back images found, create a default one
        if (empty($back_images)) {
            $back_images = array(
                array(
                    'id' => 1,
                    'image_url' => TYNR_PLUGIN_URL . 'assets/images/back-1.jpg',
                    'image_name' => 'Default Back Card',
                    'is_active' => 1,
                    'sort_order' => 1
                )
            );
        }
        
        // Debug logging
        error_log('TYNR Debug - Shortcode called with num_cards: ' . $num_cards);
        error_log('TYNR Debug - Back images count: ' . count($back_images));
        error_log('TYNR Debug - Back images: ' . print_r($back_images, true));
        
        ob_start();
        include TYNR_PLUGIN_PATH . 'templates/reading-interface.php';
        return ob_get_clean();
    }
    
    public function ajax_get_reading() {
        check_ajax_referer('tynr_nonce', 'nonce');
        
        $num_cards = isset($_POST['num_cards']) ? intval($_POST['num_cards']) : 3;
        
        // Debug logging
        error_log('TYNR Debug - AJAX get_reading called with num_cards: ' . $num_cards);
        
        $reading = $this->generate_reading($num_cards);
        
        error_log('TYNR Debug - Generated reading with ' . count($reading) . ' cards');
        
        wp_send_json_success($reading);
    }
    
    private function generate_reading($num_cards) {
        // Get random cards from the existing table
        $cards = Tarot_Yes_No_DB::get_random_cards($num_cards);
        
        $reading = array();
        foreach ($cards as $card) {
            $is_reversed = rand(0, 1) == 1;
            $answer = $this->get_card_answer($card->card_name, $is_reversed);
            
            $reading[] = array(
                'name' => $card->card_name,
                'image' => $card->card_image,
                'is_reversed' => $is_reversed,
                'answer' => $answer,
                'interpretations' => $this->get_interpretations($card->id, $is_reversed)
            );
        }
        
        return $reading;
    }
    
    private function get_card_answer($card_name, $is_reversed) {
        // Simple logic for Yes/No/Maybe based on card and orientation
        $answers = array('Yes', 'No', 'Maybe');
        $index = array_search($card_name, array_keys($this->get_card_answers_map()));
        
        if ($is_reversed) {
            $index = ($index + 1) % 3; // Shift answer for reversed cards
        }
        
        return $answers[$index % 3];
    }
    
    private function get_card_answers_map() {
        return array(
            'The Fool' => 'Yes',
            'The Magician' => 'Yes',
            'The High Priestess' => 'Maybe',
            'The Empress' => 'Yes',
            'The Emperor' => 'Yes',
            'The Hierophant' => 'No',
            'The Lovers' => 'Yes',
            'The Chariot' => 'Yes',
            'Strength' => 'Yes',
            'The Hermit' => 'No',
            'Wheel of Fortune' => 'Maybe',
            'Justice' => 'Yes',
            'The Hanged Man' => 'Maybe',
            'Death' => 'No',
            'Temperance' => 'Yes',
            'The Devil' => 'No',
            'The Tower' => 'No',
            'The Star' => 'Yes',
            'The Moon' => 'Maybe',
            'The Sun' => 'Yes',
            'Judgement' => 'Yes',
            'The World' => 'Yes'
        );
    }
    
    private function get_interpretations($card_id, $is_reversed) {
        $orientation = $is_reversed ? 'reversed' : 'upright';
        $interpretations = Tarot_Yes_No_DB::get_interpretations($card_id, $orientation);
        
        if (isset($interpretations[$orientation])) {
            return $interpretations[$orientation];
        }
        
        // Return default interpretations if none set
        return array(
            'general' => 'This card represents your current situation.',
            'love' => 'In matters of love, this card suggests...',
            'money' => 'Financially, this card indicates...',
            'health' => 'Regarding health, this card shows...',
            'career' => 'In your career, this card means...'
        );
    }
    
    public function register_settings() {
        // Settings are now managed through the database helper class
        // No need to register WordPress options
    }
    
    public function ajax_get_interpretations() {
        check_ajax_referer('tynr_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $interpretations = Tarot_Yes_No_DB::get_interpretations($card_id);
        
        // Debug logging
        error_log('TYNR Debug - Getting interpretations for card ID: ' . $card_id);
        error_log('TYNR Debug - All interpretations: ' . print_r($interpretations, true));
        
        $result = array(
            'upright' => isset($interpretations['upright']) ? $interpretations['upright'] : array(),
            'reversed' => isset($interpretations['reversed']) ? $interpretations['reversed'] : array()
        );
        
        error_log('TYNR Debug - Result: ' . print_r($result, true));
        
        wp_send_json_success($result);
    }
    
    public function ajax_save_interpretations() {
        check_ajax_referer('tynr_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $interpretations_data = isset($_POST['interpretations']) ? $_POST['interpretations'] : array();
        
        // Debug logging
        error_log('TYNR Debug - Card ID: ' . $card_id);
        error_log('TYNR Debug - Interpretations Data: ' . print_r($interpretations_data, true));
        
        if ($card_id == 0) {
            wp_send_json_error('Invalid card ID');
        }
        
        $result = Tarot_Yes_No_DB::save_interpretations($card_id, $interpretations_data);
        error_log('TYNR Debug - Save result: ' . $result . ' records saved');
        
        wp_send_json_success('Interpretations saved successfully');
    }
    
    /**
     * Check if migration is needed and perform it if necessary
     */
    private function check_migration() {
        if (is_admin() && current_user_can('manage_options')) {
            $migration_status = Tarot_Yes_No_Migration::get_migration_status();
            
            if ($migration_status['can_migrate']) {
                // Add admin notice for migration
                add_action('admin_notices', array($this, 'show_migration_notice'));
                
                // Handle migration if requested
                if (isset($_GET['tynr_migrate']) && $_GET['tynr_migrate'] === '1') {
                    Tarot_Yes_No_Migration::migrate();
                    wp_redirect(admin_url('admin.php?page=tarot-yes-no&migration=success'));
                    exit;
                }
            }
        }
    }
    
    /**
     * Show migration notice in admin
     */
    public function show_migration_notice() {
        $migration_status = Tarot_Yes_No_Migration::get_migration_status();
        
        if ($migration_status['can_migrate']) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Tarot Yes/No Reading Plugin:</strong> Data migration is required. ';
            echo '<a href="' . admin_url('admin.php?page=tarot-yes-no&tynr_migrate=1') . '" class="button button-primary">Migrate Now</a></p>';
            echo '</div>';
        }
        
        if (isset($_GET['migration']) && $_GET['migration'] === 'success') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Tarot Yes/No Reading Plugin:</strong> Data migration completed successfully!</p>';
            echo '</div>';
        }
    }
} 