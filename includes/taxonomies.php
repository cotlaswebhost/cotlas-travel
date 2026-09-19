<?php
/**
 * Register Taxonomies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Taxonomies.
 */
function ctd_register_taxonomies() {
	// Pricing Category Taxonomy
	$labels_pricing_category = array(
		'name'                       => _x( 'Pricing Categories', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Pricing Category', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Pricing Categories', 'cotlas-travel' ),
		'all_items'                  => __( 'All Pricing Categories', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Pricing Category', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Pricing Category:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Pricing Category Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Pricing Category', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Pricing Category', 'cotlas-travel' ),
		'update_item'                => __( 'Update Pricing Category', 'cotlas-travel' ),
		'view_item'                  => __( 'View Pricing Category', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate pricing categories with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove pricing categories', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Pricing Categories', 'cotlas-travel' ),
		'search_items'               => __( 'Search Pricing Categories', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No pricing categories', 'cotlas-travel' ),
		'items_list'                 => __( 'Pricing Categories list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Pricing Categories list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Pricing Categories', 'cotlas-travel' ),
	);
	$args_pricing_category = array(
		'labels'                     => $labels_pricing_category,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => false, // Hide from Gutenberg sidebar
		'meta_box_cb'                => false, // Disable default metabox
	);
	register_taxonomy( 'pricing_category', array( 'trip' ), $args_pricing_category );

	// Destination Taxonomy
	$labels_destination = array(
		'name'                       => _x( 'Destinations', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Destination', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Destinations', 'cotlas-travel' ),
		'all_items'                  => __( 'All Destinations', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Destination', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Destination:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Destination Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Destination', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Destination', 'cotlas-travel' ),
		'update_item'                => __( 'Update Destination', 'cotlas-travel' ),
		'view_item'                  => __( 'View Destination', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate destinations with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove destinations', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Destinations', 'cotlas-travel' ),
		'search_items'               => __( 'Search Destinations', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No destinations', 'cotlas-travel' ),
		'items_list'                 => __( 'Destinations list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Destinations list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Destinations', 'cotlas-travel' ),
	);
	$args_destination = array(
		'labels'                     => $labels_destination,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'destination', array( 'trip' ), $args_destination );

	// Activities Taxonomy
	$labels_activities = array(
		'name'                       => _x( 'Activities', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Activity', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Activities', 'cotlas-travel' ),
		'all_items'                  => __( 'All Activities', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Activity', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Activity:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Activity Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Activity', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Activity', 'cotlas-travel' ),
		'update_item'                => __( 'Update Activity', 'cotlas-travel' ),
		'view_item'                  => __( 'View Activity', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate activities with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove activities', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Activities', 'cotlas-travel' ),
		'search_items'               => __( 'Search Activities', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No activities', 'cotlas-travel' ),
		'items_list'                 => __( 'Activities list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Activities list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Activities', 'cotlas-travel' ),
	);
	$args_activities = array(
		'labels'                     => $labels_activities,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
		'rewrite'                    => array( 'slug' => 'trip-activity' ),
	);
	register_taxonomy( 'activities', array( 'trip' ), $args_activities );

	// Trip Types Taxonomy
	$labels_trip_types = array(
		'name'                       => _x( 'Trip Types', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Trip Type', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Trip Types', 'cotlas-travel' ),
		'all_items'                  => __( 'All Trip Types', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Trip Type', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Trip Type:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Trip Type Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Trip Type', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Trip Type', 'cotlas-travel' ),
		'update_item'                => __( 'Update Trip Type', 'cotlas-travel' ),
		'view_item'                  => __( 'View Trip Type', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate trip types with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove trip types', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Trip Types', 'cotlas-travel' ),
		'search_items'               => __( 'Search Trip Types', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No trip types', 'cotlas-travel' ),
		'items_list'                 => __( 'Trip Types list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Trip Types list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Trip Types', 'cotlas-travel' ),
	);
	$args_trip_types = array(
		'labels'                     => $labels_trip_types,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
		'rewrite'                    => array( 'slug' => 'trip-type' ),
	);
	register_taxonomy( 'trip_types', array( 'trip' ), $args_trip_types );

	// Difficulty Taxonomy
	$labels_difficulty = array(
		'name'                       => _x( 'Difficulty', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Difficulty', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Difficulty', 'cotlas-travel' ),
		'all_items'                  => __( 'All Difficulty Levels', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Difficulty', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Difficulty:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Difficulty Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Difficulty', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Difficulty', 'cotlas-travel' ),
		'update_item'                => __( 'Update Difficulty', 'cotlas-travel' ),
		'view_item'                  => __( 'View Difficulty', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate difficulty with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove difficulty', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Difficulty', 'cotlas-travel' ),
		'search_items'               => __( 'Search Difficulty', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No difficulty', 'cotlas-travel' ),
		'items_list'                 => __( 'Difficulty list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Difficulty list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Difficulty', 'cotlas-travel' ),
	);
	$args_difficulty = array(
		'labels'                     => $labels_difficulty,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'difficulty', array( 'trip' ), $args_difficulty );

	// Trip Tag Taxonomy
	$labels_trip_tag = array(
		'name'                       => _x( 'Trip Tags', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Trip Tag', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Trip Tags', 'cotlas-travel' ),
		'all_items'                  => __( 'All Trip Tags', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Trip Tag', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Trip Tag:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Trip Tag Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Trip Tag', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Trip Tag', 'cotlas-travel' ),
		'update_item'                => __( 'Update Trip Tag', 'cotlas-travel' ),
		'view_item'                  => __( 'View Trip Tag', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate trip tags with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove trip tags', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Trip Tags', 'cotlas-travel' ),
		'search_items'               => __( 'Search Trip Tags', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No trip tags', 'cotlas-travel' ),
		'items_list'                 => __( 'Trip Tags list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Trip Tags list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Trip Tags', 'cotlas-travel' ),
	);
	$args_trip_tag = array(
		'labels'                     => $labels_trip_tag,
		'hierarchical'               => false, // Tags are usually non-hierarchical
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => false, // Hide from Gutenberg sidebar
		'meta_box_cb'                => false, // Disable default metabox
	);
	register_taxonomy( 'trip_tag', array( 'trip' ), $args_trip_tag );

	// Trip Fact Taxonomy
	$labels_trip_fact = array(
		'name'                       => _x( 'Trip Facts', 'Taxonomy General Name', 'cotlas-travel' ),
		'singular_name'              => _x( 'Trip Fact', 'Taxonomy Singular Name', 'cotlas-travel' ),
		'menu_name'                  => __( 'Trip Facts', 'cotlas-travel' ),
		'all_items'                  => __( 'All Trip Facts', 'cotlas-travel' ),
		'parent_item'                => __( 'Parent Trip Fact', 'cotlas-travel' ),
		'parent_item_colon'          => __( 'Parent Trip Fact:', 'cotlas-travel' ),
		'new_item_name'              => __( 'New Trip Fact Name', 'cotlas-travel' ),
		'add_new_item'               => __( 'Add New Trip Fact', 'cotlas-travel' ),
		'edit_item'                  => __( 'Edit Trip Fact', 'cotlas-travel' ),
		'update_item'                => __( 'Update Trip Fact', 'cotlas-travel' ),
		'view_item'                  => __( 'View Trip Fact', 'cotlas-travel' ),
		'separate_items_with_commas' => __( 'Separate trip facts with commas', 'cotlas-travel' ),
		'add_or_remove_items'        => __( 'Add or remove trip facts', 'cotlas-travel' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'cotlas-travel' ),
		'popular_items'              => __( 'Popular Trip Facts', 'cotlas-travel' ),
		'search_items'               => __( 'Search Trip Facts', 'cotlas-travel' ),
		'not_found'                  => __( 'Not Found', 'cotlas-travel' ),
		'no_terms'                   => __( 'No trip facts', 'cotlas-travel' ),
		'items_list'                 => __( 'Trip Facts list', 'cotlas-travel' ),
		'items_list_navigation'      => __( 'Trip Facts list navigation', 'cotlas-travel' ),
		'back_to_items'              => __( 'Go to Trip Facts', 'cotlas-travel' ),
	);
	$args_trip_fact = array(
		'labels'                     => $labels_trip_fact,
		'hierarchical'               => false, 
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
		'show_in_rest'               => false, // Hide from Gutenberg sidebar
		'meta_box_cb'                => false, // Disable default metabox
	);
	register_taxonomy( 'trip_fact', array( 'trip' ), $args_trip_fact );
}
add_action( 'init', 'ctd_register_taxonomies' );
