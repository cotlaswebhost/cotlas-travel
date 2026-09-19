<?php
/**
 * Trip search behaviour.
 *
 * @package CotlasTravel
 */

defined( 'ABSPATH' ) || exit;

/**
 * GeneratePress Element ID holding the "Trip Search Results" layout.
 *
 * The Element is content that lives in the database, so its ID differs per
 * site. Override it with the cotlas_travel_search_element_id filter (or define
 * COTLAS_TRAVEL_SEARCH_ELEMENT_ID in wp-config.php) when installing elsewhere.
 *
 * @return int
 */
function ctd_trip_search_element_id() {
	$element_id = defined( 'COTLAS_TRAVEL_SEARCH_ELEMENT_ID' ) ? COTLAS_TRAVEL_SEARCH_ELEMENT_ID : 14541;

	return (int) apply_filters( 'cotlas_travel_search_element_id', $element_id );
}

/**
 * Restrict the "Trip Search Results" Element to trip searches only.
 *
 * Without this the trip layout takes over every site search, including regular
 * blog searches. Non-trip searches fall back to the normal search template.
 *
 * @param bool $display    Whether the element should display.
 * @param int  $element_id Element ID being rendered.
 * @return bool
 */
function ctd_restrict_trip_search_element( $display, $element_id ) {
	if ( ctd_trip_search_element_id() !== (int) $element_id ) {
		return $display;
	}

	if ( is_search() && 'trip' !== get_query_var( 'post_type' ) ) {
		return false;
	}

	return $display;
}
add_filter( 'generate_element_display', 'ctd_restrict_trip_search_element', 10, 2 );
