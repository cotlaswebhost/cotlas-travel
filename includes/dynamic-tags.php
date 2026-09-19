<?php
/**
 * GenerateBlocks Dynamic Tags Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function() {
    if ( ! class_exists( 'GenerateBlocks_Register_Dynamic_Tag' ) ) {
        return;
    }

    // 1. Trip Meta Fields (Text/Content)
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Meta Data', 'cotlas-travel' ),
        'tag'      => 'ctd_trip_meta',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'ctd_trip_code',
                'options' => [
                    // General
                    ['value' => 'ctd_trip_code', 'label' => __( 'Trip Code', 'cotlas-travel' )],
                    ['value' => 'ctd_duration_days', 'label' => __( 'Duration (Days)', 'cotlas-travel' )],
                    ['value' => 'ctd_duration_nights', 'label' => __( 'Duration (Nights)', 'cotlas-travel' )],
                    ['value' => 'ctd_min_age', 'label' => __( 'Min Age', 'cotlas-travel' )],
                    ['value' => 'ctd_max_age', 'label' => __( 'Max Age', 'cotlas-travel' )],
                    ['value' => 'ctd_min_travellers', 'label' => __( 'Min Travellers', 'cotlas-travel' )],
                    ['value' => 'ctd_total_seats', 'label' => __( 'Total Seats', 'cotlas-travel' )],
                    
                    // Titles & Content
                    ['value' => 'ctd_overview_title', 'label' => __( 'Overview Title', 'cotlas-travel' )],
                    ['value' => 'ctd_overview_content', 'label' => __( 'Overview Content', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_highlights_title', 'label' => __( 'Highlights Title', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_itinerary_title', 'label' => __( 'Itinerary Title', 'cotlas-travel' )],
                    ['value' => 'ctd_itinerary_description', 'label' => __( 'Itinerary Description', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_inc_exc_title', 'label' => __( 'Includes/Excludes Title', 'cotlas-travel' )],
                    ['value' => 'ctd_cost_includes_title', 'label' => __( 'Cost Includes Title', 'cotlas-travel' )],
                    ['value' => 'ctd_cost_excludes_title', 'label' => __( 'Cost Excludes Title', 'cotlas-travel' )],
                    // Repeater lists handled via Trip Component
                    
                    ['value' => 'ctd_trip_info_title', 'label' => __( 'Trip Info Title', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_map_title', 'label' => __( 'Map Title', 'cotlas-travel' )],
                    ['value' => 'ctd_map_iframe', 'label' => __( 'Map Iframe', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_extra_services_title', 'label' => __( 'Extra Services Title', 'cotlas-travel' )],
                    ['value' => 'ctd_extra_services_desc', 'label' => __( 'Extra Services Desc', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_more_info_title', 'label' => __( 'More Info Title', 'cotlas-travel' )],
                    ['value' => 'ctd_more_info_content', 'label' => __( 'More Info Content', 'cotlas-travel' )],
                    
                    // Flights
                    ['value' => 'ctd_flights_title', 'label' => __( 'Flights Section Title', 'cotlas-travel' )],
                    
                    // Meals
                    ['value' => 'ctd_meals_title', 'label' => __( 'Meals Section Title', 'cotlas-travel' )],
                    ['value' => 'ctd_faqs_title', 'label' => __( 'FAQs Section Title', 'cotlas-travel' )],
                    ['value' => 'ctd_downloads_title', 'label' => __( 'Downloads Section Title', 'cotlas-travel' )],
                    
                    ['value' => 'ctd_custom_booking_link', 'label' => __( 'Custom Booking Link', 'cotlas-travel' )],
                    ['value' => 'ctd_itinerary_pdf', 'label' => __( 'Itinerary PDF URL', 'cotlas-travel' )],
                ],
            ],
        ],
        'return' => 'ctd_trip_meta_dynamic_tag',
    ]);

    // 2. Trip Components (Shortcode Wrappers)
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Component', 'cotlas-travel' ),
        'tag'      => 'ctd_trip_component',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'component' => [
                'type'    => 'select',
                'label'   => __( 'Component', 'cotlas-travel' ),
                'default' => 'highlights',
                'options' => [
                    ['value' => 'highlights', 'label' => __( 'Highlights List', 'cotlas-travel' )],
                    ['value' => 'itinerary', 'label' => __( 'Itinerary Accordion', 'cotlas-travel' )],
                    ['value' => 'facts', 'label' => __( 'Trip Facts Grid', 'cotlas-travel' )],
                    ['value' => 'pricing', 'label' => __( 'Pricing Table', 'cotlas-travel' )],
                    ['value' => 'gallery', 'label' => __( 'Image Gallery', 'cotlas-travel' )],
                    ['value' => 'video_gallery', 'label' => __( 'Video Gallery', 'cotlas-travel' )],
                    ['value' => 'faqs', 'label' => __( 'FAQs', 'cotlas-travel' )],
                    ['value' => 'downloads', 'label' => __( 'File Downloads', 'cotlas-travel' )],
                    ['value' => 'extra_services', 'label' => __( 'Extra Services List', 'cotlas-travel' )],
                    ['value' => 'cost_includes', 'label' => __( 'Cost Includes List', 'cotlas-travel' )],
                    ['value' => 'cost_excludes', 'label' => __( 'Cost Excludes List', 'cotlas-travel' )],
                    ['value' => 'map_image', 'label' => __( 'Map Image (URL)', 'cotlas-travel' )],
                    ['value' => 'search_bar', 'label' => __( 'Search Bar', 'cotlas-travel' )],
                ],
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'Custom CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
            'fields' => [
                'type'    => 'text',
                'label'   => __( 'Search Fields (keyword, destination, activity, trip_type)', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_trip_component_dynamic_tag',
    ]);

    // 3. Repeater Item - FAQ
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip FAQ Info', 'cotlas-travel' ),
        'tag'      => 'ctd_faq_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'question',
                'options' => [
                    ['value' => 'question', 'label' => __( 'Question', 'cotlas-travel' )],
                    ['value' => 'answer', 'label' => __( 'Answer', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Item Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_faq_item_dynamic_tag',
    ]);

    // 4. Repeater Item - Itinerary
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Itinerary Info', 'cotlas-travel' ),
        'tag'      => 'ctd_itinerary_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'title',
                'options' => [
                    ['value' => 'title', 'label' => __( 'Day Title', 'cotlas-travel' )],
                    ['value' => 'content', 'label' => __( 'Description', 'cotlas-travel' )],
                    ['value' => 'day_label', 'label' => __( 'Day Label (e.g. Day 1)', 'cotlas-travel' )],
                    ['value' => 'image', 'label' => __( 'Day Image (URL)', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Item Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_itinerary_item_dynamic_tag',
    ]);

    // 5. Repeater Item - Trip Fact
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Fact Info', 'cotlas-travel' ),
        'tag'      => 'ctd_fact_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'value',
                'options' => [
                    ['value' => 'label', 'label' => __( 'Label', 'cotlas-travel' )],
                    ['value' => 'value', 'label' => __( 'Value', 'cotlas-travel' )],
                    ['value' => 'image', 'label' => __( 'Image (Icon)', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Item Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_fact_item_dynamic_tag',
    ]);

    // 6. Repeater Item - Extra Service
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Service Info', 'cotlas-travel' ),
        'tag'      => 'ctd_service_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'name',
                'options' => [
                    ['value' => 'name', 'label' => __( 'Service Name', 'cotlas-travel' )],
                    ['value' => 'price', 'label' => __( 'Price', 'cotlas-travel' )],
                    ['value' => 'description', 'label' => __( 'Description', 'cotlas-travel' )],
                    ['value' => 'pricing_type', 'label' => __( 'Pricing Type', 'cotlas-travel' )],
                    ['value' => 'image', 'label' => __( 'Image URL', 'cotlas-travel' )],
                    ['value' => 'image_id', 'label' => __( 'Image ID', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Item Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_service_item_dynamic_tag',
    ]);
    
    // 7. Repeater Item - Pricing Package
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Package Info', 'cotlas-travel' ),
        'tag'      => 'ctd_package_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'title',
                'options' => [
                    ['value' => 'title', 'label' => __( 'Package Title', 'cotlas-travel' )],
                    ['value' => 'general_desc', 'label' => __( 'General Description', 'cotlas-travel' )],
                    ['value' => 'price', 'label' => __( 'Price (Requires Category ID)', 'cotlas-travel' )],
                    ['value' => 'dates', 'label' => __( 'Fixed Dates (Requires Enable Fixed Dates)', 'cotlas-travel' )],
                    ['value' => 'dates_list', 'label' => __( 'Fixed Dates as HTML List (Icon + One Per Line)', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Package Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
            'cat_id' => [
                'type'    => 'text',
                'label'   => __( 'Price Category ID (For Price)', 'cotlas-travel' ),
                'default' => '',
            ],
            'date_format' => [
                'type'    => 'text',
                'label'   => __( 'Date Format (For Fixed Dates)', 'cotlas-travel' ),
                'default' => 'd M Y',
            ],
            'separator' => [
                'type'    => 'text',
                'label'   => __( 'Date Separator (For Fixed Dates)', 'cotlas-travel' ),
                'default' => ', ',
            ],
            'dateindex' => [
                'type'    => 'text',
                'label'   => __( 'Single Date Number (1, 2, 3... - Leave empty for all)', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_package_item_dynamic_tag',
    ]);

    // 8. Gallery Image Item
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Gallery Image', 'cotlas-travel' ),
        'tag'      => 'ctd_gallery_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'url',
                'options' => [
                    ['value' => 'url', 'label' => __( 'Image URL (Full)', 'cotlas-travel' )],
                    ['value' => 'id', 'label' => __( 'Image ID', 'cotlas-travel' )],
                    ['value' => 'alt', 'label' => __( 'Alt Text', 'cotlas-travel' )],
                    ['value' => 'caption', 'label' => __( 'Caption', 'cotlas-travel' )],
                    ['value' => 'title', 'label' => __( 'Title', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Image Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_gallery_item_dynamic_tag',
    ]);

    // 9. Taxonomy Term Data
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Taxonomy Term Data', 'cotlas-travel' ),
        'tag'      => 'ctd_taxonomy_term',
        'type'     => 'term-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'title',
                'options' => [
                    ['value' => 'title', 'label' => __( 'Term Title', 'cotlas-travel' )],
                    ['value' => 'description', 'label' => __( 'Term Description', 'cotlas-travel' )],
                    ['value' => 'short_description', 'label' => __( 'Short Description', 'cotlas-travel' )],
                    ['value' => 'image', 'label' => __( 'Term Image URL', 'cotlas-travel' )],
                    ['value' => 'url', 'label' => __( 'Term URL', 'cotlas-travel' )],
                    ['value' => 'count', 'label' => __( 'Post Count', 'cotlas-travel' )],
                ],
            ],
        ],
        'return' => 'ctd_taxonomy_term_dynamic_tag',
    ]);
    
    // 10. Taxonomy Slider Component
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Taxonomy Slider', 'cotlas-travel' ),
        'tag'      => 'ctd_taxonomy_slider',
        'type'     => 'post-meta', // It's not really post meta, but we return HTML
        'supports' => [],
        'options'  => [
            'taxonomy' => [
                'type'    => 'select',
                'label'   => __( 'Taxonomy', 'cotlas-travel' ),
                'default' => 'destination',
                'options' => [
                    ['value' => 'destination', 'label' => __( 'Destinations', 'cotlas-travel' )],
                    ['value' => 'activities', 'label' => __( 'Activities', 'cotlas-travel' )],
                    ['value' => 'trip_types', 'label' => __( 'Trip Types', 'cotlas-travel' )],
                ],
            ],
            'count' => [
                'type'    => 'text',
                'label'   => __( 'Number of Terms (-1 for all)', 'cotlas-travel' ),
                'default' => '-1',
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
            'autoplay' => [
                'type'    => 'select',
                'label'   => __( 'Autoplay', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'speed' => [
                'type'    => 'text',
                'label'   => __( 'Autoplay Speed (ms)', 'cotlas-travel' ),
                'default' => '3000',
            ],
            'loop' => [
                'type'    => 'select',
                'label'   => __( 'Infinite Loop', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'hide_empty' => [
                'type'    => 'select',
                'label'   => __( 'Hide Empty Terms', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
        ],
        'return' => 'ctd_taxonomy_slider_dynamic_tag',
    ]);

    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Flight Info', 'cotlas-travel' ),
        'tag'      => 'ctd_flight_info',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'direction' => [
                'type'    => 'select',
                'label'   => __( 'Direction', 'cotlas-travel' ),
                'default' => 'outbound',
                'options' => [
                    ['value' => 'outbound', 'label' => __( 'Outbound', 'cotlas-travel' )],
                    ['value' => 'inbound', 'label' => __( 'Inbound', 'cotlas-travel' )],
                ],
            ],
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'airline',
                'options' => [
                    ['value' => 'airline', 'label' => __( 'Airline', 'cotlas-travel' )],
                    ['value' => 'from', 'label' => __( 'From', 'cotlas-travel' )],
                    ['value' => 'departure_time', 'label' => __( 'Departure Time', 'cotlas-travel' )],
                    ['value' => 'to', 'label' => __( 'To', 'cotlas-travel' )],
                    ['value' => 'arrival_time', 'label' => __( 'Arrival Time', 'cotlas-travel' )],
                ],
            ],
        ],
        'return' => 'ctd_flight_info_dynamic_tag',
    ]);
    
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Meal Info', 'cotlas-travel' ),
        'tag'      => 'ctd_meal_item',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'field' => [
                'type'    => 'select',
                'label'   => __( 'Field', 'cotlas-travel' ),
                'default' => 'value',
                'options' => [
                    ['value' => 'value', 'label' => __( 'Value', 'cotlas-travel' )],
                ],
            ],
            'index' => [
                'type'    => 'text',
                'label'   => __( 'Item Number (1, 2, 3...)', 'cotlas-travel' ),
                'default' => '1',
            ],
        ],
        'return' => 'ctd_meal_item_dynamic_tag',
    ]);

    // 11. Trip Slider
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Slider', 'cotlas-travel' ),
        'tag'      => 'ctd_trip_slider',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'destination' => [
                'type'    => 'text',
                'label'   => __( 'Destination Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'activity' => [
                'type'    => 'text',
                'label'   => __( 'Activity Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'trip_type' => [
                'type'    => 'text',
                'label'   => __( 'Trip Type Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'count' => [
                'type'    => 'text',
                'label'   => __( 'Number of Posts', 'cotlas-travel' ),
                'default' => '8',
            ],
            'autoplay' => [
                'type'    => 'select',
                'label'   => __( 'Autoplay', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'speed' => [
                'type'    => 'text',
                'label'   => __( 'Autoplay Speed (ms)', 'cotlas-travel' ),
                'default' => '3000',
            ],
            'loop' => [
                'type'    => 'select',
                'label'   => __( 'Infinite Loop', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'navigation' => [
                'type'    => 'select',
                'label'   => __( 'Show Navigation', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'pagination' => [
                'type'    => 'select',
                'label'   => __( 'Show Pagination (Dots)', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_trip_slider_dynamic_tag',
    ]);

    // 12. Trip Grid
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Trip Grid', 'cotlas-travel' ),
        'tag'      => 'ctd_trip_grid',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'destination' => [
                'type'    => 'text',
                'label'   => __( 'Destination Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'activity' => [
                'type'    => 'text',
                'label'   => __( 'Activity Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'trip_type' => [
                'type'    => 'text',
                'label'   => __( 'Trip Type Slugs (comma separated)', 'cotlas-travel' ),
                'default' => '',
            ],
            'count' => [
                'type'    => 'text',
                'label'   => __( 'Number of Posts', 'cotlas-travel' ),
                'default' => '8',
            ],
            'ajax_load' => [
                'type'    => 'select',
                'label'   => __( 'Enable AJAX Load More', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'current_query' => [
                'type'    => 'select',
                'label'   => __( 'Use Current Query (Archive)', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'columns_desktop' => [
                'type'    => 'text',
                'label'   => __( 'Columns (Desktop)', 'cotlas-travel' ),
                'default' => '4',
            ],
            'columns_tablet' => [
                'type'    => 'text',
                'label'   => __( 'Columns (Tablet)', 'cotlas-travel' ),
                'default' => '3',
            ],
            'columns_mobile' => [
                'type'    => 'text',
                'label'   => __( 'Columns (Mobile)', 'cotlas-travel' ),
                'default' => '1',
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_trip_grid_dynamic_tag',
    ]);

    // 13. Hero Image
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Hero Image', 'cotlas-travel' ),
        'tag'      => 'ctd_hero_image',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'index' => [
                'type'    => 'select',
                'label'   => __( 'Image Index', 'cotlas-travel' ),
                'default' => '1',
                'options' => [
                    ['value' => '1', 'label' => '1'],
                    ['value' => '2', 'label' => '2'],
                    ['value' => '3', 'label' => '3'],
                    ['value' => '4', 'label' => '4'],
                ],
            ],
            'return_type' => [
                'type'    => 'select',
                'label'   => __( 'Return Type', 'cotlas-travel' ),
                'default' => 'url',
                'options' => [
                    ['value' => 'url', 'label' => __( 'Image URL', 'cotlas-travel' )],
                    ['value' => 'id', 'label' => __( 'Image ID', 'cotlas-travel' )],
                    ['value' => 'tag', 'label' => __( 'Image Tag (HTML)', 'cotlas-travel' )],
                ],
            ],
        ],
        'return' => 'ctd_hero_image_dynamic_tag',
    ]);

    // 14. Destination Card
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Destination Card', 'cotlas-travel' ),
        'tag'      => 'ctd_destination_card',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'taxonomy' => [
                'type'    => 'select',
                'label'   => __( 'Taxonomy', 'cotlas-travel' ),
                'default' => 'destination',
                'options' => [
                    ['value' => 'destination', 'label' => __( 'Destinations', 'cotlas-travel' )],
                    ['value' => 'activities', 'label' => __( 'Activities', 'cotlas-travel' )],
                    ['value' => 'trip_types', 'label' => __( 'Trip Types', 'cotlas-travel' )],
                ],
            ],
            'term_id' => [
                'type'    => 'text',
                'label'   => __( 'Term ID (Leave empty for current term or newly launched)', 'cotlas-travel' ),
                'default' => '',
            ],
            'newly_launched' => [
                'type'    => 'select',
                'label'   => __( 'Fetch Newly Launched?', 'cotlas-travel' ),
                'default' => 'no',
                'options' => [
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                ],
            ],
            'offset' => [
                'type'    => 'text',
                'label'   => __( 'Offset (For Newly Launched)', 'cotlas-travel' ),
                'default' => '0',
            ],
            'show_image' => [
                'type'    => 'select',
                'label'   => __( 'Show Image', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'show_title' => [
                'type'    => 'select',
                'label'   => __( 'Show Title', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'show_count' => [
                'type'    => 'select',
                'label'   => __( 'Show Post Count', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'show_desc' => [
                'type'    => 'select',
                'label'   => __( 'Show Description', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'show_short_desc' => [
                'type'    => 'select',
                'label'   => __( 'Show Short Description', 'cotlas-travel' ),
                'default' => 'yes',
                'options' => [
                    ['value' => 'yes', 'label' => __( 'Yes', 'cotlas-travel' )],
                    ['value' => 'no', 'label' => __( 'No', 'cotlas-travel' )],
                ],
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_destination_card_dynamic_tag',
    ]);

    // 15. Gallery Component
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Gallery Component', 'cotlas-travel' ),
        'tag'      => 'ctd_gallery_component',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_gallery_component_dynamic_tag',
    ]);

    // 16. Itinerary Download Button
    new GenerateBlocks_Register_Dynamic_Tag([
        'title'    => __( 'Itinerary Download Button', 'cotlas-travel' ),
        'tag'      => 'ctd_download_itinerary_btn',
        'type'     => 'post-meta',
        'supports' => [],
        'options'  => [
            'label' => [
                'type'    => 'text',
                'label'   => __( 'Button Label', 'cotlas-travel' ),
                'default' => 'Download Itinerary',
            ],
            'class' => [
                'type'    => 'text',
                'label'   => __( 'CSS Class', 'cotlas-travel' ),
                'default' => '',
            ],
        ],
        'return' => 'ctd_download_itinerary_btn_dynamic_tag',
    ]);

});

function ctd_download_itinerary_btn_dynamic_tag( $options, $block, $instance ) {
    $atts = '';
    foreach( ['label', 'class'] as $key ) {
        if ( isset( $options[ $key ] ) && $options[ $key ] !== '' ) {
            $atts .= ' ' . $key . '="' . esc_attr( $options[ $key ] ) . '"';
        }
    }
    return do_shortcode( '[ctd_download_itinerary_btn' . $atts . ']' );
}

function ctd_gallery_component_dynamic_tag( $options, $block, $instance ) {
    $class = isset( $options['class'] ) ? $options['class'] : '';
    $atts = '';
    if ( $class ) {
        $atts = ' class="' . esc_attr( $class ) . '"';
    }
    return do_shortcode( '[ctd_gallery' . $atts . ']' );
}

function ctd_trip_slider_dynamic_tag( $options, $block, $instance ) {
    $atts = '';
    foreach( ['destination', 'activity', 'trip_type', 'count', 'autoplay', 'speed', 'loop', 'navigation', 'pagination', 'class'] as $key ) {
        if ( isset( $options[ $key ] ) && $options[ $key ] !== '' ) {
            $atts .= ' ' . $key . '="' . esc_attr( $options[ $key ] ) . '"';
        }
    }
    return do_shortcode( '[ctd_trip_slider' . $atts . ']' );
}

function ctd_trip_grid_dynamic_tag( $options, $block, $instance ) {
    $atts = '';
    foreach( ['destination', 'activity', 'trip_type', 'count', 'ajax_load', 'current_query', 'columns_desktop', 'columns_tablet', 'columns_mobile', 'class'] as $key ) {
        if ( isset( $options[ $key ] ) && $options[ $key ] !== '' ) {
            $atts .= ' ' . $key . '="' . esc_attr( $options[ $key ] ) . '"';
        }
    }
    return do_shortcode( '[ctd_trip_grid' . $atts . ']' );
}
function ctd_gallery_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'url';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    
    $item = ctd_get_repeater_item( 'ctd_gallery', $index, $options, $instance );
    if ( ! $item ) return '';
    
    $img_id = is_array( $item ) ? ( isset( $item['ID'] ) ? $item['ID'] : '' ) : $item;
    if ( ! $img_id ) return '';

    switch ( $field ) {
        case 'url':
            return wp_get_attachment_image_url( $img_id, 'full' );
        case 'id':
            return $img_id;
        case 'alt':
            return get_post_meta( $img_id, '_wp_attachment_image_alt', true );
        case 'caption':
            return wp_get_attachment_caption( $img_id );
        case 'title':
            return get_the_title( $img_id );
    }
    return '';
}


/**
 * Helper to get repeater item
 */
