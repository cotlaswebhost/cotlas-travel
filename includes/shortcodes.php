<?php
/**
 * Shortcodes for Cotlas Travel Desk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTD_Shortcodes {

    public function __construct() {
        add_shortcode( 'ctd_trip_data', array( $this, 'trip_data_shortcode' ) );
        add_shortcode( 'ctd_highlights', array( $this, 'highlights_shortcode' ) );
        add_shortcode( 'ctd_itinerary', array( $this, 'itinerary_shortcode' ) );
        add_shortcode( 'ctd_trip_facts', array( $this, 'trip_facts_shortcode' ) );
        add_shortcode( 'ctd_gallery', array( $this, 'gallery_shortcode' ) );
        add_shortcode( 'ctd_video_gallery', array( $this, 'video_gallery_shortcode' ) );
        add_shortcode( 'ctd_faqs', array( $this, 'faqs_shortcode' ) );
        add_shortcode( 'ctd_downloads', array( $this, 'downloads_shortcode' ) );
        add_shortcode( 'ctd_extra_services', array( $this, 'extra_services_shortcode' ) );
        add_shortcode( 'ctd_pricing_table', array( $this, 'pricing_table_shortcode' ) );
        add_shortcode( 'ctd_overview', array( $this, 'overview_shortcode' ) );
        add_shortcode( 'ctd_more_info', array( $this, 'more_info_shortcode' ) );
        add_shortcode( 'ctd_booking_url', array( $this, 'booking_url_shortcode' ) );
        add_shortcode( 'ctd_itinerary_pdf', array( $this, 'itinerary_pdf_shortcode' ) );
        add_shortcode( 'ctd_download_itinerary_btn', array( $this, 'download_itinerary_btn_shortcode' ) );
        add_shortcode( 'ctd_term_data', array( $this, 'term_data_shortcode' ) );
        add_shortcode( 'ctd_outbound_flight', array( $this, 'outbound_flight_shortcode' ) );
        add_shortcode( 'ctd_inbound_flight', array( $this, 'inbound_flight_shortcode' ) );
        add_shortcode( 'ctd_meals', array( $this, 'meals_shortcode' ) );
        add_shortcode( 'ctd_cost_includes', array( $this, 'cost_includes_shortcode' ) );
        add_shortcode( 'ctd_cost_excludes', array( $this, 'cost_excludes_shortcode' ) );
        add_shortcode( 'ctd_search_bar', array( $this, 'search_bar_shortcode' ) );
        add_shortcode( 'ctd_taxonomy_slider', array( $this, 'taxonomy_slider_shortcode' ) );
        add_shortcode( 'ctd_trip_slider', array( $this, 'trip_slider_shortcode' ) );
        add_shortcode( 'ctd_trip_grid', array( $this, 'trip_grid_shortcode' ) );
        add_shortcode( 'ctd_hero_image', array( $this, 'hero_image_shortcode' ) );
        add_shortcode( 'ctd_destination_card', array( $this, 'destination_card_shortcode' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        add_action( 'wp_ajax_ctd_load_more_trips', array( $this, 'ajax_load_more_trips' ) );
        add_action( 'wp_ajax_nopriv_ctd_load_more_trips', array( $this, 'ajax_load_more_trips' ) );
    }

    private function get_trip_query( $atts ) {
        $args = array(
            'post_type' => 'trip',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if ( isset( $atts['current_query'] ) && $atts['current_query'] === 'yes' ) {
            global $wp_query;
            return $wp_query;
        }

        $tax_query = array();

        if ( ! empty( $atts['destination'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'destination',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['destination'] ) ),
            );
        }

        if ( ! empty( $atts['activity'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'activities',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['activity'] ) ),
            );
        }

        if ( ! empty( $atts['trip_type'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'trip_types',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['trip_type'] ) ),
            );
        }

        if ( count( $tax_query ) > 0 ) {
            if ( count( $tax_query ) > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        return new WP_Query( $args );
    }

    private function render_trip_card( $post_id ) {
        $title = get_the_title( $post_id );
        $link = get_permalink( $post_id );
        $image = get_the_post_thumbnail( $post_id, 'medium_large' );
        if ( ! $image ) {
            $image = '<div class="ctd-no-image"></div>';
        }
        
        // Meta
        $min_price = get_post_meta( $post_id, 'ctd_min_price', true );
        $duration = get_post_meta( $post_id, 'ctd_duration_days', true );
        
        // If min_price is missing, try to get it from the first available package price
        if ( ! $min_price ) {
            $packages = get_post_meta( $post_id, 'ctd_pricing_packages', true );
            if ( ! empty( $packages ) && is_array( $packages ) ) {
                foreach ( $packages as $pkg ) {
                    if ( ! empty( $pkg['prices'] ) ) {
                        foreach ( $pkg['prices'] as $price_data ) {
                            if ( ! empty( $price_data['regular_price'] ) ) {
                                $min_price = $price_data['regular_price'];
                                break 2; // Found a price, break both loops
                            }
                        }
                    }
                }
            }
        }
        
        // Location (first destination term)
        $destinations = get_the_terms( $post_id, 'destination' );
        $location_text = '';
        if ( ! empty( $destinations ) && ! is_wp_error( $destinations ) ) {
            $names = wp_list_pluck( $destinations, 'name' );
            $location_text = implode( ', ', array_slice( $names, 0, 2 ) ); // Show first 2
        }

        $html = '<div class="ctd-trip-card">';
        
        // Image Wrapper
        $html .= '<div class="ctd-trip-image">';
        $html .= '<a href="' . esc_url( $link ) . '">' . $image . '</a>';
        
        // Badges (Example: Sale if manual logic exists, currently static placeholder style based on request)
        // Check if sale? Assuming if regular price > min price? But we only have min price key easily accessible.
        // I'll leave the badge placeholder commented out or logic based on custom field if added later.
        // $html .= '<span class="ctd-badge sale">Sale</span>';
        
        // Wishlist Button
        $html .= '<button class="ctd-wishlist-btn"><svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10 19L8.55 17.7C6.86667 16.1834 5.475 14.875 4.375 13.775C3.275 12.675 2.4 11.6874 1.75 10.812C1.1 9.93736 0.646 9.13336 0.388 8.40002C0.129333 7.66669 0 6.91669 0 6.15002C0 4.58336 0.525 3.27502 1.575 2.22502C2.625 1.17502 3.93333 0.650024 5.5 0.650024C6.36667 0.650024 7.19167 0.833358 7.975 1.20002C8.75833 1.56669 9.43333 2.08336 10 2.75002C10.5667 2.08336 11.2417 1.56669 12.025 1.20002C12.8083 0.833358 13.6333 0.650024 14.5 0.650024C16.0667 0.650024 17.375 1.17502 18.425 2.22502C19.475 3.27502 20 4.58336 20 6.15002C20 6.91669 19.871 7.66669 19.613 8.40002C19.3543 9.13336 18.9 9.93736 18.25 10.812C17.6 11.6874 16.725 12.675 15.625 13.775C14.525 14.875 13.1333 16.1834 11.45 17.7L10 19Z" fill="currentColor"></path> </svg></button>';
        $html .= '</div>'; // .ctd-trip-image

        // Content
        $html .= '<div class="ctd-trip-content">';
        
        if ( $location_text ) {
            $html .= '<div class="ctd-trip-location"><svg data-prefix="fas" data-icon="map-marker" width="12" height="15" viewBox="0 0 12 15" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M6 0C4.4087 0 2.88258 0.632141 1.75736 1.75736C0.632141 2.88258 0 4.4087 0 6C0 10.05 5.2875 14.625 5.5125 14.82C5.64835 14.9362 5.82124 15 6 15C6.17877 15 6.35165 14.9362 6.4875 14.82C6.75 14.625 12 10.05 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0ZM6 13.2375C4.4025 11.7375 1.5 8.505 1.5 6C1.5 4.80653 1.97411 3.66193 2.81802 2.81802C3.66193 1.97411 4.80653 1.5 6 1.5C7.19347 1.5 8.33807 1.97411 9.18198 2.81802C10.0259 3.66193 10.5 4.80653 10.5 6C10.5 8.505 7.5975 11.745 6 13.2375ZM6 3C5.40666 3 4.82664 3.17595 4.33329 3.50559C3.83994 3.83524 3.45542 4.30377 3.22836 4.85195C3.0013 5.40013 2.94189 6.00333 3.05764 6.58527C3.1734 7.16721 3.45912 7.70176 3.87868 8.12132C4.29824 8.54088 4.83279 8.8266 5.41473 8.94236C5.99667 9.05811 6.59987 8.9987 7.14805 8.77164C7.69623 8.54458 8.16477 8.16006 8.49441 7.66671C8.82405 7.17336 9 6.59334 9 6C9 5.20435 8.68393 4.44129 8.12132 3.87868C7.55871 3.31607 6.79565 3 6 3ZM6 7.5C5.70333 7.5 5.41332 7.41203 5.16665 7.2472C4.91997 7.08238 4.72771 6.84811 4.61418 6.57403C4.50065 6.29994 4.47094 5.99834 4.52882 5.70736C4.5867 5.41639 4.72956 5.14912 4.93934 4.93934C5.14912 4.72956 5.41639 4.5867 5.70737 4.52882C5.99834 4.47094 6.29994 4.50065 6.57403 4.61418C6.84811 4.72771 7.08238 4.91997 7.2472 5.16665C7.41203 5.41332 7.5 5.70333 7.5 6C7.5 6.39782 7.34197 6.77936 7.06066 7.06066C6.77936 7.34196 6.39783 7.5 6 7.5Z" fill="currentColor"></path> </svg> ' . esc_html( $location_text ) . '</div>';
        }
        
        $html .= '<h3 class="ctd-trip-title"><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></h3>';
        
        $html .= '<div class="ctd-trip-footer">';
        
        // Duration (Left)
        if ( $duration ) {
            $html .= '<div class="ctd-meta-item duration"><svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"> <g opacity="1"> <path d="M21 10.3018H3M16 2.30176V6.30176M8 2.30176V6.30176M7.8 22.3018H16.2C17.8802 22.3018 18.7202 22.3018 19.362 21.9748C19.9265 21.6872 20.3854 21.2282 20.673 20.6637C21 20.022 21 19.1819 21 17.5018V9.10176C21 7.4216 21 6.58152 20.673 5.93979C20.3854 5.3753 19.9265 4.91636 19.362 4.62874C18.7202 4.30176 17.8802 4.30176 16.2 4.30176H7.8C6.11984 4.30176 5.27976 4.30176 4.63803 4.62874C4.07354 4.91636 3.6146 5.3753 3.32698 5.93979C3 6.58152 3 7.4216 3 9.10176V17.5018C3 19.1819 3 20.022 3.32698 20.6637C3.6146 21.2282 4.07354 21.6872 4.63803 21.9748C5.27976 22.3018 6.11984 22.3018 7.8 22.3018Z" stroke="currentColor" stroke-width="1.39" stroke-linecap="round" stroke-linejoin="round"></path> </g> </svg> ' . esc_html( $duration ) . ' ' . __( 'Days', 'cotlas-travel' ) . '</div>';
        }
        
        // Price (Right)
        $html .= '<div class="ctd-trip-price">';
        if ( $min_price ) {
            $html .= '<span class="from-text">' . __( 'from', 'cotlas-travel' ) . '</span> ';
            $html .= '<span class="current-price">₹' . $this->format_inr( $min_price ) . '</span>';
        }
        $html .= '</div>';
        
        $html .= '</div>'; // .ctd-trip-footer
        
        $html .= '</div>'; // .ctd-trip-content
        $html .= '</div>'; // .ctd-trip-card

        return $html;
    }

    public function trip_slider_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_script( 'ctd-front-script' );
        
        $atts = shortcode_atts( array(
            'destination' => '',
            'activity'    => '',
            'trip_type'   => '',
            'count'       => '8',
            'class'       => '',
            'autoplay'    => 'no',
            'speed'       => '3000',
            'loop'        => 'no',
            'navigation'  => 'yes',
            'pagination'  => 'yes',
        ), $atts );

        $query = $this->get_trip_query( $atts );

        if ( ! $query->have_posts() ) {
            wp_reset_postdata();
            return '';
        }

        // Wrapper with data attributes for JS
        $wrapper_class = 'ctd-taxonomy-slider-wrapper ctd-trip-slider ' . esc_attr( $atts['class'] );
        $data_attrs = ' data-autoplay="' . esc_attr( $atts['autoplay'] ) . '"';
        $data_attrs .= ' data-speed="' . esc_attr( $atts['speed'] ) . '"';
        $data_attrs .= ' data-loop="' . esc_attr( $atts['loop'] ) . '"';

        $html = '<div class="' . $wrapper_class . '"' . $data_attrs . '>';
        
        // Navigation Buttons
        if ( $atts['navigation'] !== 'no' ) {
            $html .= '<div class="ctd-slider-nav-container">';
            $html .= '<button class="ctd-slider-nav ctd-prev" aria-label="Previous"><i class="fa-solid fa-arrow-left"></i></button>';
            $html .= '<button class="ctd-slider-nav ctd-next" aria-label="Next"><i class="fa-solid fa-arrow-right"></i></button>';
            $html .= '</div>';
        }

        $html .= '<div class="ctd-taxonomy-slider">'; // Reusing class for JS selector
        
        while ( $query->have_posts() ) {
            $query->the_post();
            $html .= '<div class="ctd-taxonomy-slide ctd-trip-slide">'; // Reusing class for JS logic
            $html .= $this->render_trip_card( get_the_ID() );
            $html .= '</div>';
        }
        wp_reset_postdata();

        $html .= '</div>'; // .ctd-taxonomy-slider

        if ( $atts['pagination'] !== 'no' ) {
            $html .= '<div class="ctd-slider-dots"></div>';
        }

        $html .= '</div>'; // .ctd-taxonomy-slider-wrapper

        return $html;
    }

    public function trip_grid_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_script( 'ctd-front-script' );
        
        $atts = shortcode_atts( array(
            'destination'   => '',
            'activity'      => '',
            'trip_type'     => '',
            'count'         => '8',
            'class'         => '',
            'ajax_load'     => 'yes',
            'current_query' => 'no',
            'columns_desktop' => '4',
            'columns_tablet'  => '3',
            'columns_mobile'  => '1',
        ), $atts );

        $query = $this->get_trip_query( $atts );

        if ( ! $query->have_posts() ) {
            wp_reset_postdata();
            
            // Only show message if current_query is yes (Archive pages) or explicit enabled
            if ( $atts['current_query'] === 'yes' ) {
                return '<!-- wp:generateblocks/element {"uniqueId":"6197095d","tagName":"div","styles":{},"globalClasses":["cs-trip-container","no-results-container"]} --> 
                <div class="cs-trip-container no-results-container"><!-- wp:generateblocks/text {"uniqueId":"2572f2e0","tagName":"h2","globalClasses":["column-heading"]} --> 
                <h2 class="gb-text column-heading">No Trips Found</h2> 
                <!-- /wp:generateblocks/text --> 
                
                <!-- wp:paragraph --> 
                <p>✈️&nbsp;<strong>Don\'t give up on adventure!</strong><br>Your dream getaway might just need a different approach.</p> 
                <!-- /wp:paragraph --> 
                
                <!-- wp:paragraph --> 
                <p><strong>What to try next:</strong><br>• Clear some filters<br>• Browse by destination instead<br>• <a href="/contact-us">Contact us</a> for custom trip planning</p> 
                <!-- /wp:paragraph --></div> 
                <!-- /wp:generateblocks/element -->';
            }
            
            return '';
        }

        // Capture current query vars for AJAX if needed
        $current_query_vars = array();
        if ( $atts['current_query'] === 'yes' ) {
            if ( isset( $query->meta_query ) ) {
                $current_query_vars['meta_query'] = $query->meta_query->queries;
            }
            if ( isset( $query->tax_query ) ) {
                $current_query_vars['tax_query'] = $query->tax_query->queries;
            }
            // Capture 's' parameter for search
            if ( get_query_var( 's' ) ) {
                $current_query_vars['s'] = get_query_var( 's' );
            }
            
            // Sync count with the actual query's posts_per_page to avoid pagination issues
            if ( isset( $query->query_vars['posts_per_page'] ) ) {
                $atts['count'] = $query->query_vars['posts_per_page'];
            }
        }

        // Add custom style for grid columns
        $unique_id = 'ctd-grid-' . uniqid();
        $cols_d = intval($atts['columns_desktop']);
        $cols_t = intval($atts['columns_tablet']);
        $cols_m = intval($atts['columns_mobile']);
        
        $css = "<style>
            #{$unique_id} .ctd-trip-grid {
                grid-template-columns: repeat({$cols_d}, minmax(0, 1fr));
            }
            @media (max-width: 1024px) {
                #{$unique_id} .ctd-trip-grid {
                    grid-template-columns: repeat({$cols_t}, minmax(0, 1fr));
                }
            }
            @media (max-width: 768px) {
                #{$unique_id} .ctd-trip-grid {
                    grid-template-columns: repeat({$cols_m}, minmax(0, 1fr));
                }
            }
        </style>";

        $html = $css;
        $html .= '<div id="' . esc_attr($unique_id) . '" class="ctd-trip-grid-wrapper ' . esc_attr( $atts['class'] ) . '">';
        $html .= '<div class="ctd-trip-grid">';
        
        while ( $query->have_posts() ) {
            $query->the_post();
            $html .= '<div class="ctd-trip-grid-item">';
            $html .= $this->render_trip_card( get_the_ID() );
            $html .= '</div>';
        }
        
        $html .= '</div>'; // .ctd-trip-grid

        if ( $atts['ajax_load'] === 'yes' && $query->max_num_pages > 1 ) {
            $html .= '<div class="ctd-load-more-container">';
            $data_query_vars = ! empty( $current_query_vars ) ? " data-query-vars='" . esc_attr( json_encode( $current_query_vars ) ) . "'" : "";
            $html .= '<button class="ctd-load-more-btn" data-page="1" data-max="' . $query->max_num_pages . '" data-atts="' . esc_attr( json_encode( $atts ) ) . '"' . $data_query_vars . '>' . __( 'Load More', 'cotlas-travel' ) . '</button>';
            $html .= '</div>';
        }

        $html .= '</div>'; // .ctd-trip-grid-wrapper
        wp_reset_postdata();

        return $html;
    }

    public function ajax_load_more_trips() {
        // Verify Nonce if possible (skipped for simplicity as per current pattern, but recommended)
        
        $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $atts = isset( $_POST['atts'] ) ? json_decode( stripslashes( $_POST['atts'] ), true ) : array();
        $query_vars = isset( $_POST['query_vars'] ) ? json_decode( stripslashes( $_POST['query_vars'] ), true ) : array();
        
        $page++; // Next page
        $atts['paged'] = $page; // Add paged arg
        
        // Re-construct query with paged
        $args = array(
            'post_type' => 'trip',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => $page,
        );

        // If filtering via attributes
        $tax_query = array();
        if ( ! empty( $atts['destination'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'destination',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['destination'] ) ),
            );
        }
        if ( ! empty( $atts['activity'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'activities',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['activity'] ) ),
            );
        }
        if ( ! empty( $atts['trip_type'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'trip_types',
                'field'    => 'slug',
                'terms'    => array_map( 'trim', explode( ',', $atts['trip_type'] ) ),
            );
        }
        
        // Merge with query vars from current_query
        if ( ! empty( $query_vars ) ) {
            if ( ! empty( $query_vars['tax_query'] ) ) {
                // If we already have tax query from atts, merge them? 
                // Usually current_query supersedes or is used instead. 
                // Let's assume if current_query is yes, we rely on query_vars mainly.
                $tax_query = array_merge( $tax_query, $query_vars['tax_query'] );
            }
            if ( ! empty( $query_vars['meta_query'] ) ) {
                $args['meta_query'] = $query_vars['meta_query'];
            }
            if ( ! empty( $query_vars['s'] ) ) {
                $args['s'] = $query_vars['s'];
            }
            if ( ! empty( $query_vars['orderby'] ) ) {
                $args['orderby'] = $query_vars['orderby'];
            }
            if ( ! empty( $query_vars['order'] ) ) {
                $args['order'] = $query_vars['order'];
            }
        }

        if ( count( $tax_query ) > 0 ) {
            // Check if relation is needed
            if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<div class="ctd-trip-grid-item">';
                echo $this->render_trip_card( get_the_ID() );
                echo '</div>';
            }
        }
        
        wp_reset_postdata();
        die();
    }

    private function format_inr( $amount ) {
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

    public function enqueue_scripts() {
        wp_register_style( 'ctd-front-style', CTD_PLUGIN_URL . 'assets/css/front.css', array( 'select2' ), CTD_VERSION );
        wp_register_style( 'ctd-glightbox-style', CTD_PLUGIN_URL . 'assets/css/glightbox.min.css', array(), '3.1.0' );
        wp_register_style( 'ctd-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2' );
        
        // Enqueue Select2
        wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
        wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

        wp_register_script( 'ctd-glightbox-script', CTD_PLUGIN_URL . 'assets/js/glightbox.min.js', array(), '3.1.0', true );
        wp_register_script( 'ctd-front-script', CTD_PLUGIN_URL . 'assets/js/front.js', array('jquery', 'ctd-glightbox-script', 'select2'), CTD_VERSION, true );
        
        $post_id = get_queried_object_id();
        $extra_enabled   = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_extra_services', true ) : 0;
        $flights_enabled = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_flights', true ) : 0;
        $meals_enabled   = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_meals', true ) : 0;
        $downloads_enabled = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_downloads', true ) : 0;
        $more_info_enabled = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_more_info', true ) : 0;
        $booking_enabled   = $post_id ? (int) get_post_meta( $post_id, 'ctd_enable_booking', true ) : 0;
        wp_localize_script( 'ctd-front-script', 'ctdGlobals', array(
            'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
            'extraServicesEnabled' => $extra_enabled,
            'flightsEnabled'       => $flights_enabled,
            'mealsEnabled'         => $meals_enabled,
            'downloadsEnabled'     => $downloads_enabled,
            'moreInfoEnabled'      => $more_info_enabled,
            'bookingEnabled'       => $booking_enabled,
        ) );

        // Trip singles, archives and trip taxonomies need the travel stylesheet
        // and script even when no ctd_* shortcode renders on the page.
        if ( $this->is_trip_context() ) {
            wp_enqueue_style( 'ctd-front-style' );
            wp_enqueue_script( 'ctd-front-script' );
        }
    }

    /**
     * Whether the current request is a trip page.
     *
     * @return bool
     */
    private function is_trip_context() {
        if ( is_singular( 'trip' ) || is_post_type_archive( 'trip' ) ) {
            return true;
        }

        if ( is_tax( array( 'destination', 'activities', 'trip_types', 'difficulty', 'trip_tag' ) ) ) {
            return true;
        }

        return is_search() && isset( $_GET['post_type'] ) && 'trip' === sanitize_key( wp_unslash( $_GET['post_type'] ) );
    }

    /**
     * General Trip Data Shortcode
     */
    public function trip_data_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'field' => '',
            'post_id' => '',
            'class' => '',
        ), $atts );

        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $field = sanitize_text_field( $atts['field'] );
        if ( empty( $field ) ) return '';

        $value = get_post_meta( $post_id, $field, true );

        if ( $field === 'ctd_map_image' && $value ) {
            return '<img src="' . esc_url( wp_get_attachment_image_url( $value, 'full' ) ) . '" class="' . esc_attr( $atts['class'] ) . '" />';
        }
        
        if ( $field === 'ctd_map_iframe' ) {
            if ( empty( $value ) ) {
                // Fallback to Image
                $img_id = get_post_meta( $post_id, 'ctd_map_image', true );
                if ( $img_id ) {
                    return '<img src="' . esc_url( wp_get_attachment_image_url( $img_id, 'full' ) ) . '" class="' . esc_attr( $atts['class'] ) . '" />';
                }
            } else {
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
                $sanitized = wp_kses( $value, $allowed_tags );
                $extra_class = trim( 'ctd-map-iframe ' . ( $atts['class'] ? $atts['class'] : '' ) );
                return '<div class="' . esc_attr( $extra_class ) . '">' . $sanitized . '</div>';
            }
        }

        if ( ( $field === 'ctd_min_travellers' || $field === 'ctd_total_seats' ) && ( $value == -1 || $value === '-1' ) ) {
            return '<span class="' . esc_attr( $atts['class'] ) . '">' . __( 'Unlimited', 'cotlas-travel' ) . '</span>';
        }

        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }

        return '<span class="' . esc_attr( $atts['class'] ) . '">' . do_shortcode( wp_kses_post( $value ) ) . '</span>';
    }
    
    private function flight_time( $hour, $min ) {
        $h = str_pad( trim( (string) $hour ), 2, '0', STR_PAD_LEFT );
        $m = str_pad( trim( (string) $min ), 2, '0', STR_PAD_LEFT );
        if ( $h === '' && $m === '' ) return '';
        return $h . ':' . $m;
    }
    
    public function outbound_flight_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'post_id' => '', 'field' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';
        $flights = get_post_meta( $post_id, 'ctd_flights', true );
        $out = isset( $flights['outbound'] ) ? $flights['outbound'] : array();
        $field = sanitize_text_field( $atts['field'] );
        if ( $field === 'airline' || $field === 'from' || $field === 'to' ) {
            $val = isset( $out[ $field ] ) ? $out[ $field ] : '';
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        if ( $field === 'departure_time' ) {
            $val = $this->flight_time( isset( $out['dep_hour'] ) ? $out['dep_hour'] : '', isset( $out['dep_min'] ) ? $out['dep_min'] : '' );
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        if ( $field === 'arrival_time' ) {
            $val = $this->flight_time( isset( $out['arr_hour'] ) ? $out['arr_hour'] : '', isset( $out['arr_min'] ) ? $out['arr_min'] : '' );
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        return '';
    }
    
    public function inbound_flight_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'post_id' => '', 'field' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';
        $flights = get_post_meta( $post_id, 'ctd_flights', true );
        $in = isset( $flights['inbound'] ) ? $flights['inbound'] : array();
        $field = sanitize_text_field( $atts['field'] );
        if ( $field === 'airline' || $field === 'from' || $field === 'to' ) {
            $val = isset( $in[ $field ] ) ? $in[ $field ] : '';
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        if ( $field === 'departure_time' ) {
            $val = $this->flight_time( isset( $in['dep_hour'] ) ? $in['dep_hour'] : '', isset( $in['dep_min'] ) ? $in['dep_min'] : '' );
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        if ( $field === 'arrival_time' ) {
            $val = $this->flight_time( isset( $in['arr_hour'] ) ? $in['arr_hour'] : '', isset( $in['arr_min'] ) ? $in['arr_min'] : '' );
            return $val !== '' ? '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $val ) . '</span>' : '';
        }
        return '';
    }

    /**
     * Highlights List (Checkmarks)
     */
    public function highlights_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        
        $highlights = get_post_meta( $post_id, 'ctd_highlights', true );
        if ( empty( $highlights ) || ! is_array( $highlights ) ) return '';

        $html = '<ul class="ctd-highlights-list ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $highlights as $highlight ) {
            $html .= '<li>';
            $html .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z" fill="currentColor"/></svg>';
            $html .= '<span>' . esc_html( $highlight ) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Itinerary Accordion/Timeline
     */
    public function itinerary_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_style( 'ctd-fontawesome' );
        wp_enqueue_script( 'ctd-front-script' );
        
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();

        $itineraries = get_post_meta( $post_id, 'ctd_itineraries', true );
        if ( empty( $itineraries ) || ! is_array( $itineraries ) ) return '';

        $html = '<div class="ctd-itinerary-container ' . esc_attr( $atts['class'] ) . '">';
        
        // Header with Expand All Toggle
        $html .= '<div class="ctd-itinerary-top-bar">';
        $html .= '<h3 class="ctd-itinerary-main-title">' . __( '', 'cotlas-travel' ) . '</h3>';
        $html .= '<div class="ctd-expand-toggle-wrapper">';
        $html .= '<label class="ctd-switch-label" for="ctd-itinerary-expand-all">' . __( 'Expand all', 'cotlas-travel' ) . '</label>';
        $html .= '<label class="ctd-switch">';
        $html .= '<input type="checkbox" id="ctd-itinerary-expand-all">';
        $html .= '<span class="ctd-slider round"></span>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="ctd-itinerary-wrapper">';
        foreach ( $itineraries as $index => $item ) {
            $day = $index + 1;
            $title = isset( $item['title'] ) ? $item['title'] : '';
            $content = isset( $item['content'] ) ? $item['content'] : '';
            
            // Clean content: remove Gutenberg block comments and escape properly
            $content = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $content);
            
            $image_id = isset( $item['image_id'] ) ? $item['image_id'] : '';
            
            // First item expanded by default
            $active_class = ( $index === 0 ) ? ' active' : '';

            $html .= '<div class="ctd-itinerary-item' . $active_class . '">';
            
            // Marker Icon (Pin for active, Circle for inactive)
            $html .= '<div class="ctd-itinerary-marker">';
            $html .= '<span class="ctd-marker-circle"></span>';
            $html .= '<span class="ctd-marker-pin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>';
            $html .= '</div>';

            $html .= '<div class="ctd-itinerary-header">';
            $html .= '<h4 class="ctd-itinerary-day-title">' . sprintf( __( 'Day %d : %s', 'cotlas-travel' ), $day, esc_html( $title ) ) . '</h4>';
            $html .= '<span class="ctd-itinerary-toggle-icon"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>';
            $html .= '</div>';
            
            // Flex container for Image + Content
            $html .= '<div class="ctd-itinerary-content">';
            $html .= '<div class="ctd-itinerary-flex">';
            
            if ( $image_id ) {
                $html .= '<div class="ctd-itinerary-image">';
                // Force 300x300 via CSS/attributes, but request a large enough size
                $html .= wp_get_attachment_image( $image_id, 'medium_large' ); 
                $html .= '</div>';
            }
            
            $html .= '<div class="ctd-itinerary-text">' . wp_kses_post( $content ) . '</div>';
            
            $html .= '</div>'; // .ctd-itinerary-flex
            $html .= '</div>'; // .ctd-itinerary-content
            $html .= '</div>'; // .ctd-itinerary-item
        }
        $html .= '</div>'; // .ctd-itinerary-wrapper
        $html .= '</div>'; // .ctd-itinerary-container
        return $html;
    }
    
    public function meals_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';
        $meals = get_post_meta( $post_id, 'ctd_meals', true );
        if ( empty( $meals ) || ! is_array( $meals ) ) return '';
        
        $html = '<ul class="ctd-meals-list ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $meals as $meal ) {
            $html .= '<li>';
            $html .= '<svg width="20" height="20" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">';
            $html .= '<path d="M123.183,0C62.622,0,13.525,54.265,13.525,121.199c0,54.147,32.137,99.971,76.471,115.518v56.8h66.371v-56.792 c44.335-15.547,76.471-61.379,76.471-115.526C232.838,54.265,183.744,0,123.183,0z M73.652,81.714 c-6.56,11.194-10.455,24.789-10.451,39.485c0,5.321-4.308,9.624-9.621,9.624s-9.621-4.304-9.621-9.624 c0-18.1,4.788-35.031,13.092-49.21c8.298-14.179,20.147-25.656,34.35-32.696c4.763-2.36,10.534-0.408,12.89,4.354 c2.356,4.754,0.409,10.526-4.35,12.886C89.412,61.738,80.212,70.504,73.652,81.714z"></path>';
            $html .= '<path d="M471.539,0c-14.876,0-26.936,12.061-26.936,26.933v115.434c0,23.604-19.722,34.655-32.899,34.655 c0.12-1.476,0.192-2.794,0.192-3.87V26.933C411.895,12.061,399.839,0,384.963,0s-26.936,12.061-26.936,26.933v146.22 c0,1.076,0.071,2.394,0.196,3.87c-13.183,0-32.9-11.051-32.9-34.655V26.933C325.323,12.061,313.263,0,298.387,0 c-14.876,0-26.936,12.061-26.936,26.933v121.856c0,52.529,39.076,78.903,75.107,89.579c0.847,0.259,5.218,1.268,5.218,3.403 c0,2.886,0,4.479,0,4.479v47.266h66.371V246.25c0,0,0-1.593,0-4.479c0-2.135,4.375-3.144,5.217-3.403 c36.036-10.676,75.112-37.05,75.112-89.579V26.933C498.475,12.061,486.415,0,471.539,0z"></path>';
            $html .= '<path d="M166.204,308.555H80.158c-5.722,0-10.363,4.645-10.363,10.368v139.656c0,0,0,0.026,0,0.042 c0,29.485,23.904,53.38,53.384,53.38c29.485,0,53.389-23.896,53.389-53.38V318.922C176.568,313.2,171.93,308.555,166.204,308.555z M123.183,465.167c-7.794,0-14.112-6.314-14.112-14.104c0-7.79,6.318-14.112,14.112-14.112c7.79,0,14.108,6.322,14.108,14.112 C137.292,458.854,130.973,465.167,123.183,465.167z M123.183,366.405c-7.794,0-14.112-6.313-14.112-14.104 c0-7.798,6.318-14.113,14.112-14.113c7.79,0,14.108,6.314,14.108,14.113C137.292,360.092,130.973,366.405,123.183,366.405z"></path>';
            $html .= '<path d="M427.988,308.555h-86.05c-5.722,0-10.359,4.645-10.359,10.368v139.656c0,0-0.004,0.026-0.004,0.042 c0,29.485,23.905,53.38,53.384,53.38c29.485,0,53.389-23.896,53.389-53.38V318.922C438.348,313.2,433.71,308.555,427.988,308.555z M384.963,465.167c-7.794,0-14.112-6.314-14.112-14.104c0-7.79,6.318-14.112,14.112-14.112c7.79,0,14.108,6.322,14.108,14.112 C399.072,458.854,392.753,465.167,384.963,465.167z M384.963,366.405c-7.794,0-14.112-6.313-14.112-14.104 c0-7.798,6.318-14.113,14.112-14.113c7.79,0,14.108,6.314,14.108,14.113C399.072,360.092,392.753,366.405,384.963,366.405z"></path>';
            $html .= '</svg>';
            $html .= '<span>' . esc_html( $meal ) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    public function cost_includes_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';
        $items = get_post_meta( $post_id, 'ctd_cost_includes', true );
        if ( empty( $items ) || ! is_array( $items ) ) return '';
        $html = '<ul class="ctd-includes-list ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $items as $it ) {
            $html .= '<li>';
            $html .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z" /></svg>';
            $html .= '<span>' . esc_html( $it ) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    public function cost_excludes_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! $post_id ) return '';
        $items = get_post_meta( $post_id, 'ctd_cost_excludes', true );
        if ( empty( $items ) || ! is_array( $items ) ) return '';
        $html = '<ul class="ctd-excludes-list ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $items as $it ) {
            $html .= '<li>';
            $html .= '<svg fill="#000000" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="cross-circle" class="icon glyph"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm3.71,12.29a1,1,0,0,1,0,1.42,1,1,0,0,1-1.42,0L12,13.42,9.71,15.71a1,1,0,0,1-1.42,0,1,1,0,0,1,0-1.42L10.58,12,8.29,9.71A1,1,0,0,1,9.71,8.29L12,10.58l2.29-2.29a1,1,0,0,1,1.42,1.42L13.42,12Z"></path></g></svg>';
            $html .= '<span>' . esc_html( $it ) . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * FAQs Accordion
     */
    public function faqs_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_style( 'ctd-fontawesome' );
        wp_enqueue_script( 'ctd-front-script' );

        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '', 'expand_all' => 'false' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();

        $title = get_post_meta( $post_id, 'ctd_faqs_title', true );
        $faqs = get_post_meta( $post_id, 'ctd_faqs', true );
        if ( empty( $faqs ) || ! is_array( $faqs ) ) return '';

        $html = '<div class="ctd-faqs-section ' . esc_attr( $atts['class'] ) . '">';
        $html .= '<div class="ctd-faqs-wrapper">';
        
        // Expand all toggle if needed
        if ( $atts['expand_all'] === 'true' ) {
             // Logic could be added here or via JS
        }

        foreach ( $faqs as $i => $faq ) {
            $active = $i === 0 ? ' active' : '';
            $html .= '<div class="ctd-faq-item' . $active . '">';
            $html .= '<div class="ctd-faq-header">';
            $html .= '<h4 class="ctd-faq-question" style="margin:0;">' . esc_html( $faq['question'] ) . '</h4>';
            $html .= '<span class="ctd-faq-icon"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></span>';
            $html .= '</div>';
            $ans = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $faq['answer']);
            $content_style = $i === 0 ? ' style="display:block;"' : '';
            $html .= '<div class="ctd-faq-content"' . $content_style . '>' . wp_kses_post( $ans ) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>'; // .ctd-faqs-wrapper
        $html .= '</div>'; // .ctd-faqs-section
        return $html;
    }

    /**
     * Overview Section
     */
    public function overview_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();

        $title = get_post_meta( $post_id, 'ctd_overview_title', true );
        $content = get_post_meta( $post_id, 'ctd_overview_content', true );

        if ( empty( $content ) ) return '';

        $content = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $content);
        $html = '<div class="ctd-overview-section ' . esc_attr( $atts['class'] ) . '">';
        if ( $title ) {
            $html .= '<h2 class="ctd-section-title">' . esc_html( $title ) . '</h2>';
        }
        $html .= '<div class="ctd-overview-content">' . wp_kses_post( $content ) . '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * More Info Section
     */
    public function more_info_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! get_post_meta( $post_id, 'ctd_enable_more_info', true ) ) return '';

        $title = get_post_meta( $post_id, 'ctd_more_info_title', true );
        $content = get_post_meta( $post_id, 'ctd_more_info_content', true );

        if ( empty( $content ) ) return '';

        $content = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $content);
        $html = '<div class="ctd-more-info-section ' . esc_attr( $atts['class'] ) . '">';
        if ( $title ) {
            $html .= '<h2 class="ctd-section-title">' . esc_html( $title ) . '</h2>';
        }
        $html .= '<div class="ctd-more-info-content">' . wp_kses_post( $content ) . '</div>';
        $html .= '</div>';
        return $html;
    }

    // ... Other shortcodes (Trip Facts, Gallery, Video, Downloads, Services, Pricing) ...
    // I will include them here with class support.

    public function trip_facts_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        $facts = get_post_meta( $post_id, 'ctd_trip_facts', true );
        if ( empty( $facts ) || ! is_array( $facts ) ) return '';
        $html = '<div class="ctd-trip-facts ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $facts as $fact ) {
            $html .= '<div class="ctd-fact-item">';
            $html .= '<span class="ctd-fact-label"><strong>' . esc_html( $fact['label'] ) . ':</strong></span> ';
            $html .= '<span class="ctd-fact-value">' . esc_html( $fact['value'] ) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function gallery_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_style( 'ctd-glightbox-style' );
        wp_enqueue_script( 'ctd-front-script' );

        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        
        $gallery_enabled = get_post_meta( $post_id, 'ctd_gallery_enable', true );
        $gallery_images  = get_post_meta( $post_id, 'ctd_gallery_images', true );
        
        // Handle Featured Image Logic
        $featured_img_id = get_post_thumbnail_id( $post_id );
        $display_images = array();
        $is_single_image = false;

        // Determine Images to Display
        if ( empty( $gallery_images ) || ! is_array( $gallery_images ) || ! $gallery_enabled ) {
            // Fallback: Featured Image Only
            if ( $featured_img_id ) {
                $display_images = array( $featured_img_id );
                $is_single_image = true;
            } else {
                // No gallery AND no featured image - Return nothing?
                // User asked: "if no gallery is added it should display atleast featured image... if gallery video is enabled... include video also"
                // If there are videos but no images, we should probably still render something? 
                // But the structure relies on an image grid. 
                // Let's assume we need at least one image to show the card.
                // If really no image, we can't show the "card" effectively.
                // But I'll return empty string here as fallback if NO image exists at all.
                // UNLESS user wants a placeholder?
                // I'll return empty string for now to be safe, as "ctd-no-image" is handled inside slider but not grid.
                return ''; 
            }
        } else {
            // Gallery Exists
            $display_images = $gallery_images;
            
            // Prepend Featured Image if available and not already in gallery
            if ( $featured_img_id ) {
                if ( ! in_array( $featured_img_id, $display_images ) ) {
                    array_unshift( $display_images, $featured_img_id );
                }
            }
        }
        
        // Video Gallery Logic
        $video_gallery_enabled = get_post_meta( $post_id, 'ctd_video_gallery_enable', true );
        $videos = array();
        if ( $video_gallery_enabled ) {
            $videos = get_post_meta( $post_id, 'ctd_video_gallery', true );
            if ( ! is_array( $videos ) ) {
                $videos = array();
            }
            // Ensure keys are reset (0, 1, 2...) and remove empty values
            $videos = array_values( array_filter( $videos ) );
        }

        // Prepare Image Lists
        $visible_images = array_slice($display_images, 0, 4);
        $hidden_images = array_slice($display_images, 4);
        
        // CSS Classes
        $wrapper_class = 'ctd-gallery-grid-layout ' . esc_attr( $atts['class'] );
        if ( $is_single_image ) {
            $wrapper_class .= ' ctd-single-image-gallery ctd-featured-image';
        }
        
        $html = '<div class="' . $wrapper_class . '">';
        
        // Render Visible Images
        foreach ( $visible_images as $index => $img_id ) {
            $full_url = wp_get_attachment_image_url( $img_id, 'full' );
            
            // Thumb size: First image big, others smaller (unless single mode)
            $thumb_size = ($index === 0 || $is_single_image) ? 'large' : 'medium_large'; 
            if ( $is_single_image ) {
                $thumb_size = 'full'; // Ensure best quality for single featured image
            }
            $thumb_url = wp_get_attachment_image_url( $img_id, $thumb_size );
            
            $item_class = 'ctd-gallery-item item-' . ($index + 1);
            
            $html .= '<div class="' . $item_class . '">';
            
            // Main Image Link (Triggers Lightbox)
            $html .= '<a href="' . esc_url( $full_url ) . '" class="ctd-lightbox" data-gallery="trip-gallery">';
            $html .= '<img src="' . esc_url( $thumb_url ) . '" loading="lazy" />';
            $html .= '</a>';
            
            // Overlay Logic: Add to the last visible image
            // In Grid (4 items): Index 3
            // In Single Mode (1 item): Index 0
            // In Partial Grid (e.g. 2 or 3 items): Last Index
            $is_last_visible = ($index === count($visible_images) - 1);
            
            if ( $is_last_visible ) {
                 $html .= '<div class="ctd-gallery-overlay">';
                 
                 // Gallery Button
                 // If single image mode, clicking this just opens the image again (which is fine, consistent UX)
                 // We use a span that triggers click on the sibling anchor
                 $html .= '<span class="ctd-gallery-btn" onclick="this.closest(\'.ctd-gallery-item\').querySelector(\'.ctd-lightbox\').click();">';
                 $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg> ';
                 $html .= __( 'Gallery', 'cotlas-travel' );
                 $html .= '</span>';

                 // Video Button
                 if ( ! empty( $videos ) ) {
                     // First video link is the button itself
                     $first_video = trim( $videos[0] );
                     
                     // If it's a YouTube/Vimeo embed code (iframe), extract src
                     if ( strpos( $first_video, '<iframe' ) !== false ) {
                         preg_match( '/src="([^"]+)"/', $first_video, $match );
                         if ( isset( $match[1] ) ) {
                             $first_video = $match[1];
                         }
                     }
                     
                     $html .= '<a href="' . esc_url( $first_video ) . '" class="ctd-gallery-btn ctd-video-btn ctd-lightbox-video" data-gallery="trip-video-gallery" style="margin-left:10px;">';
                     $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play-circle"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg> ';
                     $html .= __( 'Video', 'cotlas-travel' );
                     $html .= '</a>';
                 }

                 $html .= '</div>'; // .ctd-gallery-overlay
            }

            $html .= '</div>'; // .ctd-gallery-item
        }

        // Hidden links for the remaining images
        if ( ! empty( $hidden_images ) ) {
            $html .= '<div style="display:none;">';
            foreach ( $hidden_images as $img_id ) {
                $full_url = wp_get_attachment_image_url( $img_id, 'full' );
                $html .= '<a href="' . esc_url( $full_url ) . '" class="ctd-lightbox" data-gallery="trip-gallery"></a>';
            }
            $html .= '</div>';
        }
        
        // Hidden links for the remaining videos
        if ( ! empty( $videos ) && count( $videos ) > 1 ) {
            $hidden_videos = array_slice( $videos, 1 );
            $html .= '<div style="display:none;">';
            foreach ( $hidden_videos as $video_url ) {
                $video_url = trim( $video_url );
                // Check if it's an iframe
                if ( strpos( $video_url, '<iframe' ) !== false ) {
                    preg_match( '/src="([^"]+)"/', $video_url, $match );
                    if ( isset( $match[1] ) ) {
                        $video_url = $match[1];
                    }
                }
                $html .= '<a href="' . esc_url( $video_url ) . '" class="ctd-lightbox-video" data-gallery="trip-video-gallery"></a>';
            }
            $html .= '</div>';
        }

        $html .= '</div>'; // Close .ctd-gallery-grid-layout here

        // Add dots container OUTSIDE the gallery grid/flex container
        $html .= '<div class="ctd-gallery-dots"></div>';

        return $html;
    }

    public function video_gallery_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! get_post_meta( $post_id, 'ctd_video_gallery_enable', true ) ) return '';
        $videos = get_post_meta( $post_id, 'ctd_video_gallery', true );
        if ( empty( $videos ) || ! is_array( $videos ) ) return '';
        $html = '<div class="ctd-video-grid ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $videos as $video ) {
            $html .= '<div class="ctd-video-item">' . wp_oembed_get( $video ) . '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function downloads_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        if ( ! get_post_meta( $post_id, 'ctd_enable_downloads', true ) ) return '';
        $downloads = get_post_meta( $post_id, 'ctd_downloads', true );
        if ( empty( $downloads ) || ! is_array( $downloads ) ) return '';
        $title = get_post_meta( $post_id, 'ctd_downloads_title', true );
        $html = '<div class="ctd-downloads ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $downloads as $item ) {
            $file_url = wp_get_attachment_url( $item['file_id'] );
            if ( $file_url ) {
                $html .= '<div class="ctd-download-item"><a href="' . esc_url( $file_url ) . '" target="_blank" class="button">' . esc_html( $item['title'] ) . '</a></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    public function extra_services_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_script( 'ctd-front-script' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        $services = get_post_meta( $post_id, 'ctd_extra_services', true );
        if ( empty( $services ) || ! is_array( $services ) ) return '';
        $html = '<div class="ctd-extra-services ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $services as $service ) {
            $html .= '<div class="ctd-service-item">';
            if ( ! empty( $service['image_id'] ) ) {
                $html .= '<div class="ctd-service-image">' . wp_get_attachment_image( intval( $service['image_id'] ), 'thumbnail' ) . '</div>';
            }
            $html .= '<div class="ctd-service-header">';
            $html .= '<span class="ctd-service-name">' . esc_html( $service['name'] ) . '</span>';
            $ptype_label = '';
            switch ( isset($service['pricing_type']) ? $service['pricing_type'] : '' ) {
                case 'per_day': $ptype_label = 'Per Day'; break;
                case 'per_hour': $ptype_label = 'Per Hour'; break;
                case 'per_person': $ptype_label = 'Per Person'; break;
                case 'per_group': $ptype_label = 'Per Group'; break;
                case 'per_vehicle': $ptype_label = 'Per Vehicle'; break;
                case 'per_trip': $ptype_label = 'Per Trip'; break;
                default: $ptype_label = 'Per Item'; break;
            }
            $price_str = is_numeric($service['price']) ? '₹' . $this->format_inr( $service['price'] ) . '/-' : $service['price'];
            $html .= '<span class="ctd-service-price">' . $price_str . ' <span class="pricing-type">' . esc_html( $ptype_label ) . '</span></span>'; 
            $html .= '</div>';
            if ( ! empty( $service['description'] ) ) {
                $desc = preg_replace('/<!--\s*\/?wp:.*?-->/', '', $service['description']);
                $html .= '<div class="ctd-service-desc">' . do_shortcode( wp_kses_post( $desc ) ) . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function pricing_table_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        $atts = shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        $packages = get_post_meta( $post_id, 'ctd_pricing_packages', true );
        if ( empty( $packages ) || ! is_array( $packages ) ) return '';
        
        $categories = get_terms( array( 'taxonomy' => 'pricing_category', 'hide_empty' => false ) );
        $cat_map = array();
        if ( ! is_wp_error( $categories ) ) {
            foreach ( $categories as $cat ) {
                $cat_map[$cat->term_id] = $cat->name;
            }
        }

        $html = '<div class="ctd-pricing-table ' . esc_attr( $atts['class'] ) . '">';
        foreach ( $packages as $pkg ) {
            $html .= '<div class="ctd-pricing-package">';
            $html .= '<h3 class="ctd-pkg-title">' . esc_html( $pkg['title'] ) . '</h3>';
            if ( ! empty( $pkg['general_desc'] ) ) {
                $html .= '<div class="ctd-pkg-desc">' . wpautop( esc_html( $pkg['general_desc'] ) ) . '</div>';
            }
            if ( ! empty( $pkg['enable_dates'] ) && ! empty( $pkg['dates'] ) ) {
                $html .= '<div class="ctd-pkg-dates"><strong>Available Dates:</strong><ul>';
                foreach ( $pkg['dates'] as $date ) {
                    $html .= '<li>' . date_i18n( get_option( 'date_format' ), strtotime( $date ) ) . '</li>';
                }
                $html .= '</ul></div>';
            }
            if ( ! empty( $pkg['prices'] ) ) {
                $html .= '<div class="ctd-pkg-prices">';
                foreach ( $pkg['prices'] as $cat_id => $price_data ) {
                    if ( isset( $cat_map[$cat_id] ) && ! empty( $price_data['regular_price'] ) ) {
                        $html .= '<div class="ctd-price-row">';
                        $html .= '<span class="ctd-price-cat">' . esc_html( $cat_map[$cat_id] ) . '</span>';
                        $html .= '<span class="gb-text price">₹' . esc_html( $this->format_inr( $price_data['regular_price'] ) ) . ' <span class="pricing-type">' . ( $price_data['pricing_type'] === 'group' ? 'Per Group' : 'Per Person' ) . '</span></span>';
                        $html .= '</div>';
                    }
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Booking URL
     */
    public function booking_url_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'post_id' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        
        $enable_booking = get_post_meta( $post_id, 'ctd_enable_booking', true );
        if ( ! $enable_booking ) return '';
        
        $url = get_post_meta( $post_id, 'ctd_custom_booking_link', true );
        return esc_url( $url );
    }

    /**
     * Itinerary PDF Download URL
     */
    public function itinerary_pdf_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'post_id' => '' ), $atts );
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        
        $enable_pdf = get_post_meta( $post_id, 'ctd_enable_itinerary_pdf', true );
        if ( ! $enable_pdf ) return '';
        
        // Generate dynamic URL
        $url = add_query_arg( array(
            'ctd_action' => 'download_itinerary',
            'post_id'    => $post_id,
        ), home_url( '/' ) );
        
        return esc_url( $url );
    }

    /**
     * Itinerary PDF Download Button
     */
    public function download_itinerary_btn_shortcode( $atts ) {
        $atts = shortcode_atts( array( 
            'post_id' => '', 
            'class' => '',
            'label' => 'Download Itinerary' 
        ), $atts );
        
        $post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
        
        $enable_pdf = get_post_meta( $post_id, 'ctd_enable_itinerary_pdf', true );
        if ( ! $enable_pdf ) return '';

        $url = add_query_arg( array(
            'ctd_action' => 'download_itinerary',
            'post_id'    => $post_id,
        ), home_url( '/' ) );

        return '<a href="' . esc_url( $url ) . '" class="ctd-download-btn ' . esc_attr( $atts['class'] ) . '" target="_blank">' . esc_html( $atts['label'] ) . '</a>';
    }

    /**
     * Taxonomy Term Data Shortcode
     */
    public function term_data_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'field' => 'title', // title, description, image, image_url, link, count
            'taxonomy' => '',
            'term_id' => '',
            'class' => '',
            'image_size' => 'full'
        ), $atts );

        $term = null;

        if ( ! empty( $atts['term_id'] ) ) {
            $term = get_term( intval( $atts['term_id'] ), $atts['taxonomy'] );
        } elseif ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
        }

        if ( ! $term || is_wp_error( $term ) ) {
            return '';
        }
        
        // Ensure we are working with a valid object
        if ( ! isset( $term->term_id ) ) return '';

        $field = $atts['field'];

        switch ( $field ) {
            case 'title':
            case 'name':
                return '<span class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $term->name ) . '</span>';
            case 'description':
                return '<div class="' . esc_attr( $atts['class'] ) . '">' . wp_kses_post( wpautop( $term->description ) ) . '</div>';
            case 'image':
                $image_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
                if ( $image_id ) {
                    return wp_get_attachment_image( $image_id, $atts['image_size'], false, array( 'class' => $atts['class'] ) );
                }
                return '';
            case 'image_url':
                $image_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
                if ( $image_id ) {
                    return esc_url( wp_get_attachment_image_url( $image_id, $atts['image_size'] ) );
                }
                return '';
            case 'link':
            case 'url':
                return esc_url( get_term_link( $term ) );
            case 'count':
                return esc_html( $term->count );
            default:
                return '';
        }
    }

    /**
     * Search Bar
     */
    public function search_bar_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_style( 'ctd-fontawesome' );
        wp_enqueue_script( 'ctd-front-script' );
        
        $atts = shortcode_atts( array( 
            'class' => '',
            'fields' => 'keyword,destination,activity' // Default fields
        ), $atts );
        
        $fields_str = $atts['fields'];
        if ( empty( $fields_str ) ) {
            $fields_str = 'keyword,destination,activity';
        }
        $fields = array_map( 'trim', explode( ',', $fields_str ) );

        $action_url = home_url( '/' );
        
        $s = get_search_query();
        $selected_dest = get_query_var( 'destination' );
        $selected_act = get_query_var( 'activities' );
        $selected_type = get_query_var( 'trip_types' );

        $html = '<form role="search" method="get" class="ctd-search-form ' . esc_attr( $atts['class'] ) . '" action="' . esc_url( $action_url ) . '">';
        $html .= '<input type="hidden" name="post_type" value="trip" />';
        
        $html .= '<div class="ctd-search-bar">';
        
        foreach ( $fields as $field ) {
            switch ( $field ) {
                case 'keyword':
                    $html .= '<div class="ctd-search-field ctd-search-keyword">';
                    $html .= '<i class="fa-solid fa-magnifying-glass"></i>';
                    $html .= '<input type="text" name="s" placeholder="' . __( 'Search...', 'cotlas-travel' ) . '" value="' . esc_attr( $s ) . '" />';
                    $html .= '</div>';
                    break;
                
                case 'destination':
                    $destinations = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => true ) );
                    $html .= '<div class="ctd-search-field ctd-search-destination">';
                    $html .= '<svg data-prefix="fas" data-icon="map-marker" width="12" height="15" viewBox="0 0 12 15" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M6 0C4.4087 0 2.88258 0.632141 1.75736 1.75736C0.632141 2.88258 0 4.4087 0 6C0 10.05 5.2875 14.625 5.5125 14.82C5.64835 14.9362 5.82124 15 6 15C6.17877 15 6.35165 14.9362 6.4875 14.82C6.75 14.625 12 10.05 12 6C12 4.4087 11.3679 2.88258 10.2426 1.75736C9.11742 0.632141 7.5913 0 6 0ZM6 13.2375C4.4025 11.7375 1.5 8.505 1.5 6C1.5 4.80653 1.97411 3.66193 2.81802 2.81802C3.66193 1.97411 4.80653 1.5 6 1.5C7.19347 1.5 8.33807 1.97411 9.18198 2.81802C10.0259 3.66193 10.5 4.80653 10.5 6C10.5 8.505 7.5975 11.745 6 13.2375ZM6 3C5.40666 3 4.82664 3.17595 4.33329 3.50559C3.83994 3.83524 3.45542 4.30377 3.22836 4.85195C3.0013 5.40013 2.94189 6.00333 3.05764 6.58527C3.1734 7.16721 3.45912 7.70176 3.87868 8.12132C4.29824 8.54088 4.83279 8.8266 5.41473 8.94236C5.99667 9.05811 6.59987 8.9987 7.14805 8.77164C7.69623 8.54458 8.16477 8.16006 8.49441 7.66671C8.82405 7.17336 9 6.59334 9 6C9 5.20435 8.68393 4.44129 8.12132 3.87868C7.55871 3.31607 6.79565 3 6 3ZM6 7.5C5.70333 7.5 5.41332 7.41203 5.16665 7.2472C4.91997 7.08238 4.72771 6.84811 4.61418 6.57403C4.50065 6.29994 4.47094 5.99834 4.52882 5.70736C4.5867 5.41639 4.72956 5.14912 4.93934 4.93934C5.14912 4.72956 5.41639 4.5867 5.70737 4.52882C5.99834 4.47094 6.29994 4.50065 6.57403 4.61418C6.84811 4.72771 7.08238 4.91997 7.2472 5.16665C7.41203 5.41332 7.5 5.70333 7.5 6C7.5 6.39782 7.34197 6.77936 7.06066 7.06066C6.77936 7.34196 6.39783 7.5 6 7.5Z" fill="currentColor"></path> </svg>';
                    $html .= '<select name="destination" class="ctd-select2">';
                    $html .= '<option value="">' . __( 'Destination', 'cotlas-travel' ) . '</option>';
                    if ( ! is_wp_error( $destinations ) ) {
                        foreach ( $destinations as $term ) {
                            $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $selected_dest, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                    }
                    $html .= '</select>';
                    $html .= '</div>';
                    break;

                case 'activity':
                    $activities = get_terms( array( 'taxonomy' => 'activities', 'hide_empty' => true ) );
                    $html .= '<div class="ctd-search-field ctd-search-activity">';
                    $html .= '<i class="fa-solid fa-person-hiking"></i>';
                    $html .= '<select name="activities" class="ctd-select2">';
                    $html .= '<option value="">' . __( 'Activity', 'cotlas-travel' ) . '</option>';
                    if ( ! is_wp_error( $activities ) ) {
                        foreach ( $activities as $term ) {
                            $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $selected_act, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                    }
                    $html .= '</select>';
                    $html .= '</div>';
                    break;

                case 'trip_type':
                    $trip_types = get_terms( array( 'taxonomy' => 'trip_types', 'hide_empty' => true ) );
                    $html .= '<div class="ctd-search-field ctd-search-triptype">';
                    $html .= '<i class="fa-solid fa-suitcase"></i>';
                    $html .= '<select name="trip_types" class="ctd-select2">';
                    $html .= '<option value="">' . __( 'Trip Type', 'cotlas-travel' ) . '</option>';
                    if ( ! is_wp_error( $trip_types ) ) {
                        foreach ( $trip_types as $term ) {
                            $html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $selected_type, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
                        }
                    }
                    $html .= '</select>';
                    $html .= '</div>';
                    break;
            }
        }

        // Submit Button (Always at the end)
        $html .= '<div class="ctd-search-submit">';
        $html .= '<button type="submit">' . __( 'Search', 'cotlas-travel' ) . '</button>';
        $html .= '</div>';

        $html .= '</div>'; // .ctd-search-bar
        $html .= '</form>';

        return $html;
    }

    public function taxonomy_slider_shortcode( $atts ) {
        wp_enqueue_style( 'ctd-front-style' );
        wp_enqueue_script( 'ctd-front-script' );
        
        $atts = shortcode_atts( array(
            'taxonomy' => 'destination',
            'count'    => '-1',
            'class'    => '',
            'columns'  => '4',
            'autoplay' => 'no',
            'speed'    => '3000', // Autoplay interval in ms
            'loop'     => 'no',
            'navigation' => 'yes',
            'pagination' => 'yes',
            'hide_empty' => 'yes', // Default true (hide empty)
        ), $atts );

        $terms = get_terms( array(
            'taxonomy'   => $atts['taxonomy'],
            'hide_empty' => ( $atts['hide_empty'] === 'yes' ),
            'number'     => intval( $atts['count'] ),
        ) );

        if ( empty( $terms ) || is_wp_error( $terms ) ) return '';

        // Wrapper with data attributes for JS
        $wrapper_class = 'ctd-taxonomy-slider-wrapper ' . esc_attr( $atts['class'] );
        $data_attrs = ' data-autoplay="' . esc_attr( $atts['autoplay'] ) . '"';
        $data_attrs .= ' data-speed="' . esc_attr( $atts['speed'] ) . '"';
        $data_attrs .= ' data-loop="' . esc_attr( $atts['loop'] ) . '"';
        $data_attrs .= ' data-columns="' . esc_attr( $atts['columns'] ) . '"';

        $html = '<div class="' . $wrapper_class . '"' . $data_attrs . '>';
        
        // Navigation Buttons (Stacked Left)
        if ( $atts['navigation'] !== 'no' ) {
            $html .= '<div class="ctd-slider-nav-container">';
            $html .= '<button class="ctd-slider-nav ctd-prev" aria-label="Previous"><i class="fa-solid fa-arrow-left"></i></button>';
            $html .= '<button class="ctd-slider-nav ctd-next" aria-label="Next"><i class="fa-solid fa-arrow-right"></i></button>';
            $html .= '</div>';
        }

        $html .= '<div class="ctd-taxonomy-slider">';
        
        foreach ( $terms as $term ) {
            $image_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
            $link = get_term_link( $term );
            
            $html .= '<div class="ctd-taxonomy-slide">';
            $html .= '<a href="' . esc_url( $link ) . '" class="ctd-tax-card">';
            
            $html .= '<div class="ctd-tax-image">';
            if ( $image_id ) {
                $html .= wp_get_attachment_image( $image_id, 'large' );
            } else {
                 $html .= '<div class="ctd-no-image"></div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="ctd-tax-overlay">';
            $html .= '<h3 class="ctd-tax-title">' . esc_html( $term->name ) . '</h3>';
            $html .= '<span class="ctd-tax-count">' . sprintf( _n( '%s Trip', '%s Trips', $term->count, 'cotlas-travel' ), number_format_i18n( $term->count ) ) . '</span>';
            $html .= '</div>';
            
            $html .= '</a>';
            $html .= '</div>';
        }

        $html .= '</div>'; // .ctd-taxonomy-slider

        // Pagination Dots (Outside Bottom)
        if ( $atts['pagination'] !== 'no' ) {
            $html .= '<div class="ctd-slider-dots"></div>';
        }

        $html .= '</div>'; // .ctd-taxonomy-slider-wrapper

        return $html;
    }

    public function hero_image_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'index' => '1',
            'class' => '',
            'size'  => 'full',
        ), $atts );

        $index = intval( $atts['index'] );
        if ( $index < 1 || $index > 4 ) {
            $index = 1;
        }

        $image_id = get_option( 'ctd_hero_image_' . $index );
        if ( ! $image_id ) {
            return '';
        }

        return wp_get_attachment_image( $image_id, $atts['size'], false, array( 'class' => $atts['class'] ) );
    }

    public function destination_card_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'term_id' => '',
            'taxonomy' => 'destination',
            'show_image' => 'yes',
            'show_title' => 'yes',
            'show_count' => 'yes',
            'show_desc' => 'yes',
            'show_short_desc' => 'yes',
            'class' => '',
            'newly_launched' => 'no',
            'offset' => '0',
        ), $atts );

        $term_id = intval( $atts['term_id'] );
        $taxonomy = sanitize_key( $atts['taxonomy'] );
        $newly_launched = $atts['newly_launched'] === 'yes';
        $offset = intval( $atts['offset'] );
        
        $term = null;

        if ( $term_id ) {
            $term = get_term( $term_id, $taxonomy );
        } elseif ( $newly_launched ) {
            // Fetch newly launched term if no specific ID is provided
            // We fetch enough terms to ensure we can access the one at the offset
            $terms = get_terms( array(
                'taxonomy'   => $taxonomy,
                'meta_key'   => 'ctd_newly_launched',
                'meta_value' => '1',
                'number'     => $offset + 1, 
                'orderby'    => 'term_id', // Latest added
                'order'      => 'DESC',
                'hide_empty' => false,
            ) );
            
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                if ( isset( $terms[ $offset ] ) ) {
                    $term = $terms[ $offset ];
                }
            }
        }

        if ( ! $term || is_wp_error( $term ) ) {
            return '';
        }

        $term_link = get_term_link( $term );
        if ( is_wp_error( $term_link ) ) {
            $term_link = '#';
        }

        $html = '<div class="destination-container ' . esc_attr( $atts['class'] ) . '">';

        // Image
        if ( $atts['show_image'] === 'yes' ) {
            $image_id = get_term_meta( $term->term_id, 'ctd_image_id', true );
            if ( $image_id ) {
                $img_attr = array( 'class' => 'destination-image' );
                $img_attr['title'] = $term->name;
                $html .= '<a href="' . esc_url( $term_link ) . '">';
                $html .= wp_get_attachment_image( $image_id, 'full', false, $img_attr );
                $html .= '</a>';
            }
        }

        // Title
        if ( $atts['show_title'] === 'yes' ) {
            $html .= '<a class="destination-name" href="' . esc_url( $term_link ) . '">' . esc_html( $term->name ) . '</a>';
        }

        // Count
        if ( $atts['show_count'] === 'yes' && $term->count > 0 ) {
            $count_text = sprintf( _n( '%s Tour', '%s Tours', $term->count, 'cotlas-travel' ), $term->count );
            $arrow_svg = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left:5px; vertical-align:middle;"><path d="M8 0L6.59 1.41L12.17 7H0V9H12.17L6.59 14.59L8 16L16 8L8 0Z" fill="currentColor"/></svg>';
            $html .= '<a class="destination-count" href="' . esc_url( $term_link ) . '">' . esc_html( $count_text ) . ' ' . $arrow_svg . '</a>';
        }

        // Short Description
        if ( $atts['show_short_desc'] === 'yes' ) {
            $short_desc = get_term_meta( $term->term_id, 'ctd_short_description', true );
            if ( $short_desc ) {
                // User originally had a P tag, but wants a link. A tag with class destination-sub is best.
                $html .= '<a class="destination-sub" href="' . esc_url( $term_link ) . '">' . esc_html( $short_desc ) . '</a>';
            }
        }

        // Description
        if ( $atts['show_desc'] === 'yes' ) {
            if ( ! empty( $term->description ) ) {
                // Wrapping the whole description in an anchor tag
                $html .= '<a class="destination-desc" href="' . esc_url( $term_link ) . '">';
                $html .= wp_kses_post( wpautop( $term->description ) );
                $html .= '</a>';
            }
        }

        $html .= '</div>';

        return $html;
    }

}

new CTD_Shortcodes();
