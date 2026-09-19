jQuery(document).ready(function($) {
    
    // --- General Media Uploader (Single Image) ---
    function initMediaUploader(btnClass, inputId, previewId, removeBtnClass) {
        var mediaUploader;
        $(document).on('click', btnClass, function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: { text: 'Choose Image' },
                multiple: false
            });
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $(inputId).val(attachment.id);
                $(previewId).html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;" />');
                $(removeBtnClass).show();
            });
            mediaUploader.open();
        });

        $(document).on('click', removeBtnClass, function(e) {
            e.preventDefault();
            $(inputId).val('');
            $(previewId).html('');
            $(this).hide();
        });
    }

    // --- Generic File Uploader (Single File) ---
    function initFileUploader(btnClass, inputId, previewId, removeBtnClass) {
        var fileUploader;
        $(document).on('click', btnClass, function(e) {
            e.preventDefault();
            if (fileUploader) {
                fileUploader.open();
                return;
            }
            fileUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose File',
                button: { text: 'Choose File' },
                multiple: false
            });
            fileUploader.on('select', function() {
                var attachment = fileUploader.state().get('selection').first().toJSON();
                $(inputId).val(attachment.id);
                // For files, maybe show filename or icon
                var icon = attachment.icon || attachment.url;
                $(previewId).html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
                $(removeBtnClass).show();
            });
            fileUploader.open();
        });

        $(document).on('click', removeBtnClass, function(e) {
            e.preventDefault();
            $(inputId).val('');
            $(previewId).html('');
            $(this).hide();
        });
    }

    // --- Generic File Uploader (Reusable) ---
    var genericFileUploader;
    $(document).on('click', '.ctd-upload-file', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        
        if (genericFileUploader) {
            genericFileUploader.open();
        } else {
            genericFileUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose File',
                button: { text: 'Use File' },
                multiple: false
            });
        }

        genericFileUploader.off('select').on('select', function() {
            var attachment = genericFileUploader.state().get('selection').first().toJSON();
            $(targetInput).val(attachment.id);
            
            var html = '<div style="display: flex; align-items: center; gap: 10px; background: #f0f0f1; padding: 10px; border-radius: 4px;">';
            html += '<span class="dashicons dashicons-pdf"></span>';
            html += '<a href="' + attachment.url + '" target="_blank" style="text-decoration: none; font-weight: 500;">' + attachment.title + '</a>';
            html += '</div>';
            
            $(targetPreview).html(html);
            $btn.next('.ctd-remove-file').show();
        });

        genericFileUploader.open();
    });

    $(document).on('click', '.ctd-remove-file', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        
        $(targetInput).val('');
        $(targetPreview).html('');
        $btn.hide();
    });

    // Taxonomy Image
    initMediaUploader('.ctd-upload-image', '#ctd-image-id', '#ctd-image-preview', '.ctd-remove-image');    
    // Map Image
    initMediaUploader('.ctd-upload-map-image', '#ctd_map_image', '#ctd_map_image_preview', '.ctd-remove-map-image');


    // --- Metabox Tabs Switching ---
    $('.ctd-metabox-tabs li').click(function() {
        var tab_id = $(this).attr('data-tab');

        $('.ctd-metabox-tabs li').removeClass('active');
        $('.ctd-tab-pane').removeClass('active');

        $(this).addClass('active');
        $("#ctd-tab-" + tab_id).addClass('active');
    });

    // --- Keep Trip Settings anchored below the editor ---
    // WordPress makes every meta box draggable between the main column and the
    // sidebar (wp-admin/js/postbox.js). Dragging this one by accident is how it
    // ends up stuck in the sidebar, so exclude it from the sortable.
    function lockTripSettingsMetabox() {
        if (!$('#ctd_trip_settings').length) {
            return;
        }
        $('.meta-box-sortables.ui-sortable').sortable('option', 'cancel', '#ctd_trip_settings');
    }

    // The block editor initializes postboxes once its own editor is ready, so
    // wrap the initializer to make sure the lock is applied right after it runs.
    if (window.postboxes && typeof window.postboxes.add_postbox_toggles === 'function' && !window.postboxes.add_postbox_toggles.ctdLocked) {
        var ctdAddPostboxToggles = window.postboxes.add_postbox_toggles;
        var ctdWrappedAddPostboxToggles = function() {
            var result = ctdAddPostboxToggles.apply(this, arguments);
            lockTripSettingsMetabox();
            return result;
        };
        ctdWrappedAddPostboxToggles.ctdLocked = true;
        window.postboxes.add_postbox_toggles = ctdWrappedAddPostboxToggles;
    }

    lockTripSettingsMetabox();
    $(window).on('load', lockTripSettingsMetabox);
    $(document).on('postbox-moved', lockTripSettingsMetabox);

    // --- Age Limit Toggle ---
    $('#ctd_enable_age_limit').change(function() {
        if(this.checked) {
            $('.ctd-age-fields').slideDown();
        } else {
            $('.ctd-age-fields').slideUp();
        }
    });

    // --- Gallery Toggle ---
    $('#ctd_gallery_enable').change(function() {
        if(this.checked) {
            $('.ctd-gallery-wrap').slideDown();
        } else {
            $('.ctd-gallery-wrap').slideUp();
        }
    });
    
    // --- Booking Toggle ---
    $('#ctd_enable_booking').change(function() {
        if(this.checked) {
            $('.ctd-booking-url-wrap').slideDown();
        } else {
            $('.ctd-booking-url-wrap').slideUp();
        }
    });

    // --- More Info Toggle ---
    $('#ctd_enable_more_info').change(function() {
        if(this.checked) {
            $('.ctd-more-info-wrap').slideDown();
        } else {
            $('.ctd-more-info-wrap').slideUp();
        }
    });

    // --- Downloads Toggle ---
    $('#ctd_enable_downloads').change(function() {
        if(this.checked) {
            $('.ctd-downloads-wrap').slideDown();
        } else {
            $('.ctd-downloads-wrap').slideUp();
        }
    });

    // --- Extra Services Toggle ---
    $('#ctd_enable_extra_services').change(function() {
        if(this.checked) {
            $('.ctd-extra-services-wrap').slideDown();
        } else {
            $('.ctd-extra-services-wrap').slideUp();
        }
    });

    // --- Itinerary PDF Toggle ---
    $('#ctd_enable_itinerary_pdf').change(function() {
        if(this.checked) {
            $('.ctd-itinerary-pdf-wrap').slideDown();
        } else {
            $('.ctd-itinerary-pdf-wrap').slideUp();
        }
    });

    $('#ctd_video_gallery_enable').change(function() {
        if(this.checked) {
            $('.ctd-video-gallery-wrap').slideDown();
        } else {
            $('.ctd-video-gallery-wrap').slideUp();
        }
    });

    // --- Flights Toggle ---
    $('#ctd_enable_flights').change(function() {
        if(this.checked) {
            $('#ctd-tab-flights .ctd-flights-wrap').slideDown();
        } else {
            $('#ctd-tab-flights .ctd-flights-wrap').slideUp();
        }
    }).trigger('change');

    // --- Meals Details Toggle ---
    $('#ctd_enable_meals').change(function() {
        if(this.checked) {
            $('#ctd-tab-meals .ctd-meals-wrap').slideDown();
        } else {
            $('#ctd-tab-meals .ctd-meals-wrap').slideUp();
        }
    }).trigger('change');

    // --- Pricing Category Toggle ---
    $(document).on('click', '.ctd-pricing-cat-header', function(e) {
        if ($(e.target).closest('label.ctd-switch').length || $(e.target).is('input[type=checkbox]')) {
            return;
        }
        var $box = $(this).closest('.ctd-pricing-category-box');
        var $content = $box.find('.ctd-pricing-cat-content');
        if ($box.hasClass('active')) {
            $content.slideUp();
            $box.removeClass('active');
        } else {
            $box.siblings('.ctd-pricing-category-box').removeClass('active').find('.ctd-pricing-cat-content').slideUp();
            $content.slideDown();
            $box.addClass('active');
        }
    });
    $('.ctd-pricing-cat-content').hide();
    $(document).on('change', '.ctd-pricing-enable', function() {
        var $content = $(this).closest('.ctd-pricing-cat-content');
        var $fields = $content.find('.ctd-pricing-fields');
        if (this.checked) {
            $fields.slideDown();
        } else {
            $fields.slideUp();
        }
    });

    // --- Extra Service Image Uploader (data-target & data-preview) ---
    var serviceImageUploader;
    $(document).on('click', '.ctd-upload-service-image', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        serviceImageUploader = wp.media({
            title: 'Choose Image',
            button: { text: 'Use Image' },
            multiple: false
        });
        serviceImageUploader.on('select', function() {
            var attachment = serviceImageUploader.state().get('selection').first().toJSON();
            $(targetInput).val(attachment.id);
            $(targetPreview).html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;" />');
            $btn.next('.ctd-remove-service-image').show();
        });
        serviceImageUploader.open();
    });
    $(document).on('click', '.ctd-remove-service-image', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        $(targetInput).val('');
        $(targetPreview).html('');
        $btn.hide();
    });

    // --- Hero Image Uploader ---
    var heroImageUploader;
    $(document).on('click', '.ctd-upload-hero-image', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        
        // Create a unique uploader instance or reuse a generic one if you want, 
        // but keeping it simple like service uploader:
        heroImageUploader = wp.media({
            title: 'Choose Hero Image',
            button: { text: 'Use Image' },
            multiple: false
        });

        heroImageUploader.on('select', function() {
            var attachment = heroImageUploader.state().get('selection').first().toJSON();
            $(targetInput).val(attachment.id);
            $(targetPreview).html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;" />');
            $btn.next('.ctd-remove-hero-image').show();
        });

        heroImageUploader.open();
    });

    $(document).on('click', '.ctd-remove-hero-image', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetInput = $btn.data('target');
        var targetPreview = $btn.data('preview');
        $(targetInput).val('');
        $(targetPreview).html('');
        $btn.hide();
    });

    // --- TinyMCE Helper ---
    function initMCE(id) {
        if ( typeof wp !== 'undefined' && wp.editor ) {
            wp.editor.initialize(id, {
                tinymce: {
                    wpautop: true,
                    plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
                    toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | wp_more | spellchecker',
                    toolbar2: ''
                },
                quicktags: true,
                mediaButtons: true
            });
        }
    }

    function removeMCE(id) {
         if ( typeof wp !== 'undefined' && wp.editor ) {
            wp.editor.remove(id);
        }
    }

    // Init on load
    $('.ctd-wysiwyg-editor').each(function() {
        var id = $(this).attr('id');
        if(id) {
            initMCE(id);
        }
    });

    // --- Sortable Initialization ---
    if ($.fn.sortable) {
        $('.ctd-sortable-list').sortable({
            handle: '.ctd-sort-handle',
            placeholder: 'ui-state-highlight',
            forcePlaceholderSize: true,
            axis: 'y',
            start: function(event, ui) {
                 // Remove editors inside the dragged item
                 ui.item.find('.ctd-wysiwyg-editor').each(function() {
                     removeMCE($(this).attr('id'));
                 });
            },
            stop: function(event, ui) {
                 // Re-init editors
                 ui.item.find('.ctd-wysiwyg-editor').each(function() {
                     initMCE($(this).attr('id'));
                 });
            },
            update: function(event, ui) {
                // If itinerary, re-number days
                if ($(this).data('repeater') === 'ctd_itineraries') {
                    $(this).find('.ctd-repeater-item').each(function(idx) {
                        $(this).find('.ctd-day-badge').text('Day ' + (idx + 1));
                    });
                }
            }
        });
    }

    // --- Repeater Functionality ---
    $('.ctd-repeater-wrapper').each(function() {
        var $wrapper = $(this);
        var repeaterName = $wrapper.data('repeater');
        var $template = $wrapper.find('.ctd-repeater-template');
        
        // Add Item
        $wrapper.on('click', '.ctd-repeater-add', function(e) {
            e.preventDefault();
            var index = $wrapper.find('.ctd-repeater-item').length; 
            var newIndex = new Date().getTime(); 
            var templateHtml = $template.html();
            
            // Replace {index} with newIndex
            var newItemHtml = templateHtml.replace(/{index}/g, newIndex);
            
            // Replace {day_count} for itinerary if needed
            var dayCount = $wrapper.find('.ctd-repeater-item').length + 1;
            newItemHtml = newItemHtml.replace(/{day_count}/g, dayCount);

            $(this).before(newItemHtml);
            
            var $newItem = $(this).prev('.ctd-repeater-item');

            // Init Select2 on the new item
            initSelect2($newItem);

            // Init Editors
            $newItem.find('.ctd-wysiwyg-editor').each(function() {
                initMCE($(this).attr('id'));
            });
        });

        // Remove Item
        $wrapper.on('click', '.ctd-repeater-remove', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ctd-repeater-item');
            
            // Remove editors first to avoid memory leaks
            $item.find('.ctd-wysiwyg-editor').each(function() {
                removeMCE($(this).attr('id'));
            });

            $item.remove();
            
            // Re-number days if itinerary
            if (repeaterName === 'ctd_itineraries') {
                $wrapper.find('.ctd-repeater-item').each(function(idx) {
                    $(this).find('.ctd-day-badge').text('Day ' + (idx + 1));
                });
            }
        });
    });

    // --- Itinerary Accordion ---
    $(document).on('click', '.ctd-accordion-header', function(e) {
        // Prevent toggle if clicking delete or handle
        if ($(e.target).closest('.ctd-repeater-remove').length || $(e.target).closest('.ctd-sort-handle').length) {
            return;
        }

        var $item = $(this).closest('.ctd-accordion-item');
        var $content = $item.find('.ctd-accordion-content');
        
        // Toggle current
        if ($item.hasClass('active')) {
            $content.slideUp();
            $item.removeClass('active');
        } else {
            // Close others in same wrapper
            $item.siblings('.ctd-accordion-item').removeClass('active').find('.ctd-accordion-content').slideUp();
            
            $content.slideDown();
            $item.addClass('active');
        }
    });

    // --- Itinerary Title Live Update ---
    $(document).on('input', '.ctd-itinerary-title-input', function() {
        var val = $(this).val();
        $(this).closest('.ctd-accordion-item').find('.ctd-day-title-text').text(val);
    });

    // --- Accordion Title Live Updates (Services, FAQs, Downloads) ---
    $(document).on('input', '.ctd-service-name-input', function() {
        var val = $(this).val();
        $(this).closest('.ctd-accordion-item').find('.ctd-service-title-text').text(val || 'New Service');
    });

    $(document).on('input', '.ctd-faq-question-input', function() {
        var val = $(this).val();
        $(this).closest('.ctd-accordion-item').find('.ctd-faq-question-text').text(val || 'New FAQ');
    });

    $(document).on('input', '.ctd-download-title-input', function() {
        var val = $(this).val();
        $(this).closest('.ctd-accordion-item').find('.ctd-download-title-text').text(val || 'New File');
    });

    // --- Trip Facts Add (Special Handling) ---
    $('#ctd_add_trip_fact_btn').click(function(e) {
        e.preventDefault();
        var selectedFact = $('#ctd_trip_fact_select').val();
        
        if (!selectedFact) {
            alert('Please select a trip fact first.');
            return;
        }
        
        var $wrapper = $('#ctd_trip_facts_container');
        var $template = $wrapper.find('#ctd_trip_fact_template');
        var newIndex = new Date().getTime();
        var templateHtml = $template.html();
        
        // Replace placeholders
        var newItemHtml = templateHtml.replace(/{index}/g, newIndex);
        newItemHtml = newItemHtml.replace(/{label}/g, selectedFact);
        
        $wrapper.append(newItemHtml);
        
        // Reset select
        $('#ctd_trip_fact_select').val('');
    });

    // --- Flight Accordion ---
    $('.ctd-flight-section-title').click(function() {
        var $this = $(this);
        var $content = $this.next('.ctd-flight-section-content');
        var $parent = $this.closest('.ctd-tab-fields');
        
        if ($content.is(':visible')) {
            $content.slideUp();
            $this.find('.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        } else {
            // Close others
            $parent.find('.ctd-flight-section-content').slideUp();
            $parent.find('.ctd-flight-section-title .dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            
            // Open this one
            $content.slideDown();
            $this.find('.dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        }
    });


    // --- Image Gallery Multiple Upload ---
    var galleryUploader;
    $(document).on('click', '.ctd-gallery-add', function(e) {
        e.preventDefault();

        var $gallery = $(this).closest('.ctd-gallery-images');

        if (!galleryUploader) {
            galleryUploader = wp.media({
                title: 'Select Images',
                button: { text: 'Add Images' },
                library: { type: 'image' },
                multiple: true
            });

            // Make a plain click add another image to the selection instead of
            // replacing it, so no Ctrl/Cmd key is needed to pick several images.
            galleryUploader.on('open', function() {
                var selection = galleryUploader.state().get('selection');
                if (selection) {
                    selection.multiple = 'add';
                }
            });

            galleryUploader.on('select', function() {
                var $target = galleryUploader.ctdGalleryTarget;

                if (!$target || !$target.length) {
                    return;
                }

                galleryUploader.state().get('selection').each(function(attachment) {
                    var data = attachment.toJSON();

                    if (!data.id) {
                        return;
                    }

                    // Ignore images that are already in the gallery.
                    if ($target.find('input[value="' + data.id + '"]').length) {
                        return;
                    }

                    // Not every upload gets every size generated, so fall back
                    // through the available sizes instead of assuming "thumbnail".
                    var src = data.url;
                    if (data.sizes) {
                        if (data.sizes.thumbnail) {
                            src = data.sizes.thumbnail.url;
                        } else if (data.sizes.medium) {
                            src = data.sizes.medium.url;
                        } else if (data.sizes.large) {
                            src = data.sizes.large.url;
                        }
                    }

                    var html = '<div class="ctd-gallery-image">';
                    html += '<input type="hidden" name="ctd_gallery_images[]" value="' + data.id + '" />';
                    html += '<img src="' + src + '" />';
                    html += '<span class="ctd-gallery-remove dashicons dashicons-no-alt"></span>';
                    html += '</div>';

                    $target.find('.ctd-gallery-add').before(html);
                });
            });
        }

        galleryUploader.ctdGalleryTarget = $gallery;
        galleryUploader.open();
    });

    $(document).on('click', '.ctd-gallery-remove', function() {
        $(this).parent().remove();
    });

    // --- Select2 Helper ---
    function initSelect2($context) {
        if ($.fn.select2) {
            $context.find('select').not('.select2-hidden-accessible').select2({
                width: '100%'
            });
        }
    }

    // Init Select2 on Load for all selects in metabox
    initSelect2($('.ctd-metabox-content'));

    // --- Ajax Complete ---
    $(document).ajaxComplete(function(event, xhr, settings) {
        if ( settings.data && settings.data.indexOf('action=add-tag') !== -1 ) {
            if ( xhr.responseXML ) {
                var response = xhr.responseXML.getElementsByTagName('term_id');
                if ( response.length > 0 ) {
                    $('#ctd-image-id').val('');
                    $('#ctd-image-preview').html('');
                    $('.ctd-remove-image').hide();
                }
            }
        }
    });

    // --- Nested Tabs (Pricing) ---
    $(document).on('click', '.ctd-nested-tabs-nav li', function() {
        var $this = $(this);
        var target = $this.data('target');
        var $wrapper = $this.closest('.ctd-repeater-item-content');
        
        $wrapper.find('.ctd-nested-tabs-nav li').removeClass('active');
        $this.addClass('active');
        
        $wrapper.find('.ctd-nested-tab-pane').removeClass('active');
        $wrapper.find('.ctd-nested-tab-pane[data-tab="'+target+'"]').addClass('active');
    });

    // --- Enable Dates Toggle ---
    $(document).on('change', 'input[name*="[enable_dates]"]', function() {
         var $wrapper = $(this).closest('.ctd-nested-tab-pane').find('.ctd-dates-wrapper');
         if(this.checked) {
             $wrapper.slideDown();
         } else {
             $wrapper.slideUp();
         }
    });

    // --- Sub-Repeater (Dates) ---
    $(document).on('click', '.ctd-add-sub-repeater', function(e) {
        e.preventDefault();
        var $wrapper = $(this).closest('.ctd-sub-repeater');
        var fieldName = $(this).data('name');
        var newIndex = new Date().getTime();
        
        var html = '<div class="ctd-sub-repeater-item">';
        html += '<input type="date" name="' + fieldName + '[' + newIndex + ']" />';
        html += '<button type="button" class="button ctd-remove-sub-repeater">x</button>';
        html += '</div>';
        
        $(this).before(html);
    });

    $(document).on('click', '.ctd-remove-sub-repeater', function(e) {
        e.preventDefault();
        $(this).closest('.ctd-sub-repeater-item').remove();
    });

    // --- File Download Repeater Upload ---
    var fileRepeaterUploader;
    $(document).on('click', '.ctd-upload-file-repeater', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $wrapper = $btn.closest('.ctd-sub-field');
        
        if (fileRepeaterUploader) {
            fileRepeaterUploader.open();
        } else {
            fileRepeaterUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose File',
                button: { text: 'Choose File' },
                multiple: false
            });
        }

        // We need to store which button triggered the open
        fileRepeaterUploader.off('select').on('select', function() {
            var attachment = fileRepeaterUploader.state().get('selection').first().toJSON();
            $wrapper.find('input[type="hidden"]').val(attachment.id);
            var icon = attachment.icon || attachment.url;
            $wrapper.find('.ctd-file-preview').html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
            $wrapper.find('.ctd-remove-file-repeater').show();
        });

        fileRepeaterUploader.open();
    });

    $(document).on('click', '.ctd-remove-file-repeater', function(e) {
        e.preventDefault();
        var $wrapper = $(this).closest('.ctd-sub-field');
        $wrapper.find('input[type="hidden"]').val('');
        $wrapper.find('.ctd-file-preview').html('');
        $(this).hide();
    });

    // --- Itinerary Image Upload ---
    var itineraryImageUploader;
    $(document).on('click', '.ctd-upload-itinerary-image', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $wrapper = $btn.closest('.ctd-image-upload-wrapper');
        
        if (itineraryImageUploader) {
            itineraryImageUploader.open();
        } else {
            itineraryImageUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: { text: 'Use Image' },
                multiple: false
            });
        }

        // We need to store which button triggered the open
        itineraryImageUploader.off('select').on('select', function() {
            var attachment = itineraryImageUploader.state().get('selection').first().toJSON();
            $wrapper.find('input[type="hidden"]').val(attachment.id);
            $wrapper.find('.ctd-image-preview').html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;" />');
            $wrapper.find('.ctd-remove-itinerary-image').show();
        });

        itineraryImageUploader.open();
    });

    $(document).on('click', '.ctd-remove-itinerary-image', function(e) {
        e.preventDefault();
        var $wrapper = $(this).closest('.ctd-image-upload-wrapper');
        $wrapper.find('input[type="hidden"]').val('');
        $wrapper.find('.ctd-image-preview').html('');
        $(this).hide();
    });

});