function ctd_get_repeater_item( $meta_key, $index, $options = array(), $instance = null ) {
    if ( class_exists( 'GenerateBlocks_Dynamic_Tags' ) ) {
        $post_id = GenerateBlocks_Dynamic_Tags::get_id( $options, 'post', $instance );
    } else {
        $post_id = get_the_ID();
    }
    if ( ! $post_id ) return null;
    
    $items = get_post_meta( $post_id, $meta_key, true );
    if ( ! is_array( $items ) ) return null;
    
    // Normalize indexes to be 0-based sequential for consistent access
    $items = array_values( $items );
    
    // Convert 1-based index to 0-based
    $idx = intval( $index ) - 1;
    if ( isset( $items[ $idx ] ) ) {
        return $items[ $idx ];
    }
    return null;
}

/**
 * Calendar icon markup used by the fixed dates list output.
 */
function ctd_calendar_icon() {
    return '<svg class="ctd-icon-calendar" aria-hidden="true" role="img" focusable="false" height="1em" width="1em" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M148 288h-40c-6.6 0-12-5.4-12-12v-40c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12zm108-12v-40c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v40c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12zm96 0v-40c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v40c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12zm-96 96v-40c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v40c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12zm-96 0v-40c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v40c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12zm192 0v-40c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v40c0 6.6 5.4 12 12 12h40c6.6 0 12-5.4 12-12zm96-260v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V112c0-26.5 21.5-48 48-48h48V12c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v52h128V12c0-6.6 5.4-12 12-12h40c6.6 0 12 5.4 12 12v52h48c26.5 0 48 21.5 48 48zm-48 346V160H48v298c0 3.3 2.7 6 6 6h340c3.3 0 6-2.7 6-6z"></path></svg>';
}

