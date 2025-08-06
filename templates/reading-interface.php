<?php
/**
 * Frontend Reading Interface Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tynr-reading-container">
    <div class="tynr-header">
        <h2>Tarot Yes/No Reading</h2>
        <p>Choose your preferred card and receive your personalized reading.</p>
    </div>
    
    <!-- Card Selection Phase -->
    <div id="card-selection-phase" class="tynr-phase">
        <div class="back-cards-container">
            <h3>Select Your Card</h3>
            <p>Choose <?php echo $num_cards; ?> card(s) to begin your reading.</p>
            
            <div class="overlapping-cards-container">
                <div class="overlapping-cards-stack">
                    <?php 
                    $selected_count = 0;
                    $active_images = array();
                    
                    // Filter active images and ensure they're arrays
                    foreach ($back_images as $index => $image) {
                        if (is_array($image) && isset($image['is_active']) && $image['is_active']) {
                            $active_images[] = $image;
                        }
                    }
                    
                    foreach ($active_images as $index => $image): 
                    ?>
                        <div class="overlapping-card-item" data-index="<?php echo $index; ?>" style="z-index: <?php echo count($active_images) - $index; ?>; --original-x: <?php echo $index * 60; ?>px; --mobile-x: <?php echo $index * 30; ?>px;">
                            <div class="card-image clickable-card">
                                <img src="<?php echo esc_url($image['image_url']); ?>" 
                                     alt="Back Card"
                                     class="back-card-image">
                            </div>
                        </div>
                    <?php 
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        
        <div class="selection-summary" style="display: none;">
            <h3>Selected Cards: <span id="selected-count">0</span>/<?php echo $num_cards; ?></h3>
            <div id="selected-cards-list"></div>
            <button type="button" id="reading-btn" class="tynr-btn tynr-btn-primary">
                Get Your Reading
            </button>
        </div>
        
        <script>
            // Pass the maximum cards value to JavaScript
            window.tynrMaxCards = <?php echo $num_cards; ?>;
        </script>
    </div>
    
    <!-- Reading Results Phase -->
    <div id="reading-results-phase" class="tynr-phase" style="display: none;">
        <div class="reading-header">
            <h3>Your Tarot Reading</h3>
            <button type="button" id="new-reading-btn" class="tynr-btn tynr-btn-secondary">
                Start New Reading
            </button>
        </div>
        
        <div class="cards-reading">
            <div id="reading-cards-container"></div>
        </div>
        
        <div class="reading-summary">
            <h4>Reading Summary</h4>
            <div id="reading-summary-content"></div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div id="loading-state" class="tynr-loading" style="display: none;">
        <div class="loading-spinner"></div>
        <p>Shuffling the cards and preparing your reading...</p>
    </div>
</div>

<style>
/* Set CSS custom properties for dynamic card count */
.overlapping-cards-stack {
    --num-cards: <?php echo count($active_images); ?>;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .overlapping-cards-stack {
        --num-cards: <?php echo count($active_images); ?>;
    }
}
</style> 