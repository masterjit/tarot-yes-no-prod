/**
 * Admin JavaScript for Tarot Yes/No Reading
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        initializeAdmin();
    });
    
    function initializeAdmin() {
        console.log('TYNR: Initializing admin...');
        
        // Back image management
        $('#upload-back-image').on('click', function() {
            console.log('TYNR: Upload button clicked');
            uploadBackImage();
        });
        
        // Interpretation management
        $('#card-select').on('change', loadCardInterpretations);
        $('.tab-button').on('click', switchInterpretationTab);
        $('#save-interpretations').on('click', saveInterpretations);
        
        console.log('TYNR: Admin initialized');
    }
    
    function uploadBackImage() {
        console.log('TYNR: uploadBackImage called');
        
        // Check if wp.media is available
        if (typeof wp === 'undefined') {
            console.log('TYNR: wp is undefined');
            alert('WordPress Media Library is not available. Please refresh the page and try again.');
            return;
        }
        
        if (typeof wp.media === 'undefined') {
            console.log('TYNR: wp.media is undefined');
            alert('WordPress Media Library is not available. Please refresh the page and try again.');
            return;
        }
        
        console.log('TYNR: Creating media uploader...');
        
        try {
            const mediaUploader = wp.media({
                title: 'Select Back Card Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                console.log('TYNR: Image selected');
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                console.log('TYNR: Attachment data:', attachment);
                
                // Update the hidden input with the image URL
                $('#back_image_url').val(attachment.url);
                
                // Update the image name input with the attachment name
                $('#back_image_name').val(attachment.title || attachment.filename || 'Back Card');
                
                // Update the preview
                const previewHtml = `
                    <img src="${attachment.url}" alt="${attachment.title || attachment.filename || 'Back Card'}" 
                         style="width: 192px; height: 332px; object-fit: cover; border: 1px solid #ddd;">
                `;
                $('#back-image-preview').html(previewHtml);
                
                console.log('TYNR: Preview updated');
            });
            
            console.log('TYNR: Opening media uploader...');
            mediaUploader.open();
        } catch (error) {
            console.error('TYNR: Error creating media uploader:', error);
            alert('Error opening media library. Please try again.');
        }
    }
    
    function loadCardInterpretations() {
        const cardId = $('#card-select').val();
        const cardName = $('#card-select option:selected').text();
        
        if (!cardId) {
            $('#interpretation-form').hide();
            return;
        }
        
        $('#selected-card-name').text(cardName);
        $('#interpretation-form').show();
        
        // Load existing interpretations
        $.ajax({
            url: tynr_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tynr_get_interpretations',
                nonce: tynr_admin_ajax.nonce,
                card_id: cardId
            },
            success: function(response) {
                if (response.success) {
                    populateInterpretations(response.data);
                } else {
                    clearInterpretations();
                }
            },
            error: function() {
                clearInterpretations();
            }
        });
    }
    
    function populateInterpretations(interpretations) {
        // Clear all fields first
        clearInterpretations();
        
        // Populate with existing data
        Object.keys(interpretations).forEach(orientation => {
            Object.keys(interpretations[orientation]).forEach(category => {
                const value = interpretations[orientation][category];
                $(`#${orientation}-${category}`).val(value);
            });
        });
    }
    
    function clearInterpretations() {
        $('textarea[name*="interpretations"]').val('');
    }
    
    function switchInterpretationTab() {
        const orientation = $(this).data('orientation');
        
        // Update tab buttons
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        // Show/hide orientation sections
        $('.orientation-section').hide();
        $(`.orientation-section[data-orientation="${orientation}"]`).show();
    }
    
    function saveInterpretations() {
        const cardId = $('#card-select').val();
        const interpretations = {
            upright: {},
            reversed: {}
        };
        
        // Collect upright interpretations
        $('.orientation-section[data-orientation="upright"] textarea').each(function() {
            const category = $(this).attr('id').replace('upright-', '');
            interpretations.upright[category] = $(this).val();
        });
        
        // Collect reversed interpretations
        $('.orientation-section[data-orientation="reversed"] textarea').each(function() {
            const category = $(this).attr('id').replace('reversed-', '');
            interpretations.reversed[category] = $(this).val();
        });
        
        $.ajax({
            url: tynr_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tynr_save_interpretations',
                nonce: tynr_admin_ajax.nonce,
                card_id: cardId,
                interpretations: interpretations
            },
            success: function(response) {
                if (response.success) {
                    alert('Interpretations saved successfully!');
                } else {
                    alert('Error saving interpretations.');
                }
            },
            error: function() {
                alert('Error saving interpretations.');
            }
        });
    }
    
})(jQuery); 