function ctd_format_inr( $amount ) {
    $amount = (string) $amount;
    $parts = explode( '.', $amount );
    $int = $parts[0];
    $dec = isset( $parts[1] ) ? '.' . $parts[1] : '';
    $last3 = substr( $int, -3 );
    $rest = substr( $int, 0, -3 );
    if ( $rest !== '' ) {
        $rest = preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest );
    }
    return ( $rest ? $rest . ',' : '' ) . $last3 . $dec;
}

/**
 * FAQ Item Callback
 */
function ctd_faq_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'question';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    
    $item = ctd_get_repeater_item( 'ctd_faqs', $index, $options, $instance );
    if ( ! $item ) return '';
    
    $value = isset( $item[ $field ] ) ? $item[ $field ] : '';
    if ( $field === 'answer' ) {
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses_post( $value ) );
    }
    return $value;
}

/**
 * Itinerary Item Callback
 */
function ctd_itinerary_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'title';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    
    $item = ctd_get_repeater_item( 'ctd_itineraries', $index, $options, $instance );
    
    if ( $field === 'day_label' ) {
        return sprintf( __( 'Day %d', 'cotlas-travel' ), intval( $index ) );
    }

    if ( ! $item ) return '';
    
    if ( $field === 'image' ) {
        $img_id = isset( $item['image_id'] ) ? $item['image_id'] : '';
        return $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
    }
    
    $value = isset( $item[ $field ] ) ? $item[ $field ] : '';
    if ( $field === 'content' ) {
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses_post( $value ) );
    }
    return $value;
}

