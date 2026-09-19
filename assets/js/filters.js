jQuery(document).ready(function($) {
    
    // Accordion Toggle
    $('.ctd-filter-title').on('click', function() {
        var group = $(this).closest('.ctd-filter-group');
        var content = $(this).next('.ctd-filter-content');
        
        group.toggleClass('closed');
        group.toggleClass('active');
        content.slideToggle();
    });

    // Show More / Less
    $('.ctd-show-more').on('click', function(e) {
        e.preventDefault();
        var link = $(this);
        var list = link.prev('.ctd-filter-list');
        var hiddenItems = list.find('.ctd-hidden-item, .ctd-was-hidden');
        
        if (link.hasClass('expanded')) {
            // Show Less
            list.find('.ctd-was-hidden').addClass('ctd-hidden-item').removeClass('ctd-was-hidden').slideUp();
            link.text(link.data('text-more'));
            link.removeClass('expanded');
        } else {
            // Show More
            list.find('.ctd-hidden-item').removeClass('ctd-hidden-item').addClass('ctd-was-hidden').slideDown();
            link.text(link.data('text-less'));
            link.addClass('expanded');
        }
    });

    // Range Sliders
    $('.ctd-range-slider').each(function() {
        var slider = $(this);
        var min = parseInt(slider.data('min'));
        var max = parseInt(slider.data('max'));
        var currentMin = parseInt(slider.data('current-min'));
        var currentMax = parseInt(slider.data('current-max'));
        var inputs = slider.next('.ctd-range-inputs');
        var inputMin = inputs.find('.ctd-input-min');
        var inputMax = inputs.find('.ctd-input-max');
        var displayMin = inputs.find('.ctd-min-val');
        var displayMax = inputs.find('.ctd-max-val');
        
        // Check if duration slider (has "Days" text in display)
        var isDuration = displayMin.text().indexOf('Days') !== -1;

        slider.slider({
            range: true,
            min: min,
            max: max,
            values: [ currentMin, currentMax ],
            slide: function( event, ui ) {
                inputMin.val( ui.values[0] );
                inputMax.val( ui.values[1] );
                
                if (isDuration) {
                    displayMin.text( ui.values[0] + ' Days' );
                    displayMax.text( ui.values[1] + ' Days' );
                } else {
                    displayMin.text( ui.values[0] );
                    displayMax.text( ui.values[1] );
                }
            }
        });
    });

    // Initialize Select2
    if ($.fn.select2) {
        $('.ctd-select2').select2({
            width: '100%',
            minimumResultsForSearch: Infinity // Hide search box for sort dropdown usually
        });
        
        // Auto-submit for sort dropdown
        $('.ctd-sort-form .ctd-select2').on('select2:select', function (e) {
            $(this).closest('form').submit();
        });
    }

});