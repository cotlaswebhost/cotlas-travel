<?php
/**
 * Trip Filtering & Search Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CTD_Filters {

    public function __construct() {
        // Shortcodes
        add_shortcode( 'ctd_filter_sidebar', array( $this, 'filter_sidebar_shortcode' ) );
        add_shortcode( 'ctd_archive_search', array( $this, 'archive_search_shortcode' ) );
        add_shortcode( 'ctd_sort_dropdown', array( $this, 'sort_dropdown_shortcode' ) );

        // Query Modification
        add_action( 'pre_get_posts', array( $this, 'modify_trip_query' ) );
        add_filter( 'posts_search', array( $this, 'extend_search_to_taxonomies' ), 10, 2 );
        add_filter( 'posts_join', array( $this, 'join_taxonomies_for_search' ), 10, 2 );
        add_filter( 'posts_groupby', array( $this, 'groupby_taxonomies_for_search' ), 10, 2 );
        
        // Enqueue Assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue Scripts & Styles
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'ctd-filters-css', CTD_PLUGIN_URL . 'assets/css/filters.css', array(), CTD_VERSION );
        
        // jQuery UI for Sliders
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css' );
        
        // Select2
        wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
        wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

        wp_enqueue_script( 'ctd-filters-js', CTD_PLUGIN_URL . 'assets/js/filters.js', array( 'jquery', 'jquery-ui-slider', 'select2' ), CTD_VERSION, true );
    }

    /**
     * Filter Sidebar Shortcode
     */
    public function filter_sidebar_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'class' => '',
        ), $atts );

        ob_start();
        ?>
        <div class="ctd-filter-sidebar <?php echo esc_attr( $atts['class'] ); ?>">
            <form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" class="ctd-filter-form">
                
                <div class="ctd-filter-header">
                    <h3><?php _e( 'Filter By', 'cotlas-travel' ); ?></h3>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" class="ctd-clear-all"><?php _e( 'Clear all', 'cotlas-travel' ); ?></a>
                </div>

                <?php 
                // Taxonomies to filter
                $taxonomies = array(
                    'destination' => __( 'Destination', 'cotlas-travel' ),
                    'activities'  => __( 'Activities', 'cotlas-travel' ),
                    'trip_types'  => __( 'Trip Types', 'cotlas-travel' ),
                    'difficulty'  => __( 'Difficulties', 'cotlas-travel' ),
                );

                foreach ( $taxonomies as $slug => $label ) {
                    $this->render_taxonomy_filter( $slug, $label );
                }
                
                // Price Filter
                $this->render_price_filter();

                // Duration Filter
                $this->render_duration_filter();
                
                // Hidden inputs for current query args (like 's' or 'orderby')
                // But generally, the form submission handles the main params.
                // We might need to preserve 's' if set.
                if ( get_query_var( 's' ) ) {
                    echo '<input type="hidden" name="s" value="' . esc_attr( get_query_var( 's' ) ) . '">';
                }
                ?>
                
                <button type="submit" class="ctd-apply-filters-btn"><?php _e( 'Apply Filters', 'cotlas-travel' ); ?></button>

            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Taxonomy Filter Section
     */
    private function render_taxonomy_filter( $taxonomy, $label ) {
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true, // Only show terms with trips
        ) );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return;
        }

        $current_values = isset( $_GET[ $taxonomy ] ) ? (array) $_GET[ $taxonomy ] : array();
        
        // Generate unique ID for accordion
        $id = 'ctd-filter-' . $taxonomy;
        ?>
        <div class="ctd-filter-group">
            <div class="ctd-filter-title" data-target="<?php echo esc_attr( $id ); ?>">
                <?php echo esc_html( $label ); ?>
                <span class="ctd-accordion-icon">
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.41 0.589966L6 5.16997L10.59 0.589966L12 1.99997L6 7.99997L0 1.99997L1.41 0.589966Z" fill="#333333"/>
                    </svg>
                </span>
            </div>
            <div id="<?php echo esc_attr( $id ); ?>" class="ctd-filter-content">
                <ul class="ctd-filter-list">
                    <?php 
                    $count = 0;
                    $limit = 5; // Show 5 initially
                    foreach ( $terms as $term ) {
                        $checked = in_array( $term->slug, $current_values ) ? 'checked' : '';
                        $class = $count >= $limit ? 'ctd-hidden-item' : '';
                        ?>
                        <li class="<?php echo esc_attr( $class ); ?>">
                            <label class="ctd-checkbox-label">
                                <input type="checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php echo $checked; ?>>
                                <span class="ctd-checkbox-custom"></span>
                                <span class="ctd-term-name"><?php echo esc_html( $term->name ); ?></span>
                                <span class="ctd-term-count"><?php echo esc_html( $term->count ); ?></span>
                            </label>
                        </li>
                        <?php
                        $count++;
                    }
                    ?>
                </ul>
                <?php if ( count( $terms ) > $limit ) : ?>
                    <a href="#" class="ctd-show-more" data-text-more="<?php _e('Show More', 'cotlas-travel'); ?>" data-text-less="<?php _e('Show Less', 'cotlas-travel'); ?>"><?php _e( 'Show More', 'cotlas-travel' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Price Filter (Range Slider)
     */
    private function render_price_filter() {
        // Get min/max prices from DB for bounds
        global $wpdb;
        $min_db = $wpdb->get_var( "SELECT MIN(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'ctd_min_price'" );
        $max_db = $wpdb->get_var( "SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'ctd_min_price'" );
        
        $min_db = $min_db ? floor($min_db) : 0;
        $max_db = $max_db ? ceil($max_db) : 1000;

        $current_min = isset( $_GET['min_price'] ) ? intval( $_GET['min_price'] ) : $min_db;
        $current_max = isset( $_GET['max_price'] ) ? intval( $_GET['max_price'] ) : $max_db;
        ?>
        <div class="ctd-filter-group">
            <div class="ctd-filter-title" data-target="ctd-filter-price">
                <?php _e( 'Price', 'cotlas-travel' ); ?>
                <span class="ctd-accordion-icon">
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.41 0.589966L6 5.16997L10.59 0.589966L12 1.99997L6 7.99997L0 1.99997L1.41 0.589966Z" fill="#333333"/>
                    </svg>
                </span>
            </div>
            <div id="ctd-filter-price" class="ctd-filter-content">
                <div class="ctd-range-slider" data-min="<?php echo $min_db; ?>" data-max="<?php echo $max_db; ?>" data-current-min="<?php echo $current_min; ?>" data-current-max="<?php echo $current_max; ?>"></div>
                <div class="ctd-range-inputs">
                    <span class="ctd-range-value ctd-min-val"><?php echo $current_min; ?></span>
                    <span class="ctd-range-value ctd-max-val"><?php echo $current_max; ?></span>
                    <input type="hidden" name="min_price" class="ctd-input-min" value="<?php echo $current_min; ?>">
                    <input type="hidden" name="max_price" class="ctd-input-max" value="<?php echo $current_max; ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Duration Filter (Range Slider)
     */
    private function render_duration_filter() {
        // Get min/max duration from DB
        global $wpdb;
        $min_db = $wpdb->get_var( "SELECT MIN(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'ctd_duration_days'" );
        $max_db = $wpdb->get_var( "SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = 'ctd_duration_days'" );

        $min_db = $min_db ? floor($min_db) : 1;
        $max_db = $max_db ? ceil($max_db) : 30;

        $current_min = isset( $_GET['min_days'] ) ? intval( $_GET['min_days'] ) : $min_db;
        $current_max = isset( $_GET['max_days'] ) ? intval( $_GET['max_days'] ) : $max_db;

        ?>
        <div class="ctd-filter-group">
            <div class="ctd-filter-title" data-target="ctd-filter-duration">
                <?php _e( 'Duration', 'cotlas-travel' ); ?>
                <span class="ctd-accordion-icon">
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.41 0.589966L6 5.16997L10.59 0.589966L12 1.99997L6 7.99997L0 1.99997L1.41 0.589966Z" fill="#333333"/>
                    </svg>
                </span>
            </div>
            <div id="ctd-filter-duration" class="ctd-filter-content">
                <div class="ctd-range-slider" data-min="<?php echo $min_db; ?>" data-max="<?php echo $max_db; ?>" data-current-min="<?php echo $current_min; ?>" data-current-max="<?php echo $current_max; ?>"></div>
                <div class="ctd-range-inputs">
                    <span class="ctd-range-value ctd-min-val"><?php echo $current_min; ?> <?php _e('Days', 'cotlas-travel'); ?></span>
                    <span class="ctd-range-value ctd-max-val"><?php echo $current_max; ?> <?php _e('Days', 'cotlas-travel'); ?></span>
                    <input type="hidden" name="min_days" class="ctd-input-min" value="<?php echo $current_min; ?>">
                    <input type="hidden" name="max_days" class="ctd-input-max" value="<?php echo $current_max; ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Archive Search Shortcode
     */
    public function archive_search_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'class' => '' ), $atts );
        $s = get_query_var( 's' );
        
        ob_start();
        ?>
        <div class="ctd-archive-search <?php echo esc_attr( $atts['class'] ); ?>">
            <form role="search" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>">
                <?php echo '<?xml version="1.0" encoding="utf-8"?><!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools --> 
 <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> 
 <path fill-rule="evenodd" clip-rule="evenodd" d="M11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19C12.8487 19 14.551 18.3729 15.9056 17.3199L19.2929 20.7071C19.6834 21.0976 20.3166 21.0976 20.7071 20.7071C21.0976 20.3166 21.0976 19.6834 20.7071 19.2929L17.3199 15.9056C18.3729 14.551 19 12.8487 19 11C19 6.58172 15.4183 3 11 3ZM5 11C5 7.68629 7.68629 5 11 5C14.3137 5 17 7.68629 17 11C17 14.3137 14.3137 17 11 17C7.68629 17 5 14.3137 5 11Z" fill="#152C70"/> 
 </svg>'; ?>
                <input type="search" name="s" placeholder="<?php _e( 'Search', 'cotlas-travel' ); ?>" value="<?php echo esc_attr( $s ); ?>">
                <!-- Preserve other filters if present -->
                <?php 
                $params = array( 'destination', 'activities', 'trip_types', 'difficulty', 'min_price', 'max_price', 'min_days', 'max_days', 'orderby' );
                foreach ( $params as $param ) {
                    if ( isset( $_GET[ $param ] ) ) {
                        if ( is_array( $_GET[ $param ] ) ) {
                            foreach ( $_GET[ $param ] as $val ) {
                                echo '<input type="hidden" name="' . esc_attr( $param ) . '[]" value="' . esc_attr( $val ) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . esc_attr( $param ) . '" value="' . esc_attr( $_GET[ $param ] ) . '">';
                        }
                    }
                }
                ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Sort Dropdown Shortcode
     */
    public function sort_dropdown_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'class' => '' ), $atts );
        
        $current_order = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'date-desc'; // Default
        
        $options = array(
            'date-desc'       => __( 'Recently Added', 'cotlas-travel' ),
            'price-asc'       => __( 'Lowest Price First', 'cotlas-travel' ),
            'price-desc'      => __( 'Highest Price First', 'cotlas-travel' ),
            'duration-asc'    => __( 'Shortest Duration First', 'cotlas-travel' ),
            'duration-desc'   => __( 'Longest Duration First', 'cotlas-travel' ),
            'title-asc'       => __( 'Alphabetical - A to Z', 'cotlas-travel' ),
            'title-desc'      => __( 'Alphabetical - Z to A', 'cotlas-travel' ),
        );

        ob_start();
        ?>
        <div class="ctd-sort-dropdown <?php echo esc_attr( $atts['class'] ); ?>">
            <form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'trip' ) ); ?>" class="ctd-sort-form">
                <select name="orderby" class="ctd-select2" onchange="this.form.submit()">
                    <option value=""><?php _e( 'Sort (Default)', 'cotlas-travel' ); ?></option>
                    <?php foreach ( $options as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_order, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                 <!-- Preserve other filters -->
                 <?php 
                $params = array( 's', 'destination', 'activities', 'trip_types', 'difficulty', 'min_price', 'max_price', 'min_days', 'max_days' );
                foreach ( $params as $param ) {
                    if ( isset( $_GET[ $param ] ) ) {
                        if ( is_array( $_GET[ $param ] ) ) {
                            foreach ( $_GET[ $param ] as $v ) {
                                echo '<input type="hidden" name="' . esc_attr( $param ) . '[]" value="' . esc_attr( $v ) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . esc_attr( $param ) . '" value="' . esc_attr( $_GET[ $param ] ) . '">';
                        }
                    }
                }
                ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Group By Taxonomies for Search
     */
    public function groupby_taxonomies_for_search( $groupby, $query ) {
        global $wpdb;

        if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
            return $groupby;
        }

        if ( $query->get( 'post_type' ) !== 'trip' && ! $query->is_post_type_archive('trip') ) {
            return $groupby;
        }

        if ( empty( $groupby ) ) {
            $groupby = "{$wpdb->posts}.ID";
        }
        
        return $groupby;
    }

    /**
     * Join Taxonomies for Search
     */
    public function join_taxonomies_for_search( $join, $query ) {
        global $wpdb;

        if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
            return $join;
        }

        if ( $query->get( 'post_type' ) !== 'trip' && ! $query->is_post_type_archive('trip') ) {
             return $join;
        }

        $join .= " LEFT JOIN {$wpdb->term_relationships} AS ctd_tr ON ({$wpdb->posts}.ID = ctd_tr.object_id)";
        $join .= " LEFT JOIN {$wpdb->term_taxonomy} AS ctd_tt ON (ctd_tr.term_taxonomy_id = ctd_tt.term_taxonomy_id)";
        $join .= " LEFT JOIN {$wpdb->terms} AS ctd_t ON (ctd_tt.term_id = ctd_t.term_id)";

        return $join;
    }

    /**
     * Extend Search to Taxonomies
     */
    public function extend_search_to_taxonomies( $search, $query ) {
        global $wpdb;

        if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
            return $search;
        }

        if ( $query->get( 'post_type' ) !== 'trip' && ! $query->is_post_type_archive('trip') ) {
             return $search;
        }

        $search_term = $query->get( 's' );
        if ( empty( $search_term ) ) {
            return $search;
        }

        $n = ! empty( $query->query_vars['exact'] ) ? '' : '%';
        $searchand = '';
        $search = '';
        
        if ( ! empty( $search_term ) ) {
            $search = " AND (";
            $search .= "({$wpdb->posts}.post_title LIKE '{$n}" . esc_sql( $search_term ) . "{$n}')";
            $search .= " OR ({$wpdb->posts}.post_content LIKE '{$n}" . esc_sql( $search_term ) . "{$n}')";
            $search .= " OR (ctd_t.name LIKE '{$n}" . esc_sql( $search_term ) . "{$n}')";
            $search .= ")";
        }

        return $search;
    }

    /**
     * Modify Main Query
     */
    public function modify_trip_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Check if we are on the relevant archive pages
        $is_trip_archive = $query->is_post_type_archive( 'trip' );
        $is_trip_tax     = $query->is_tax( array( 'destination', 'activities', 'trip_types', 'difficulty' ) );
        $is_trip_search  = $query->is_search() && $query->get( 'post_type' ) === 'trip';

        if ( ! $is_trip_archive && ! $is_trip_tax && ! $is_trip_search ) {
            return;
        }

        // Tax Query
        $tax_query = array();
        $taxonomies = array( 'destination', 'activities', 'trip_types', 'difficulty' );
        
        foreach ( $taxonomies as $tax ) {
            if ( ! empty( $_GET[ $tax ] ) ) {
                $terms = is_array( $_GET[ $tax ] ) ? $_GET[ $tax ] : array( $_GET[ $tax ] );
                $tax_query[] = array(
                    'taxonomy' => $tax,
                    'field'    => 'slug',
                    'terms'    => $terms,
                );
            }
        }

        if ( ! empty( $tax_query ) ) {
            $tax_query['relation'] = 'AND';
            $query->set( 'tax_query', $tax_query );
        }

        // Meta Query (Price & Duration)
        $meta_query = array();

        // Price
        if ( isset( $_GET['min_price'] ) && isset( $_GET['max_price'] ) ) {
            $meta_query[] = array(
                'key'     => 'ctd_min_price',
                'value'   => array( intval( $_GET['min_price'] ), intval( $_GET['max_price'] ) ),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }

        // Duration
        if ( isset( $_GET['min_days'] ) && isset( $_GET['max_days'] ) ) {
            $meta_query[] = array(
                'key'     => 'ctd_duration_days',
                'value'   => array( intval( $_GET['min_days'] ), intval( $_GET['max_days'] ) ),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }

        if ( ! empty( $meta_query ) ) {
            $meta_query['relation'] = 'AND';
            $query->set( 'meta_query', $meta_query );
        }

        // Ordering
        if ( isset( $_GET['orderby'] ) ) {
            $order_param = $_GET['orderby'];
            switch ( $order_param ) {
                case 'price-asc':
                    $query->set( 'meta_key', 'ctd_min_price' );
                    $query->set( 'orderby', 'meta_value_num' );
                    $query->set( 'order', 'ASC' );
                    break;
                case 'price-desc':
                    $query->set( 'meta_key', 'ctd_min_price' );
                    $query->set( 'orderby', 'meta_value_num' );
                    $query->set( 'order', 'DESC' );
                    break;
                case 'duration-asc':
                    $query->set( 'meta_key', 'ctd_duration_days' );
                    $query->set( 'orderby', 'meta_value_num' );
                    $query->set( 'order', 'ASC' );
                    break;
                case 'duration-desc':
                    $query->set( 'meta_key', 'ctd_duration_days' );
                    $query->set( 'orderby', 'meta_value_num' );
                    $query->set( 'order', 'DESC' );
                    break;
                case 'title-asc':
                    $query->set( 'orderby', 'title' );
                    $query->set( 'order', 'ASC' );
                    break;
                case 'title-desc':
                    $query->set( 'orderby', 'title' );
                    $query->set( 'order', 'DESC' );
                    break;
                case 'date-desc':
                default:
                    $query->set( 'orderby', 'date' );
                    $query->set( 'order', 'DESC' );
                    break;
            }
        }
    }
}

new CTD_Filters();