/**
 * Fact Item Callback
 */
function ctd_fact_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'value';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    
    $item = ctd_get_repeater_item( 'ctd_trip_facts', $index, $options, $instance );
    if ( ! $item ) return '';
    
    if ( $field === 'image' || $field === 'image_id' ) {
        $label = isset( $item['label'] ) ? $item['label'] : '';
        if ( ! $label ) return '';
        
        // 1. Try exact match
        $term = get_term_by( 'name', $label, 'trip_fact' );
        
        // 2. Try decoding HTML entities
        if ( ! $term ) {
            $decoded_label = html_entity_decode( $label );
            $term = get_term_by( 'name', $decoded_label, 'trip_fact' );
        }

        // 3. Try matching by slug
        if ( ! $term ) {
            $slug = sanitize_title( $label );
            $term = get_term_by( 'slug', $slug, 'trip_fact' );
        }

        if ( ! $term ) return '';
        
        $img_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
        
        if ( $field === 'image_id' ) {
            return $img_id;
        }

        // Return URL (default behavior for image source in most blocks)
        return $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
    }
    
    return isset( $item[ $field ] ) ? $item[ $field ] : '';
}

/**
 * Service Item Callback
 */
function ctd_service_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'name';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    
    $item = ctd_get_repeater_item( 'ctd_extra_services', $index, $options, $instance );
    if ( ! $item ) return '';
    
    $value = isset( $item[ $field ] ) ? $item[ $field ] : '';
    
    if ( $field === 'price' ) {
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $value );
        }
        return is_numeric( $value ) ? '₹' . $value : $value;
    }
    if ( $field === 'description' ) {
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses_post( $value ) );
    }
    if ( $field === 'image' ) {
        $img_id = isset( $item['image_id'] ) ? intval( $item['image_id'] ) : 0;
        return $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
    }
    if ( $field === 'image_id' ) {
        return isset( $item['image_id'] ) ? intval( $item['image_id'] ) : '';
    }
    return $value;
}

