<?php
/**
 * Admin Page for Tarot Yes/No Reading
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';
$settings = Tarot_Yes_No_DB::get_all_settings();
?>

<div class="wrap">
    <h1>Tarot Yes/No Reading Settings</h1>
    
    <!-- Shortcode Information Box -->
    <div class="notice notice-info" style="margin: 20px 0; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa;">
        <h3 style="margin-top: 0; color: #0073aa;">📋 Shortcode Usage</h3>
        <p style="margin-bottom: 10px;"><strong>Use this shortcode to display the Tarot Yes/No Reading on any post or page:</strong></p>
        <code style="background: #fff; padding: 8px 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; display: inline-block; margin: 5px 0;">[tarot_yes_no_reading]</code>
        <p style="margin-top: 10px; margin-bottom: 0; font-size: 13px; color: #666;">
            Simply copy and paste this shortcode into any WordPress post or page where you want the reading interface to appear.
        </p>
    </div>
    
    <nav class="nav-tab-wrapper">
        <a href="?page=tarot-yes-no&tab=settings" class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
            General Settings
        </a>
        <a href="?page=tarot-yes-no&tab=back-images" class="nav-tab <?php echo $current_tab === 'back-images' ? 'nav-tab-active' : ''; ?>">
            Back Card Images
        </a>
        <a href="?page=tarot-yes-no&tab=interpretations" class="nav-tab <?php echo $current_tab === 'interpretations' ? 'nav-tab-active' : ''; ?>">
            Card Interpretations
        </a>
    </nav>
    
    <div class="tab-content">
        <?php if ($current_tab === 'settings'): ?>
            <?php
            // Handle settings update
            if (isset($_POST['submit'])) {
                $settings = array(
                    'num_cards' => isset($_POST['tynr_settings']['num_cards']) ? intval($_POST['tynr_settings']['num_cards']) : 3,
                    'enable_animations' => isset($_POST['tynr_settings']['enable_animations']) ? 1 : 0,
                    'show_card_names' => isset($_POST['tynr_settings']['show_card_names']) ? 1 : 0
                );
                
                foreach ($settings as $key => $value) {
                    Tarot_Yes_No_DB::set_setting($key, $value);
                }
                
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
            }
            ?>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Number of Cards to Select</th>
                        <td>
                            <select name="tynr_settings[num_cards]" class="tynr-dropdown">
                                <?php 
                                $current_num_cards = isset($settings['num_cards']) ? $settings['num_cards'] : 3;
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($current_num_cards == $i) ? 'selected="selected"' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <p class="description">How many back card images users can choose from.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Animations</th>
                        <td>
                            <label>
                                <input type="checkbox" name="tynr_settings[enable_animations]" value="1" 
                                       <?php checked(isset($settings['enable_animations']) ? $settings['enable_animations'] : true); ?>>
                                Enable card flip animations
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Card Names</th>
                        <td>
                            <label>
                                <input type="checkbox" name="tynr_settings[show_card_names]" value="1" 
                                       <?php checked(isset($settings['show_card_names']) ? $settings['show_card_names'] : true); ?>>
                                Display card names in readings
                            </label>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </form>
            
        <?php elseif ($current_tab === 'back-images'): ?>
            <div class="back-images-manager">
                <h2>Manage Back Card Image</h2>
                <p>Upload a single back card image and set how many copies to display.</p>
                
                <?php
                // Handle back image settings update
                if (isset($_POST['submit_back_image'])) {
                    $back_image_url = isset($_POST['back_image_url']) ? sanitize_url($_POST['back_image_url']) : '';
                    $back_image_name = isset($_POST['back_image_name']) ? sanitize_text_field($_POST['back_image_name']) : 'Back Card';
                    $num_cards_to_show = isset($_POST['num_cards_to_show']) ? intval($_POST['num_cards_to_show']) : 3;
                    
                    // Save settings
                    Tarot_Yes_No_DB::set_setting('back_image_url', $back_image_url);
                    Tarot_Yes_No_DB::set_setting('back_image_name', $back_image_name);
                    Tarot_Yes_No_DB::set_setting('num_cards_to_show', $num_cards_to_show);
                    
                    echo '<div class="notice notice-success is-dismissible"><p>Back image settings saved successfully!</p></div>';
                }
                
                $back_image_settings = array(
                    'image_url' => Tarot_Yes_No_DB::get_setting('back_image_url', ''),
                    'image_name' => Tarot_Yes_No_DB::get_setting('back_image_name', 'Back Card'),
                    'num_cards_to_show' => Tarot_Yes_No_DB::get_setting('num_cards_to_show', 3)
                );
                ?>
                
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Back Card Image</th>
                            <td>
                                <div class="back-image-upload">
                                    <input type="hidden" name="back_image_url" id="back_image_url" 
                                           value="<?php echo esc_attr($back_image_settings['image_url']); ?>">
                                    <input type="text" name="back_image_name" id="back_image_name" 
                                           value="<?php echo esc_attr($back_image_settings['image_name']); ?>" 
                                           placeholder="Image Name" style="width: 300px;">
                                    <button type="button" class="button" id="upload-back-image">Upload Image</button>
                                </div>
                                <div id="back-image-preview" style="margin-top: 10px;">
                                    <?php if (!empty($back_image_settings['image_url'])): ?>
                                        <img src="<?php echo esc_url($back_image_settings['image_url']); ?>" 
                                             alt="<?php echo esc_attr($back_image_settings['image_name']); ?>" 
                                             style="width: 192px; height: 332px; object-fit: cover; border: 1px solid #ddd;">
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Number of Cards to Show</th>
                            <td>
                                <select name="num_cards_to_show" class="tynr-dropdown">
                                    <?php 
                                    $current_num_cards = isset($back_image_settings['num_cards_to_show']) ? $back_image_settings['num_cards_to_show'] : 3;
                                    for ($i = 1; $i <= 10; $i++): 
                                    ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($current_num_cards == $i) ? 'selected="selected"' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <p class="description">How many copies of the back card image to display to users.</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="submit_back_image" id="submit_back_image" class="button button-primary" value="Save Back Image Settings">
                    </p>
                </form>
            </div>
            
        <?php elseif ($current_tab === 'interpretations'): ?>
            <div class="interpretations-manager">
                <h2>Manage Card Interpretations</h2>
                <p>Set interpretations for each tarot card in different categories.</p>
                
                <div class="card-selector">
                    <label for="card-select">Select Card:</label>
                    <select id="card-select">
                        <option value="">Choose a card...</option>
                        <?php
                        global $wpdb;
                        
                        // Check if the table exists
                        $table_name = $wpdb->prefix . 'ac_three_tarot_cards';
                        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
                        
                                                 if ($table_exists) {
                             $cards = $wpdb->get_results("SELECT id, card_name FROM $table_name ORDER BY id");
                             if ($cards) {
                                 foreach ($cards as $card) {
                                     echo '<option value="' . $card->id . '">' . esc_html($card->card_name) . '</option>';
                                 }
                             } else {
                                 echo '<option value="" disabled>No cards found in database</option>';
                             }
                         } else {
                            // Fallback to a basic list of tarot cards if table doesn't exist
                            $basic_cards = array(
                                'The Fool', 'The Magician', 'The High Priestess', 'The Empress', 'The Emperor',
                                'The Hierophant', 'The Lovers', 'The Chariot', 'Strength', 'The Hermit',
                                'Wheel of Fortune', 'Justice', 'The Hanged Man', 'Death', 'Temperance',
                                'The Devil', 'The Tower', 'The Star', 'The Moon', 'The Sun',
                                'Judgement', 'The World'
                            );
                            
                            foreach ($basic_cards as $index => $card_name) {
                                echo '<option value="' . ($index + 1) . '">' . esc_html($card_name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div id="interpretation-form" style="display: none;">
                    <h3>Interpretations for <span id="selected-card-name"></span></h3>
                    
                    <div class="interpretation-tabs">
                        <button type="button" class="tab-button active" data-orientation="upright">Upright</button>
                        <button type="button" class="tab-button" data-orientation="reversed">Reversed</button>
                    </div>
                    
                    <div class="interpretation-content">
                        <div class="orientation-section" data-orientation="upright">
                            <h4>Upright Interpretations</h4>
                            <?php
                            $categories = array('general', 'love', 'money', 'health', 'career');
                            foreach ($categories as $category):
                            ?>
                                <div class="interpretation-field">
                                    <label for="upright-<?php echo $category; ?>"><?php echo ucfirst($category); ?>:</label>
                                    <textarea id="upright-<?php echo $category; ?>" name="interpretations[upright][<?php echo $category; ?>]" rows="3"></textarea>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="orientation-section" data-orientation="reversed" style="display: none;">
                            <h4>Reversed Interpretations</h4>
                            <?php foreach ($categories as $category): ?>
                                <div class="interpretation-field">
                                    <label for="reversed-<?php echo $category; ?>"><?php echo ucfirst($category); ?>:</label>
                                    <textarea id="reversed-<?php echo $category; ?>" name="interpretations[reversed][<?php echo $category; ?>]" rows="3"></textarea>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="button" class="button button-primary" id="save-interpretations">Save Interpretations</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.back-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.back-image-item {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

.image-controls {
    margin-top: 10px;
}

.image-controls input[type="text"] {
    width: 100%;
    margin-bottom: 5px;
}

.interpretation-tabs {
    margin: 20px 0;
}

.tab-button {
    padding: 10px 20px;
    margin-right: 5px;
    border: 1px solid #ddd;
    background: #f9f9f9;
    cursor: pointer;
}

.tab-button.active {
    background: #0073aa;
    color: white;
}

.interpretation-field {
    margin-bottom: 15px;
}

.interpretation-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.interpretation-field textarea {
    width: 100%;
}

.tynr-dropdown {
    min-width: 200px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fff;
    font-size: 14px;
    line-height: 1.4;
    color: #333;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tynr-dropdown:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 1px #0073aa;
}

.tynr-dropdown option {
    padding: 8px 12px;
    background-color: #fff;
    color: #333;
}

.tynr-dropdown option:checked {
    background-color: #0073aa;
    color: #fff;
}
</style> 