jQuery(document).ready(function($) {
    
    $('.ctd-faq-item').each(function() {
        var $item = $(this);
        var $content = $item.find('.ctd-faq-content');
        if ($item.hasClass('active')) {
            $content.show();
        } else {
            $content.hide();
        }
    });

    // Taxonomy Slider Logic
    function initTaxonomySliders() {
        if ($('.ctd-taxonomy-slider-wrapper').length === 0) return;

        $('.ctd-taxonomy-slider-wrapper').each(function() {
            var $wrapper = $(this);
            var $slider = $wrapper.find('.ctd-taxonomy-slider');
            var $prev = $wrapper.find('.ctd-prev');
            var $next = $wrapper.find('.ctd-next');
            var $dotsContainer = $wrapper.find('.ctd-slider-dots');
            
            if ($slider.length === 0) return;

            var autoplay = $wrapper.data('autoplay') === 'yes';
            var speed = parseInt($wrapper.data('speed')) || 3000;
            var loop = $wrapper.data('loop') === 'yes';
            var autoplayInterval;

            // Update Pagination
            function updatePagination() {
                if (!$dotsContainer.length) return;
                
                var scrollLeft = $slider.scrollLeft();
                var scrollWidth = $slider[0].scrollWidth;
                var clientWidth = $slider[0].clientWidth;
                
                if (clientWidth === 0) return; // Hidden or not ready

                var maxScroll = scrollWidth - clientWidth;
                
                var $slides = $slider.find('.ctd-taxonomy-slide');
                var totalSlides = $slides.length;
                if (totalSlides === 0) return;
                
                var slideWidth = $slides.first().outerWidth(true); // outerWidth does not include flex gap usually
                if (!slideWidth) return;

                // Calculate Gap
                var gap = parseFloat($slider.css('gap')) || 0;
                var stride = slideWidth + gap;

                var slidesPerView = Math.round(clientWidth / stride);
                if (slidesPerView === 0) slidesPerView = 1;
                
                var totalPages = Math.ceil(totalSlides / slidesPerView);
                var currentPage = Math.round(scrollLeft / stride);
                
                // Safety check
                if (currentPage < 0) currentPage = 0;
                if (currentPage >= totalPages) currentPage = totalPages - 1;

                // Re-build dots if needed
                if ($dotsContainer.children().length !== totalPages) {
                    $dotsContainer.empty();
                    for (var i = 0; i < totalPages; i++) {
                        $dotsContainer.append('<div class="ctd-slider-dot" data-page="' + i + '"></div>');
                    }
                }
                
                $dotsContainer.find('.ctd-slider-dot').removeClass('active');
                $dotsContainer.find('.ctd-slider-dot').eq(currentPage).addClass('active');
            }

            // Initial calculation
            setTimeout(updatePagination, 200);
            $slider.on('scroll', function() {
                updatePagination();
            });
            $(window).on('resize', updatePagination);

            // Dot Click
            $dotsContainer.on('click', '.ctd-slider-dot', function() {
                var page = $(this).data('page');
                // Calculate Gap again to be safe
                var $firstSlide = $slider.find('.ctd-taxonomy-slide').first();
                var gap = parseFloat($slider.css('gap')) || 0;
                var stride = ($firstSlide.outerWidth(true) || 0) + gap;
                
                var scrollAmount = page * stride * Math.round($slider[0].clientWidth / stride); // Scroll by page
                // Or just scroll to specific item index? Dots usually mean pages.
                // Let's scroll to the start of that page.
                var slidesPerView = Math.round($slider[0].clientWidth / stride) || 1;
                scrollAmount = page * slidesPerView * stride;

                $slider.stop(true, false).animate({ scrollLeft: scrollAmount }, 800);
            });

            // Navigation
            $prev.on('click', function() {
                var $firstSlide = $slider.find('.ctd-taxonomy-slide').first();
                var gap = parseFloat($slider.css('gap')) || 0;
                var stride = ($firstSlide.length ? $firstSlide.outerWidth(true) : $slider[0].clientWidth) + gap;
                
                // Recalculate stride based on current slide width which might change if responsive
                // Or better, just get current slide width
                
                $slider.stop(true, false).animate({ scrollLeft: '-=' + stride }, 800);
            });

            $next.on('click', function() {
                var $firstSlide = $slider.find('.ctd-taxonomy-slide').first();
                var gap = parseFloat($slider.css('gap')) || 0;
                var stride = ($firstSlide.length ? $firstSlide.outerWidth(true) : $slider[0].clientWidth) + gap;
                
                var maxScroll = $slider[0].scrollWidth - $slider[0].clientWidth;
                
                // If close to end (tolerance 10px)
                if (loop && $slider.scrollLeft() >= maxScroll - 10) {
                     $slider.stop(true, false).animate({ scrollLeft: 0 }, 1000);
                } else {
                     $slider.stop(true, false).animate({ scrollLeft: '+=' + stride }, 800);
                }
            });

            // Autoplay
            function startAutoplay() {
                if (!autoplay) return;
                stopAutoplay();
                autoplayInterval = setInterval(function() {
                    // Check if element is still in DOM
                    if ($slider.closest('body').length === 0) {
                        stopAutoplay();
                        return;
                    }

                    // Don't scroll if user is hovering (double check)
                    if ($wrapper.is(':hover')) return;

                    var maxScroll = $slider[0].scrollWidth - $slider[0].clientWidth;
                    if ($slider.scrollLeft() >= maxScroll - 10) {
                        if (loop) {
                            $slider.stop(true, false).animate({ scrollLeft: 0 }, 1000);
                        } else {
                            stopAutoplay();
                        }
                    } else {
                        var $firstSlide = $slider.find('.ctd-taxonomy-slide').first();
                        var gap = parseFloat($slider.css('gap')) || 0;
                        var stride = ($firstSlide.length ? $firstSlide.outerWidth(true) : $slider[0].clientWidth) + gap;
                        
                        $slider.stop(true, false).animate({ scrollLeft: '+=' + stride }, 1000);
                    }
                }, speed);
            }

            function stopAutoplay() {
                if (autoplayInterval) clearInterval(autoplayInterval);
            }

            if (autoplay) {
                // Use Intersection Observer to only autoplay when visible
                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                startAutoplay();
                            } else {
                                stopAutoplay();
                            }
                        });
                    }, { threshold: 0.1 }); // 10% visible
                    
                    observer.observe($wrapper[0]);
                } else {
                    // Fallback for older browsers
                    startAutoplay();
                }

                $wrapper.on('mouseenter', stopAutoplay);
                $wrapper.on('mouseleave', function() {
                    // Only restart if still visible (simple check or just restart and let observer stop it if needed)
                    // Actually, if mouse leaves, we probably want to restart if it was supposed to be playing.
                    // But if it's out of view, observer might have stopped it? 
                    // If we restart here, we risk starting it when out of view.
                    // But mouseleave implies user was interacting with it, so it's likely in view.
                    // To be safe, we can check visibility or just rely on observer to catch it later if it scrolls out.
                    startAutoplay();
                });
                $wrapper.on('touchstart', stopAutoplay); // Stop on touch too
            }
        });
    }

    initTaxonomySliders();

    // AJAX Load More Trips
    $(document).on('click', '.ctd-load-more-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.hasClass('loading')) return;

        var page = parseInt($btn.data('page'));
        var max = parseInt($btn.data('max'));
        var atts = $btn.data('atts');
        var queryVars = $btn.data('query-vars'); // Get query vars
        var $grid = $btn.closest('.ctd-trip-grid-wrapper').find('.ctd-trip-grid');

        if (page >= max) return;

        $btn.addClass('loading').text('Loading...');

        $.ajax({
            url: ctdGlobals.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ctd_load_more_trips',
                page: page,
                atts: JSON.stringify(atts),
                query_vars: JSON.stringify(queryVars) // Pass query vars
            },
            success: function(response) {
                if (response) {
                    $grid.append(response);
                    $btn.data('page', page + 1);
                    $btn.removeClass('loading').text('Load More');
                    
                    if (page + 1 >= max) {
                        $btn.parent().html('<div class="ctd-no-more-trips" style="text-align:center; margin-top:20px; font-weight:bold; color:#555;">No more trips found, Please refine your searches</div>');
                    }
                } else {
                    $btn.parent().html('<div class="ctd-no-more-trips" style="text-align:center; margin-top:20px; font-weight:bold; color:#555;">No more trips found, Please refine your searches</div>');
                }
            },
            error: function() {
                $btn.removeClass('loading').text('Error');
            }
        });
    });

    $('.ctd-faq-header').click(function() {
        var $item = $(this).closest('.ctd-faq-item');
        if ($item.hasClass('active')) {
            $item.removeClass('active');
            $item.find('.ctd-faq-content').slideUp();
        } else {
            var $siblings = $item.siblings('.ctd-faq-item');
            $siblings.removeClass('active').find('.ctd-faq-content').slideUp();
            $item.addClass('active');
            $item.find('.ctd-faq-content').slideDown();
        }
    });

    // Itinerary Accordion
    // Handle Individual Click
    $('.ctd-itinerary-header').click(function() {
        var $item = $(this).closest('.ctd-itinerary-item');
        var $wrapper = $item.closest('.ctd-itinerary-container');
        var isExpandAll = $wrapper.find('#ctd-itinerary-expand-all').is(':checked');

        if (isExpandAll) {
            // In "Expand All" mode, clicking just toggles that specific item
            // And if we close one, we should probably uncheck "Expand All" visually
            if ($item.hasClass('active')) {
                $item.find('.ctd-itinerary-content').slideUp();
                $item.removeClass('active');
                $wrapper.find('#ctd-itinerary-expand-all').prop('checked', false);
            } else {
                $item.find('.ctd-itinerary-content').slideDown();
                $item.addClass('active');
            }
        } else {
            // In normal mode (Accordion behavior)
            if ($item.hasClass('active')) {
                $item.find('.ctd-itinerary-content').slideUp();
                $item.removeClass('active');
            } else {
                // Close siblings
                var $siblings = $item.siblings('.ctd-itinerary-item');
                $siblings.find('.ctd-itinerary-content').slideUp();
                $siblings.removeClass('active');
                
                // Open this one
                $item.find('.ctd-itinerary-content').slideDown();
                $item.addClass('active');
            }
        }
    });

    // Handle Expand All Toggle
    $('#ctd-itinerary-expand-all').change(function() {
        var $wrapper = $(this).closest('.ctd-itinerary-container');
        var $items = $wrapper.find('.ctd-itinerary-item');
        
        if ($(this).is(':checked')) {
            // Expand All
            $items.addClass('active');
            $items.find('.ctd-itinerary-content').slideDown();
        } else {
            // Collapse All (except maybe first one? Or just close all?)
            // Usually "Collapse All" closes everything.
            // But let's keep the first one open if user wants "reset" state, or just close all.
            // Let's close all for clean "Collapse" action.
            $items.removeClass('active');
            $items.find('.ctd-itinerary-content').slideUp();
            
            // Optional: Re-open first one?
            // $items.first().addClass('active').find('.ctd-itinerary-content').slideDown();
        }
    });

    // Expand All Toggles (if implemented)
    $('.ctd-expand-all').click(function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $(target).find('.ctd-itinerary-content, .ctd-faq-content').slideDown();
        $(target).find('.ctd-itinerary-item, .ctd-faq-item').addClass('active');
    });

    // Initialize GLightbox
    if (typeof GLightbox !== 'undefined') {
        const lightbox = GLightbox({
            selector: '.ctd-lightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });

        // Initialize Video Lightbox separately if needed, or share the same instance if selectors match
        // But for video button specifically, we want to ensure it picks up the video gallery items
        const videoLightbox = GLightbox({
            selector: '.ctd-lightbox-video',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
            plyr: {
                config: {
                    ratio: '16:9', // or '4:3'
                    muted: false,
                    hideControls: true,
                    youtube: {
                        noCookie: true,
                        rel: 0,
                        showinfo: 0,
                        iv_load_policy: 3
                    },
                    vimeo: {
                        byline: false,
                        portrait: false,
                        title: false,
                        speed: true,
                        transparent: false
                    }
                }
            }
        });
    }

    // Auto-Scroll Gallery Carousel on Mobile/Tablet
    var galleryIntervals = [];

    function startGalleryAutoScroll() {
        var $gallery = $('.ctd-gallery-grid-layout');
        
        // Clear existing intervals
        galleryIntervals.forEach(function(interval) {
            clearInterval(interval);
        });
        galleryIntervals = [];

        // Only run if it's in carousel mode (display: flex)
        if ($gallery.css('display') !== 'flex') {
            return;
        }

        $gallery.each(function() {
            var $this = $(this);
            
            // Clone items for infinite loop if not already cloned
            if ($this.find('.ctd-cloned').length === 0) {
                var $originalItems = $this.find('.ctd-gallery-item');
                // We clone all items to ensure we have enough buffer for smooth scrolling
                var $clones = $originalItems.clone().addClass('ctd-cloned').removeClass('item-1 item-2 item-3 item-4');
                $this.append($clones);
            }

            var $items = $this.find('.ctd-gallery-item'); // All items including clones
            var $originalItems = $items.not('.ctd-cloned');
            var itemCount = $originalItems.length;
            var $dotsContainer = $this.next('.ctd-gallery-dots');
            
            // Generate Dots (Only for original items)
            $dotsContainer.empty();
            $originalItems.each(function(index) {
                $dotsContainer.append('<div class="ctd-dot ' + (index === 0 ? 'active' : '') + '" data-index="' + index + '"></div>');
            });

            var isPaused = false;

            // Pause on hover or touch
            $this.on('mouseenter touchstart', function() {
                isPaused = true;
            }).on('mouseleave touchend', function() {
                isPaused = false;
            });

            // Update active dot on scroll
            $this.off('scroll').on('scroll', function() {
                var slideWidth = $items.first().outerWidth(true);
                var scrollLeft = $(this).scrollLeft();
                var index = Math.round(scrollLeft / slideWidth);
                
                // Modulo to map cloned items back to original dots
                var dotIndex = index % itemCount;
                $dotsContainer.find('.ctd-dot').removeClass('active').eq(dotIndex).addClass('active');
                
                // Infinite Loop Jump Logic (Manual Scroll)
                var singleSetWidth = itemCount * slideWidth;
                // If we scrolled past the originals into clones significantly, jump back
                // But only if not currently animating (to avoid conflict) - simplistic check
                if (scrollLeft >= singleSetWidth) {
                     // We don't jump on manual scroll usually to keep it natural, 
                     // but for true infinite feel we might. 
                     // For now, let's rely on the interval for the "loop" effect mostly.
                     // Or silently jump if user scrolls WAY too far.
                     if (scrollLeft >= singleSetWidth + (slideWidth / 2)) {
                         $(this).scrollLeft(scrollLeft - singleSetWidth);
                     }
                }
            });

            // Dot Click Navigation (Smoother)
            $dotsContainer.off('click').on('click', '.ctd-dot', function(e) {
                e.preventDefault();
                e.stopPropagation(); 
                var index = $(this).data('index');
                var slideWidth = $items.first().outerWidth(true);
                var targetScroll = index * slideWidth;
                
                $this.animate({ scrollLeft: targetScroll }, 600, 'swing');
            });

            var interval = setInterval(function() {
                if (isPaused) return;
                
                if ($this.css('display') !== 'flex') return;

                var slideWidth = $items.first().outerWidth(true);
                var singleSetWidth = itemCount * slideWidth;
                var currentScroll = $this.scrollLeft();
                var nextScroll = currentScroll + slideWidth;

                // Infinite Loop Logic
                // If next scroll would hit the start of clones (or past it)
                // We animate to that clone, then instantly jump back to 0
                if (Math.ceil(currentScroll + slideWidth) >= singleSetWidth - 5) { // Tolerance added
                     // Animate to the first cloned item (visually same as first item)
                     $this.animate({ scrollLeft: singleSetWidth }, 800, 'swing', function() {
                         // Callback: Jump instantly to 0 (real first item) WITHOUT animation
                         $this.scrollLeft(0);
                     });
                } else {
                    // Normal scroll
                    $this.animate({ scrollLeft: nextScroll }, 800, 'swing');
                }
                
            }, 4000); 
            
            galleryIntervals.push(interval);
        });
    }

    // Run on load and resize
    startGalleryAutoScroll();
    
    var resizeTimer;
    $(window).resize(function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            startGalleryAutoScroll();
        }, 250);
    });

    // Disable Lightbox on Mobile/Tablet via JS as well
    $('.ctd-lightbox').on('click', function(e) {
        if ($(window).width() <= 1024) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Cleanup Empty Trip Fact Items
    // Hides .trip-fact-item blocks if they don't contain a valid image or text
    function cleanupEmptyFactItems() {
        $('.trip-fact-item').each(function() {
            var $item = $(this);
            var hasImage = false;
            var hasText = false;

            // Check for Image
            var $img = $item.find('img');
            if ($img.length > 0) {
                var src = $img.attr('src');
                if (src && src.trim() !== '' && src !== 'null') {
                    hasImage = true;
                }
            }

            // Check for Text (excluding whitespace)
            var text = $item.text().trim();
            if (text.length > 0) {
                hasText = true;
            }

            // If neither valid image nor text, hide the container
            if (!hasImage && !hasText) {
                $item.hide();
                // Also hide parent if it's a direct GB container and becomes empty?
                // For now, just hiding the item itself as requested.
            }
        });
    }
    
    cleanupEmptyFactItems();

    function cleanupEmptyDurationBlocks() {
        $('.ctd-title-duration-night').each(function() {
            var $block = $(this);
            var val = $block.find('.duration').text().trim();
            if (!val) {
                $block.hide();
            }
        });
    }
    cleanupEmptyDurationBlocks();
    
    function adjustDurationGrid() {
        $('.duration').each(function() {
            var $container = $(this);
            var $night = $container.find('.ctd-title-duration-night');
            if ($night.length && ($night.is(':hidden') || $.trim($night.find('.duration').text()) === '')) {
                $container.css('grid-template-columns', '1fr');
            } else {
                $container.css('grid-template-columns', 'repeat(2, minmax(0, 1fr))');
            }
        });
    }
    adjustDurationGrid();

    function cleanupEmptyServiceItems() {
        $('.extra-services-item').each(function() {
            var $item = $(this);
            var hasImage = false;
            var $img = $item.find('img');
            if ($img.length > 0) {
                var src = $img.attr('src');
                if (src && src.trim() !== '' && src !== 'null') {
                    hasImage = true;
                }
            }
            var hasName = false;
            var nameText = $item.find('.service-name, .ctd-service-name').text().trim();
            if (nameText.length > 0) {
                hasName = true;
            }
            var hasPrice = false;
            var $priceEl = $item.find('[class*=\"price\"], .ctd-service-price');
            var priceText = $priceEl.text().trim();
            if (priceText.length > 0) {
                hasPrice = /\d/.test(priceText) || priceText.length > 0;
            }
            if (!hasImage && !hasName && !hasPrice) {
                $item.hide();
            }
        });
    }
    cleanupEmptyServiceItems();

    function toggleExtraServicesSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.extraServicesEnabled || 0, 10) : 0;
        $('.extra-services, .extra-services-list').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleExtraServicesSection();

    function toggleFlightSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.flightsEnabled || 0, 10) : 0;
        $('.flight-details').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleFlightSection();

    function toggleMealsSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.mealsEnabled || 0, 10) : 0;
        $('.meals-details').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleMealsSection();
    
    function toggleDownloadsSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.downloadsEnabled || 0, 10) : 0;
        $('.download-section').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleDownloadsSection();
    
    function toggleMoreInfoSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.moreInfoEnabled || 0, 10) : 0;
        $('.more-info-section').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleMoreInfoSection();
    
    function toggleBookingSection() {
        var enabled = typeof ctdGlobals !== 'undefined' ? parseInt(ctdGlobals.bookingEnabled || 0, 10) : 0;
        $('.booking-button').each(function() {
            $(this).toggle(enabled === 1);
        });
    }
    toggleBookingSection();

    // Initialize Select2 for Search Bar
    if ($.fn.select2) {
        $('.ctd-select2').select2({
            width: '100%',
            minimumResultsForSearch: 0 // Always show search box
        });
    }

});

