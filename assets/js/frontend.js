/**
 * Frontend JavaScript for Tarot Yes/No Reading
 */
(function($) {
    'use strict';
    
    let selectedCards = [];
    let currentReading = null;
    
    $(document).ready(function() {
        initializeReading();
    });
    
    function initializeReading() {
        // Card selection functionality - click on card image
        $('.clickable-card').on('click', function() {
            const cardItem = $(this).closest('.overlapping-card-item');
            
            // Don't allow clicking on disabled cards
            if (cardItem.hasClass('disabled')) {
                return;
            }
            
            const cardIndex = cardItem.data('index');
            toggleCardSelection(cardIndex, cardItem);
        });
        
        // Get reading button
        $('#get-reading-btn').on('click', function() {
            getReading();
        });
        
        // New reading button
        $('#new-reading-btn').on('click', function() {
            resetReading();
        });
    }
    
    function toggleCardSelection(cardIndex, cardItem) {
    const isSelected = cardItem.hasClass('selected');
    const maxCards = window.tynrMaxCards || 3; // Use the global variable or default to 3
    
    if (isSelected) {
        // Deselect card
        cardItem.removeClass('selected');
        selectedCards = selectedCards.filter(card => card.index !== cardIndex);
    } else {
        // Check if we can select more cards
        if (selectedCards.length >= maxCards) {
            // Don't allow selection if maximum reached
            return;
        }
        
        // Select card
        cardItem.addClass('selected');
        selectedCards.push({
            index: cardIndex
        });
    }
    
    updateSelectionSummary();
}
    
    function updateSelectionSummary() {
        const count = selectedCards.length;
        const maxCards = window.tynrMaxCards || 3; // Use the global variable or default to 3
        
        console.log('updateSelectionSummary - count:', count, 'maxCards:', maxCards);
        
        $('#selected-count').text(count);
        
        // Update selected cards list - no need to show card names
        const cardsList = $('#selected-cards-list');
        cardsList.empty();
        
        // Show/hide summary section and button
        if (count > 0) {
            $('.selection-summary').show();
            $('#get-reading-btn').show(); // Show the button when cards are selected
        } else {
            $('.selection-summary').hide();
            $('#get-reading-btn').hide(); // Hide the button when no cards selected
        }
        
        // Enable/disable get reading button
        const getReadingBtn = $('#get-reading-btn');
        const shouldEnable = count === maxCards;
        console.log('Button should be enabled:', shouldEnable);
        getReadingBtn.prop('disabled', !shouldEnable);
        
        // Update card disabled states
        $('.overlapping-card-item').each(function() {
            const cardItem = $(this);
            const isSelected = cardItem.hasClass('selected');
            
            if (!isSelected && count >= maxCards) {
                cardItem.addClass('disabled');
            } else {
                cardItem.removeClass('disabled');
            }
        });
    }
    
    function getReading() {
        console.log('getReading called with selectedCards.length:', selectedCards.length);
        console.log('window.tynrMaxCards:', window.tynrMaxCards);
        
        // Double-check that we have the right number of cards
        const maxCards = window.tynrMaxCards || 3;
        if (selectedCards.length !== maxCards) {
            console.log('ERROR: Card count mismatch! selectedCards.length:', selectedCards.length, 'maxCards:', maxCards);
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: tynr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tynr_get_reading',
                nonce: tynr_ajax.nonce,
                num_cards: selectedCards.length
            },
            success: function(response) {
                hideLoading();
                console.log('AJAX response:', response);
                if (response.success) {
                    currentReading = response.data;
                    displayReading(response.data);
                } else {
                    console.log('AJAX returned error:', response);
                    alert('Error getting reading. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.log('AJAX error:', status, error);
                console.log('Response text:', xhr.responseText);
                alert('Error getting reading. Please try again.');
            }
        });
    }
    
    function displayReading(reading) {
        const container = $('#reading-cards-container');
        container.empty();
        
        reading.forEach((card, index) => {
            const cardHtml = createCardHtml(card, index);
            container.append(cardHtml);
        });
        
        // Show reading results
        $('#card-selection-phase').hide();
        $('#reading-results-phase').show();
        
        // Create reading summary
        createReadingSummary(reading);
    }
    
    function createCardHtml(card, index) {
        const orientation = card.is_reversed ? 'Reversed' : 'Upright';
        const orientationClass = card.is_reversed ? 'reversed' : 'upright';
        
        let interpretationsHtml = '';
        Object.keys(card.interpretations).forEach(category => {
            const interpretation = card.interpretations[category];
            interpretationsHtml += `
                <div class="interpretation-category">
                    <h5>${category.charAt(0).toUpperCase() + category.slice(1)}</h5>
                    <p>${interpretation}</p>
                </div>
            `;
        });
        
        return `
            <div class="reading-card ${orientationClass}">
                <div class="card-header">
                    <img src="${card.image}" alt="${card.name}" class="card-image-small">
                    <div class="card-details">
                        <h4>${card.name}</h4>
                        <div class="card-orientation">${orientation}</div>
                    </div>
                </div>
                
                <div class="card-answer">
                    Tarot Result: ${card.answer}
                </div>
                
                <div class="interpretations">
                    ${interpretationsHtml}
                </div>
            </div>
        `;
    }
    
    function createReadingSummary(reading) {
        const summaryContainer = $('#reading-summary-content');
        summaryContainer.empty();
        
        // Count answers
        const answers = reading.map(card => card.answer);
        const yesCount = answers.filter(answer => answer === 'Yes').length;
        const noCount = answers.filter(answer => answer === 'No').length;
        const maybeCount = answers.filter(answer => answer === 'Maybe').length;
        
        // Overall message
        let overallMessage = '';
        if (yesCount > noCount && yesCount > maybeCount) {
            overallMessage = 'The cards suggest a positive outcome.';
        } else if (noCount > yesCount && noCount > maybeCount) {
            overallMessage = 'The cards suggest caution or reconsideration.';
        } else {
            overallMessage = 'The cards suggest the situation is still unfolding.';
        }
        
        const summaryHtml = `
            <div class="summary-stats">
                <p><strong>Yes:</strong> ${yesCount} | <strong>No:</strong> ${noCount} | <strong>Maybe:</strong> ${maybeCount}</p>
            </div>
            <div class="summary-message">
                <p><strong>Overall:</strong> ${overallMessage}</p>
            </div>
        `;
        
        summaryContainer.html(summaryHtml);
    }
    
    function resetReading() {
        // Refresh the page to start a completely new reading
        window.location.reload();
    }
    
    function showLoading() {
        $('#loading-state').show();
    }
    
    function hideLoading() {
        $('#loading-state').hide();
    }
    
})(jQuery); 