/**
 * Package Item Callback
 */
function ctd_package_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'title';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    $cat_id = isset( $options['cat_id'] ) ? $options['cat_id'] : '';
    
    $item = ctd_get_repeater_item( 'ctd_pricing_packages', $index, $options, $instance );
    if ( ! $item ) return '';

    if ( $field === 'dates' || $field === 'dates_list' ) {
        if ( empty( $item['enable_dates'] ) ) {
            return '';
        }

        $dates = isset( $item['dates'] ) && is_array( $item['dates'] ) ? $item['dates'] : array();
        if ( empty( $dates ) ) {
            return '';
        }

        $date_format = isset( $options['date_format'] ) && '' !== $options['date_format'] ? $options['date_format'] : 'd M Y';
        $separator   = isset( $options['separator'] ) ? $options['separator'] : ', ';
        $date_index  = isset( $options['dateindex'] ) ? intval( $options['dateindex'] ) : 0;
        $formatted   = array();

        foreach ( $dates as $date ) {
            $date = trim( (string) $date );
            if ( '' === $date ) {
                continue;
            }
            $timestamp   = strtotime( $date );
            $formatted[] = $timestamp ? date_i18n( $date_format, $timestamp ) : $date;
        }

        if ( empty( $formatted ) ) {
            return '';
        }

        // Single date by position (1-based). Used to print one date per dynamic tag.
        if ( $date_index > 0 ) {
            $position = $date_index - 1;
            if ( ! isset( $formatted[ $position ] ) ) {
                return '';
            }
            if ( $field !== 'dates_list' ) {
                return $formatted[ $position ];
            }
            $formatted = array( $formatted[ $position ] );
        }

        if ( $field === 'dates_list' ) {
            $items = '';
            foreach ( $formatted as $formatted_date ) {
                $items .= '<li class="ctd-date-item">'
                    . '<span class="ctd-date-icon">' . ctd_calendar_icon() . '</span>'
                    . '<span class="ctd-date-text">' . esc_html( $formatted_date ) . '</span>'
                    . '</li>';
            }
            return '<ul class="ctd-dates-list">' . $items . '</ul>';
        }

        return implode( $separator, $formatted );
    }

    if ( $field === 'price' ) {
        if ( $cat_id ) {
            if ( isset( $item['prices'][ $cat_id ] ) ) {
                $pdata = $item['prices'][ $cat_id ];
                $enabled = isset( $pdata['enabled'] ) ? intval( $pdata['enabled'] ) : 0;
                $val = isset( $pdata['regular_price'] ) ? $pdata['regular_price'] : '';
                $ptype = isset( $pdata['pricing_type'] ) ? $pdata['pricing_type'] : 'person';
                if ( ! $enabled || $val === '' || floatval( $val ) <= 0 ) {
                    return '';
                }
                if ( is_numeric( $val ) ) {
                    $fmt = ctd_format_inr( $val );
                    $label = $ptype === 'group' ? 'Per Group' : 'Per Person';
                    return '<p class="gb-text price">₹' . $fmt . ' <span class="pricing-type">' . $label . '</span></p>';
                }
                return '<p class="gb-text price">₹' . $val . ' <span class="pricing-type">' . ( $ptype === 'group' ? 'Per Group' : 'Per Person' ) . '</span></p>';
            }
            return '';
        }
        $min = null;
        $min_type = 'person';
        if ( ! empty( $item['prices'] ) && is_array( $item['prices'] ) ) {
            foreach ( $item['prices'] as $p ) {
                $enabled = isset( $p['enabled'] ) ? intval( $p['enabled'] ) : 1;
                if ( ! $enabled ) continue;
                if ( isset( $p['regular_price'] ) && $p['regular_price'] !== '' && floatval( $p['regular_price'] ) > 0 ) {
                    $val = $p['regular_price'];
                    if ( is_numeric( $val ) ) {
                        $num = floatval( $val );
                        if ( is_null( $min ) || $num < $min ) {
                            $min = $num;
                            $min_type = isset( $p['pricing_type'] ) ? $p['pricing_type'] : 'person';
                        }
                    } else {
                        if ( is_null( $min ) ) {
                            $min = $val;
                            $min_type = isset( $p['pricing_type'] ) ? $p['pricing_type'] : 'person';
                        }
                    }
                }
            }
        }
        if ( is_null( $min ) ) {
            return '';
        }
        if ( is_numeric( $min ) ) {
            $fmt = ctd_format_inr( $min );
            $label = $min_type === 'group' ? 'Per Group' : 'Per Person';
            return '<p class="gb-text price">₹' . $fmt . ' <span class="pricing-type">' . $label . '</span></p>';
        }
        $label = $min_type === 'group' ? 'Per Group' : 'Per Person';
        return '<p class="gb-text price">₹' . $min . ' <span class="pricing-type">' . $label . '</span></p>';
    }
    
    $value = isset( $item[ $field ] ) ? $item[ $field ] : '';
    if ( $field === 'general_desc' ) {
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses_post( $value ) );
    }
    return $value;
}