/* ===== Trip pricing + trip archive filter panel =====
   Moved out of the child theme so the plugin is self-contained. ===== */

document.addEventListener('DOMContentLoaded', function() {
    var containers = document.querySelectorAll('.pricing-section .pricing-container');
    containers.forEach(function(c) {
        var priceEl = c.querySelector('.price-wrapper .gb-text.price');
        var hasPrice = !!priceEl && priceEl.textContent.trim().length > 0;
        if (!hasPrice) {
            c.style.display = 'none';
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const filterButton = document.getElementById('open-trip-filters');
    const filterPanel = document.querySelector('.cs-sidebar-filter');
    
    if (!filterButton || !filterPanel) return;
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'cs-filter-overlay';
    document.body.appendChild(overlay);
    
    // Check if close button exists, if not create it
    let closeButton = filterPanel.querySelector('.cs-filter-close');
    if (!closeButton) {
        closeButton = document.createElement('button');
        closeButton.className = 'cs-filter-close';
        closeButton.innerHTML = '&times;';
        closeButton.setAttribute('aria-label', 'Close filters');
        filterPanel.insertBefore(closeButton, filterPanel.firstChild);
    }
    
    // Open panel function
    function openFilterPanel() {
        filterPanel.classList.add('active');
        overlay.classList.add('active');
        document.body.classList.add('filter-panel-open');
    }
    
    // Close panel function
    function closeFilterPanel() {
        filterPanel.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('filter-panel-open');
    }
    
    // Event Listeners
    
    // 1. Open with button click
    filterButton.addEventListener('click', openFilterPanel);
    
    // 2. Close with close button
    closeButton.addEventListener('click', closeFilterPanel);
    
    // 3. Close with overlay click
    overlay.addEventListener('click', closeFilterPanel);
    
    // 4. Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFilterPanel();
        }
    });
    
    // 5. Close when clicking outside panel (optional extra safety)
    filterPanel.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // 6. Close when a filter is applied (if your filter uses AJAX)
    // You may need to adjust this based on how your filter works
    const filterElements = filterPanel.querySelectorAll('select, input, button[type="submit"]');
    filterElements.forEach(element => {
        if (element.tagName === 'SELECT' || element.type === 'submit') {
            element.addEventListener('change', function() {
                // Close panel after a short delay to allow filter to apply
                setTimeout(closeFilterPanel, 300);
            });
        }
    });
});
