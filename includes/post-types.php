<?php
/**
 * Register Custom Post Types.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Trip Post Type.
 */
function ctd_register_trip_cpt() {
	$labels = array(
		'name'                  => _x( 'Trips', 'Post Type General Name', 'cotlas-travel' ),
		'singular_name'         => _x( 'Trip', 'Post Type Singular Name', 'cotlas-travel' ),
		'menu_name'             => __( 'Trip Desk', 'cotlas-travel' ),
		'name_admin_bar'        => __( 'Trip', 'cotlas-travel' ),
		'archives'              => __( 'Trip Archives', 'cotlas-travel' ),
		'attributes'            => __( 'Trip Attributes', 'cotlas-travel' ),
		'parent_item_colon'     => __( 'Parent Trip:', 'cotlas-travel' ),
		'all_items'             => __( 'All Trips', 'cotlas-travel' ),
		'add_new_item'          => __( 'Add New Trip', 'cotlas-travel' ),
		'add_new'               => __( 'Add New', 'cotlas-travel' ),
		'new_item'              => __( 'New Trip', 'cotlas-travel' ),
		'edit_item'             => __( 'Edit Trip', 'cotlas-travel' ),
		'update_item'           => __( 'Update Trip', 'cotlas-travel' ),
		'view_item'             => __( 'View Trip', 'cotlas-travel' ),
		'view_items'            => __( 'View Trips', 'cotlas-travel' ),
		'search_items'          => __( 'Search Trip', 'cotlas-travel' ),
		'not_found'             => __( 'Not found', 'cotlas-travel' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'cotlas-travel' ),
		'featured_image'        => __( 'Featured Image', 'cotlas-travel' ),
		'set_featured_image'    => __( 'Set featured image', 'cotlas-travel' ),
		'remove_featured_image' => __( 'Remove featured image', 'cotlas-travel' ),
		'use_featured_image'    => __( 'Use as featured image', 'cotlas-travel' ),
		'insert_into_item'      => __( 'Insert into trip', 'cotlas-travel' ),
		'uploaded_to_this_item' => __( 'Uploaded to this trip', 'cotlas-travel' ),
		'items_list'            => __( 'Trips list', 'cotlas-travel' ),
		'items_list_navigation' => __( 'Trips list navigation', 'cotlas-travel' ),
		'filter_items_list'     => __( 'Filter trips list', 'cotlas-travel' ),
	);
	$args = array(
		'label'                 => __( 'Trip', 'cotlas-travel' ),
		'description'           => __( 'Trip Description', 'cotlas-travel' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail' ), // Title, Content (Trip Details), Featured Image
		'taxonomies'            => array( 'destination', 'activities', 'trip_types', 'difficulty', 'trip_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-airplane', // Suitable icon for travel
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true, // Enable Gutenberg editor
	);
	register_post_type( 'trip', $args );
}
add_action( 'init', 'ctd_register_trip_cpt' );

/**
 * Add Trip Duration Column to Trip List.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function ctd_add_trip_columns( $columns ) {
	$new_columns = array();
	
	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;
		// Insert Trip Duration after Title
		if ( 'title' === $key ) {
			$new_columns['ctd_trip_duration'] = __( 'Trip Duration', 'cotlas-travel' );
		}
	}
	
	return $new_columns;
}
add_filter( 'manage_trip_posts_columns', 'ctd_add_trip_columns' );

/**
 * Display Trip Duration in Trip List Column.
 *
 * @param string $column_name Column name.
 * @param int    $post_id Post ID.
 */
function ctd_manage_trip_custom_column( $column_name, $post_id ) {
	if ( 'ctd_trip_duration' === $column_name ) {
		$days   = get_post_meta( $post_id, 'ctd_duration_days', true );
		$nights = get_post_meta( $post_id, 'ctd_duration_nights', true );
		$unit   = get_post_meta( $post_id, 'ctd_duration_unit', true );
		
		$output = '';
		if ( $days ) {
			$output .= $days . ' ' . ucfirst( $unit ? $unit : 'days' );
		}
		
		if ( $nights ) {
			if ( $output ) {
				$output .= ' - ';
			}
			$output .= $nights . ' ' . __( 'Nights', 'cotlas-travel' );
		}
		
		echo $output ? esc_html( $output ) : '<span aria-hidden="true">—</span>';
	}
}
add_action( 'manage_trip_posts_custom_column', 'ctd_manage_trip_custom_column', 10, 2 );

// Enable block editor for 'trip' post type
add_filter('use_block_editor_for_post_type', 'ctd_enable_block_editor', 10, 2);
function ctd_enable_block_editor($use_block_editor, $post_type) {
    if ($post_type === 'trip') {
        return true;
    }
    return $use_block_editor;
}