/**
 * Render Callback for Simple Meta
 */
function ctd_trip_meta_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : '';
    if ( empty( $field ) ) return '';

    // Get current post ID
    $post_id = get_the_ID();
    if ( ! $post_id ) return '';

    $value = get_post_meta( $post_id, $field, true );
    
    // Check booking toggle
    if ( $field === 'ctd_custom_booking_link' ) {
        $enable = get_post_meta( $post_id, 'ctd_enable_booking', true );
        if ( ! $enable ) return '';
    }
    
    // Respect More Info toggle
    if ( $field === 'ctd_more_info_title' || $field === 'ctd_more_info_content' ) {
        $enable_more = get_post_meta( $post_id, 'ctd_enable_more_info', true );
        if ( ! $enable_more ) return '';
    }
    // Respect Downloads toggle for title output
    if ( $field === 'ctd_downloads_title' ) {
        $enable_down = get_post_meta( $post_id, 'ctd_enable_downloads', true );
        if ( ! $enable_down ) return '';
    }
    
    // Itinerary PDF - Return URL instead of ID
    if ( $field === 'ctd_itinerary_pdf' ) {
        return $value ? wp_get_attachment_url( $value ) : '';
    }
    
    // Check for unlimited travellers
    if ( ( $field === 'ctd_min_travellers' || $field === 'ctd_total_seats' ) && ( $value == -1 || $value === '-1' ) ) {
        return __( 'Unlimited', 'cotlas-travel' );
    }
    
    // Process content fields to allow shortcodes/HTML
    $content_fields = [
        'ctd_overview_content', 'ctd_itinerary_description', 'ctd_more_info_content', 'ctd_extra_services_desc'
    ];

    if ( in_array( $field, $content_fields ) ) {
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses_post( $value ) );
    }
    
    if ( $field === 'ctd_map_iframe' ) {
        // Fallback to Image if Iframe is empty
        if ( empty( $value ) ) {
            $img_id = get_post_meta( $post_id, 'ctd_map_image', true );
            if ( $img_id ) {
                return wp_get_attachment_image( $img_id, 'full', false, array( 'class' => 'ctd-map-image' ) );
            }
        }

        $allowed_tags = wp_kses_allowed_html( 'post' );
        $allowed_tags['iframe'] = array(
            'src'             => true,
            'height'          => true,
            'width'           => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'style'           => true,
            'loading'         => true,
            'referrerpolicy'  => true,
            'title'           => true,
            'class'           => true,
            'id'              => true,
            'name'            => true,
            'allow'           => true,
        );
        $value = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $value);
        return do_shortcode( wp_kses( $value, $allowed_tags ) );
    }

    if ( class_exists( 'GenerateBlocks_Dynamic_Tag_Callbacks' ) ) {
        return GenerateBlocks_Dynamic_Tag_Callbacks::output( $value, $options, $instance );
    }
    return $value;
}

/**
 * Render Callback for Components (Shortcodes)
 */
function ctd_trip_component_dynamic_tag( $options, $block, $instance ) {
    $component = isset( $options['component'] ) ? $options['component'] : '';
    $class = isset( $options['class'] ) ? $options['class'] : '';
    $fields = isset( $options['fields'] ) ? $options['fields'] : '';
    
    if ( empty( $component ) ) return '';

    $shortcode = '';
    $class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    $fields_attr = $fields ? ' fields="' . esc_attr( $fields ) . '"' : '';

    switch ( $component ) {
        case 'highlights':
            $shortcode = '[ctd_highlights' . $class_attr . ']';
            break;
        case 'itinerary':
            $shortcode = '[ctd_itinerary' . $class_attr . ']';
            break;
        case 'facts':
            $shortcode = '[ctd_trip_facts' . $class_attr . ']';
            break;
        case 'pricing':
            $shortcode = '[ctd_pricing_table' . $class_attr . ']';
            break;
        case 'gallery':
            $shortcode = '[ctd_gallery' . $class_attr . ']';
            break;
        case 'video_gallery':
            $shortcode = '[ctd_video_gallery' . $class_attr . ']';
            break;
        case 'faqs':
            $shortcode = '[ctd_faqs' . $class_attr . ']';
            break;
        case 'downloads':
            $shortcode = '[ctd_downloads' . $class_attr . ']';
            break;
        case 'extra_services':
            $shortcode = '[ctd_extra_services' . $class_attr . ']';
            break;
        case 'cost_includes':
            $shortcode = '[ctd_cost_includes' . $class_attr . ']';
            break;
        case 'cost_excludes':
            $shortcode = '[ctd_cost_excludes' . $class_attr . ']';
            break;
        case 'search_bar':
            $shortcode = '[ctd_search_bar' . $class_attr . $fields_attr . ']';
            break;
        case 'map_image':
             // Special case for image URL - Class not applicable to URL string
             $img_id = get_post_meta( get_the_ID(), 'ctd_map_image', true );
             return $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
    }

    return do_shortcode( $shortcode );
}

/**
 * Taxonomy Term Data Callback
 */
function ctd_taxonomy_term_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'title';
    
    $term_id = 0;

    // Try to get Term ID from GenerateBlocks context (works for Query Loops)
    if ( class_exists( 'GenerateBlocks_Dynamic_Tags' ) && method_exists( 'GenerateBlocks_Dynamic_Tags', 'get_id' ) ) {
        $term_id = GenerateBlocks_Dynamic_Tags::get_id( $options, 'term', $instance );
    }

    // Fallback to Queried Object (Archive Pages)
    if ( ! $term_id && ( is_tax() || is_category() || is_tag() ) ) {
        $obj = get_queried_object();
        if ( $obj && isset( $obj->term_id ) ) {
            $term_id = $obj->term_id;
        }
    }

    if ( ! $term_id ) {
        return '';
    }

    $term = get_term( $term_id );
    if ( ! $term || is_wp_error( $term ) ) {
        return '';
    }

    switch ( $field ) {
        case 'title':
            return $term->name;
        case 'description':
            return wp_kses_post( wpautop( $term->description ) );
        case 'short_description':
            return get_term_meta( $term->term_id, 'ctd_short_description', true );
        case 'image':
            $img_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
            return $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
        case 'url':
            return get_term_link( $term );
        case 'count':
            return $term->count;
    }
    return '';
}
function ctd_taxonomy_slider_dynamic_tag( $options, $block, $instance ) {
    $taxonomy = isset( $options['taxonomy'] ) ? $options['taxonomy'] : 'destination';
    $count = isset( $options['count'] ) ? $options['count'] : '-1';
    $class = isset( $options['class'] ) ? $options['class'] : '';
    $autoplay = isset( $options['autoplay'] ) ? $options['autoplay'] : 'no';
    $speed = isset( $options['speed'] ) ? $options['speed'] : '3000';
    $loop = isset( $options['loop'] ) ? $options['loop'] : 'no';
    $hide_empty = isset( $options['hide_empty'] ) ? $options['hide_empty'] : 'yes';
    
    $atts = ' taxonomy="' . esc_attr( $taxonomy ) . '"';
    $atts .= ' count="' . esc_attr( $count ) . '"';
    $atts .= ' autoplay="' . esc_attr( $autoplay ) . '"';
    $atts .= ' speed="' . esc_attr( $speed ) . '"';
    $atts .= ' loop="' . esc_attr( $loop ) . '"';
    $atts .= ' hide_empty="' . esc_attr( $hide_empty ) . '"';
    if ( $class ) {
        $atts .= ' class="' . esc_attr( $class ) . '"';
    }
    
    return do_shortcode( '[ctd_taxonomy_slider' . $atts . ']' );
}
function ctd_flight_info_dynamic_tag( $options, $block, $instance ) {
    $post_id = get_the_ID();
    if ( ! $post_id ) return '';
    $direction = isset( $options['direction'] ) ? $options['direction'] : 'outbound';
    $field = isset( $options['field'] ) ? $options['field'] : 'airline';
    $flights = get_post_meta( $post_id, 'ctd_flights', true );
    $data = isset( $flights[ $direction ] ) ? $flights[ $direction ] : array();
    if ( in_array( $field, array( 'airline', 'from', 'to' ), true ) ) {
        return isset( $data[ $field ] ) ? $data[ $field ] : '';
    }
    if ( $field === 'departure_time' ) {
        $h = isset( $data['dep_hour'] ) ? $data['dep_hour'] : '';
        $m = isset( $data['dep_min'] ) ? $data['dep_min'] : '';
        $h = str_pad( trim( (string) $h ), 2, '0', STR_PAD_LEFT );
        $m = str_pad( trim( (string) $m ), 2, '0', STR_PAD_LEFT );
        return ($h !== '' || $m !== '') ? $h . ':' . $m : '';
    }
    if ( $field === 'arrival_time' ) {
        $h = isset( $data['arr_hour'] ) ? $data['arr_hour'] : '';
        $m = isset( $data['arr_min'] ) ? $data['arr_min'] : '';
        $h = str_pad( trim( (string) $h ), 2, '0', STR_PAD_LEFT );
        $m = str_pad( trim( (string) $m ), 2, '0', STR_PAD_LEFT );
        return ($h !== '' || $m !== '') ? $h . ':' . $m : '';
    }
    return '';
}
function ctd_meal_item_dynamic_tag( $options, $block, $instance ) {
    $field = isset( $options['field'] ) ? $options['field'] : 'value';
    $index = isset( $options['index'] ) ? $options['index'] : '1';
    $item = ctd_get_repeater_item( 'ctd_meals', $index, $options, $instance );
    if ( ! $item ) return '';
    
    // Meals repeater is stored as a simple array of strings
    if ( is_array( $item ) ) {
        if ( isset( $item['value'] ) ) {
            return $item['value'];
        }
        $first = reset( $item );
        return $first;
    }
    return $item;
}

function ctd_hero_image_dynamic_tag( $options, $block, $instance ) {
    $index = isset( $options['index'] ) ? intval( $options['index'] ) : 1;
    $type  = isset( $options['return_type'] ) ? $options['return_type'] : 'url';
    
    $image_id = get_option( 'ctd_hero_image_' . $index );
    if ( ! $image_id ) return '';

    if ( $type === 'id' ) {
        return $image_id;
    }
    if ( $type === 'tag' ) {
        return wp_get_attachment_image( $image_id, 'full' );
    }
    
    // Default URL
    return wp_get_attachment_image_url( $image_id, 'full' );
}

function ctd_destination_card_dynamic_tag( $options, $block, $instance ) {
    $atts = '';
    
    // Handle term ID: if empty, try to get from context
    $term_id = isset( $options['term_id'] ) ? $options['term_id'] : '';
    $newly_launched = isset( $options['newly_launched'] ) ? $options['newly_launched'] : 'no';
    
    // Only try to find context if NO term ID AND NOT newly launched
    if ( ! $term_id && $newly_launched !== 'yes' ) {
        // Try to get Term ID from GenerateBlocks context
        if ( class_exists( 'GenerateBlocks_Dynamic_Tags' ) && method_exists( 'GenerateBlocks_Dynamic_Tags', 'get_id' ) ) {
            $term_id = GenerateBlocks_Dynamic_Tags::get_id( $options, 'term', $instance );
        }
        // Fallback to Queried Object
        if ( ! $term_id && ( is_tax() || is_category() || is_tag() ) ) {
            $obj = get_queried_object();
            if ( $obj && isset( $obj->term_id ) ) {
                $term_id = $obj->term_id;
            }
        }
    }
    
    if ( $term_id ) {
        $atts .= ' term_id="' . esc_attr( $term_id ) . '"';
    }

    foreach( ['taxonomy', 'show_image', 'show_title', 'show_count', 'show_desc', 'show_short_desc', 'class', 'newly_launched', 'offset'] as $key ) {
        if ( isset( $options[ $key ] ) && $options[ $key ] !== '' ) {
            $atts .= ' ' . $key . '="' . esc_attr( $options[ $key ] ) . '"';
        }
    }
    
    return do_shortcode( '[ctd_destination_card' . $atts . ']' );
}
