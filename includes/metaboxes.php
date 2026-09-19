<?php
/**
 * Trip Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes.
 */
function ctd_register_meta_boxes() {
	add_meta_box(
		'ctd_trip_settings',
		__( 'Trip Settings', 'cotlas-travel' ),
		'ctd_render_trip_settings_meta_box',
		'trip',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ctd_register_meta_boxes' );

/**
 * Keep the Trip Settings meta box anchored in the main column.
 *
 * WordPress saves the column a user last dragged a meta box to in the
 * "meta-box-order_{$post_type}" user option, then re-registers the box in that
 * context on every screen load. That is how Trip Settings ends up stuck in the
 * sidebar after an accidental click on the move up/down arrow. Discard the
 * saved location for this box so it is always rendered below the editor.
 *
 * @param mixed $order Stored meta box order for the trip screen.
 * @return mixed Filtered meta box order.
 */
function ctd_force_trip_settings_metabox_location( $order ) {
	if ( ! is_array( $order ) ) {
		return $order;
	}

	$box_id = 'ctd_trip_settings';

	foreach ( $order as $context => $ids ) {
		if ( ! is_string( $ids ) ) {
			continue;
		}

		$ids = array_filter( array_map( 'trim', explode( ',', $ids ) ), 'strlen' );
		$order[ $context ] = implode( ',', array_diff( $ids, array( $box_id ) ) );
	}

	$existing = isset( $order['normal'] ) ? $order['normal'] : '';
	$order['normal'] = '' === $existing ? $box_id : $box_id . ',' . $existing;

	return $order;
}
add_filter( 'get_user_option_meta-box-order_trip', 'ctd_force_trip_settings_metabox_location' );

/**
 * Render Trip Settings Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function ctd_render_trip_settings_meta_box( $post ) {
	// Add nonce for security and authentication.
	wp_nonce_field( 'ctd_save_trip_settings', 'ctd_trip_settings_nonce' );

    // Fetch Price Categories
    $price_categories = get_terms( array(
        'taxonomy'   => 'pricing_category',
        'hide_empty' => false,
    ) );

	// Retrieve existing values from the database.
	$trip_code       = get_post_meta( $post->ID, 'ctd_trip_code', true );
	$duration_days   = get_post_meta( $post->ID, 'ctd_duration_days', true );
	$duration_unit   = get_post_meta( $post->ID, 'ctd_duration_unit', true );
	$duration_nights = get_post_meta( $post->ID, 'ctd_duration_nights', true );
	$enable_age      = get_post_meta( $post->ID, 'ctd_enable_age_limit', true );
	$min_age         = get_post_meta( $post->ID, 'ctd_min_age', true );
	$max_age         = get_post_meta( $post->ID, 'ctd_max_age', true );
	$min_travellers  = get_post_meta( $post->ID, 'ctd_min_travellers', true );
	$total_seats     = get_post_meta( $post->ID, 'ctd_total_seats', true );
	$trip_tags       = get_the_terms( $post->ID, 'trip_tag' );
	$selected_tag    = ! empty( $trip_tags ) && ! is_wp_error( $trip_tags ) ? $trip_tags[0]->term_id : '';
    
    // Pricing
    $pricing_packages = get_post_meta( $post->ID, 'ctd_pricing_packages', true );
    
    // Overview
    $overview_title       = get_post_meta( $post->ID, 'ctd_overview_title', true );
    $overview_content     = get_post_meta( $post->ID, 'ctd_overview_content', true );
    $highlights_title     = get_post_meta( $post->ID, 'ctd_highlights_title', true );
    $highlights           = get_post_meta( $post->ID, 'ctd_highlights', true );

    // Itinerary
    $itinerary_title      = get_post_meta( $post->ID, 'ctd_itinerary_title', true );
    $itinerary_desc       = get_post_meta( $post->ID, 'ctd_itinerary_description', true );
    $itineraries          = get_post_meta( $post->ID, 'ctd_itineraries', true );
    $itinerary_pdf_enable = get_post_meta( $post->ID, 'ctd_enable_itinerary_pdf', true );

    // Includes/Excludes
    $inc_exc_title        = get_post_meta( $post->ID, 'ctd_inc_exc_title', true );
    $cost_includes_title  = get_post_meta( $post->ID, 'ctd_cost_includes_title', true );
    $cost_includes        = get_post_meta( $post->ID, 'ctd_cost_includes', true );
    $cost_excludes_title  = get_post_meta( $post->ID, 'ctd_cost_excludes_title', true );
    $cost_excludes        = get_post_meta( $post->ID, 'ctd_cost_excludes', true );

    // Trip Info
    $trip_info_title      = get_post_meta( $post->ID, 'ctd_trip_info_title', true );
    $trip_facts           = get_post_meta( $post->ID, 'ctd_trip_facts', true );

    // Gallery
    $gallery_enable       = get_post_meta( $post->ID, 'ctd_gallery_enable', true );
    $gallery_images       = get_post_meta( $post->ID, 'ctd_gallery_images', true );
    $video_gallery_enable = get_post_meta( $post->ID, 'ctd_video_gallery_enable', true );
    $video_gallery        = get_post_meta( $post->ID, 'ctd_video_gallery', true );

    // Map
    $map_title            = get_post_meta( $post->ID, 'ctd_map_title', true );
    $map_image            = get_post_meta( $post->ID, 'ctd_map_image', true );
    $map_iframe           = get_post_meta( $post->ID, 'ctd_map_iframe', true );

    // Extra Services
    $extra_services_title = get_post_meta( $post->ID, 'ctd_extra_services_title', true );
    $extra_services_desc  = get_post_meta( $post->ID, 'ctd_extra_services_desc', true );
    $extra_services       = get_post_meta( $post->ID, 'ctd_extra_services', true );
    $extra_services_enable= get_post_meta( $post->ID, 'ctd_enable_extra_services', true );

    // FAQs
    $faqs                 = get_post_meta( $post->ID, 'ctd_faqs', true );

    // File Downloads
    $downloads            = get_post_meta( $post->ID, 'ctd_downloads', true );
    $downloads_title      = get_post_meta( $post->ID, 'ctd_downloads_title', true );
    $downloads_enable     = get_post_meta( $post->ID, 'ctd_enable_downloads', true );
    
    // More Info
    $more_info_title      = get_post_meta( $post->ID, 'ctd_more_info_title', true );
    $more_info_content    = get_post_meta( $post->ID, 'ctd_more_info_content', true );
    $more_info_enable     = get_post_meta( $post->ID, 'ctd_enable_more_info', true );

    // Custom Booking
    $custom_booking_link  = get_post_meta( $post->ID, 'ctd_custom_booking_link', true );
    $booking_enable       = get_post_meta( $post->ID, 'ctd_enable_booking', true );

    // Meals
    $meals                = get_post_meta( $post->ID, 'ctd_meals', true );
    $meals_title          = get_post_meta( $post->ID, 'ctd_meals_title', true );
    $meals_enable         = get_post_meta( $post->ID, 'ctd_enable_meals', true );

    // Guide Languages
    $guide_languages      = get_post_meta( $post->ID, 'ctd_guide_languages', true );

    // Flights
    $flights              = get_post_meta( $post->ID, 'ctd_flights', true );
    $outbound_flight      = isset( $flights['outbound'] ) ? $flights['outbound'] : array();
    $inbound_flight       = isset( $flights['inbound'] ) ? $flights['inbound'] : array();
    $flights_enable       = get_post_meta( $post->ID, 'ctd_enable_flights', true );

	// Default values
	if ( '' === $min_travellers ) {
		$min_travellers = 1;
	}

	?>
	<div class="ctd-metabox-wrapper">
		<ul class="ctd-metabox-tabs">
			<li class="active" data-tab="general">
				<span class="dashicons dashicons-admin-generic"></span>
				<?php _e( 'General', 'cotlas-travel' ); ?>
			</li>
            <li data-tab="flights">
                <span class="dashicons dashicons-airplane"></span>
                <?php _e( 'Flights', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="pricing">
                <span class="dashicons dashicons-money"></span>
                <?php _e( 'Pricing', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="overview">
                <span class="dashicons dashicons-text-page"></span>
                <?php _e( 'Overview', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="itinerary">
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php _e( 'Itinerary', 'cotlas-travel' ); ?>
            </li>
            <li data-tab="meals">
                <span class="dashicons dashicons-food"></span>
                <?php _e( 'Meals Details', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="inc-exc">
                <span class="dashicons dashicons-yes"></span>
                <?php _e( 'Includes/Excludes', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="trip-info">
                <span class="dashicons dashicons-info"></span>
                <?php _e( 'Trip Info', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="gallery">
                <span class="dashicons dashicons-format-gallery"></span>
                <?php _e( 'Gallery', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="map">
                <span class="dashicons dashicons-location"></span>
                <?php _e( 'Map', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="extra-services">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e( 'Extra Services', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="faqs">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e( 'FAQs', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="downloads">
                <span class="dashicons dashicons-download"></span>
                <?php _e( 'Downloads', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="more-info">
                <span class="dashicons dashicons-info-outline"></span>
                <?php _e( 'More Info', 'cotlas-travel' ); ?>
            </li>
			<li data-tab="booking">
                <span class="dashicons dashicons-tickets-alt"></span>
                <?php _e( 'Booking', 'cotlas-travel' ); ?>
            </li>
		</ul>

		<div class="ctd-metabox-content">
			<!-- General Tab -->
			<div id="ctd-tab-general" class="ctd-tab-pane active">
				<div class="ctd-tab-header">
					<h2 class="ctd-tab-heading"><?php _e( 'General', 'cotlas-travel' ); ?></h2>
				</div>
				
				<div class="ctd-tab-fields">
					<!-- Trip Code -->
					<div class="ctd-form-field">
						<label for="ctd_trip_code"><?php _e( 'Trip Code', 'cotlas-travel' ); ?></label>
						<div class="ctd-input-wrap">
							<input type="text" name="ctd_trip_code" id="ctd_trip_code" value="<?php echo esc_attr( $trip_code ); ?>" class="regular-text" />
						</div>
					</div>

					<!-- Duration -->
					<div class="ctd-form-field">
						<label><?php _e( 'Duration', 'cotlas-travel' ); ?></label>
						<div class="ctd-input-wrap ctd-duration-group">
							<div class="duration-selector">
								<div class="ctd-duration-days">
									<input type="number" name="ctd_duration_days" value="<?php echo esc_attr( $duration_days ); ?>" class="small-text" min="0" />
									<select name="ctd_duration_unit">
										<option value="days" <?php selected( $duration_unit, 'days' ); ?>><?php _e( 'Days', 'cotlas-travel' ); ?></option>
										<option value="hours" <?php selected( $duration_unit, 'hours' ); ?>><?php _e( 'Hours', 'cotlas-travel' ); ?></option>
									</select>
								</div>
								<div class="ctd-duration-nights">
									<input type="number" name="ctd_duration_nights" value="<?php echo esc_attr( $duration_nights ); ?>" class="small-text" min="0" />
									<span class="ctd-suffix"><?php _e( 'Night(s)', 'cotlas-travel' ); ?></span>
								</div>
							</div>
							<p class="description"><?php _e( 'Enter the duration for the trip and choose desired unit.', 'cotlas-travel' ); ?></p>
						</div>
					</div>

					<!-- Set Minimum And Maximum Age -->
					<div class="ctd-form-field">
						<div class="ctd-field-header">
							<label for="ctd_enable_age_limit"><?php _e( 'Set Minimum And Maximum Age', 'cotlas-travel' ); ?></label>
							<label class="ctd-switch">
								<input type="checkbox" name="ctd_enable_age_limit" id="ctd_enable_age_limit" value="1" <?php checked( $enable_age, 1 ); ?> />
								<span class="ctd-slider round"></span>
							</label>
						</div>
						
						<div class="ctd-age-fields" style="<?php echo $enable_age ? '' : 'display:none;'; ?>">
							<label></label>
							<div class="ctd-sub-field">
								<label for="ctd_min_age"><?php _e( 'Minimum Age', 'cotlas-travel' ); ?></label>
								<input type="number" name="ctd_min_age" id="ctd_min_age" value="<?php echo esc_attr( $min_age ); ?>" class="small-text" min="0" />
							</div>
							<div class="ctd-sub-field">
								<label for="ctd_max_age"><?php _e( 'Maximum Age', 'cotlas-travel' ); ?></label>
								<input type="number" name="ctd_max_age" id="ctd_max_age" value="<?php echo esc_attr( $max_age ); ?>" class="small-text" min="0" />
							</div>
						</div>
					</div>

					<!-- Minimum Travellers Per Booking -->
					<div class="ctd-form-field">
						<label for="ctd_min_travellers"><?php _e( 'Minimum Travellers Per Booking', 'cotlas-travel' ); ?></label>
						<div class="ctd-input-wrap">
							<input type="number" name="ctd_min_travellers" id="ctd_min_travellers" value="<?php echo esc_attr( $min_travellers ); ?>" class="small-text" min="-1" />
						</div>
					</div>

					<!-- Total Travellers Seats -->
					<div class="ctd-form-field">
						<label for="ctd_total_seats"><?php _e( 'Total Travellers Seats', 'cotlas-travel' ); ?></label>
						<div class="ctd-input-wrap">
							<input type="number" name="ctd_total_seats" id="ctd_total_seats" value="<?php echo esc_attr( $total_seats ); ?>" class="regular-text" min="-1" />
						</div>
					</div>

					<!-- Trip Tags -->
					<div class="ctd-form-field ctd-trip-wrap">
						<label for="ctd_trip_tags"><?php _e( 'Trip Tag', 'cotlas-travel' ); ?></label>
						<div class="ctd-input-wrap">
							<?php
							$tags = get_terms( array(
								'taxonomy'   => 'trip_tag',
								'hide_empty' => false,
							) );

							if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
								?>
								<select name="ctd_trip_tag" id="ctd_trip_tag" class="regular-text">
									<option value=""><?php _e( 'Select a Trip Tag', 'cotlas-travel' ); ?></option>
									<?php foreach ( $tags as $tag ) : ?>
										<option value="<?php echo esc_attr( $tag->term_id ); ?>" <?php selected( $selected_tag, $tag->term_id ); ?>>
											<?php echo esc_html( $tag->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php _e( 'Select a trip tag.', 'cotlas-travel' ); ?></p>
								<?php
							} else {
								echo '<p class="description">' . __( 'No trip tags found. Please add them from the Trip Tags menu.', 'cotlas-travel' ) . '</p>';
							}
							?>
						</div>
					</div>

                    <!-- Guide Languages -->
                    <div class="ctd-form-field">
                        <label><?php _e( 'Guide Languages', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper" data-repeater="ctd_guide_languages">
                            <?php 
                            if ( ! empty( $guide_languages ) && is_array( $guide_languages ) ) {
                                foreach ( $guide_languages as $index => $language ) {
                                    ?>
                                    <div class="ctd-repeater-item">
                                        <div class="ctd-repeater-item-header">
                                            <div class="ctd-input-wrap" style="width: 100%;">
                                                <input type="text" name="ctd_guide_languages[<?php echo $index; ?>]" value="<?php echo esc_attr( $language ); ?>" placeholder="Language (e.g. English)" class="regular-text" style="width: 100%;" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="margin-left: 10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add Language', 'cotlas-travel' ); ?></button>
                            
                            <!-- Template -->
                            <div class="ctd-repeater-template" style="display:none;">
                                <div class="ctd-repeater-item">
                                    <div class="ctd-repeater-item-header">
                                        <div class="ctd-input-wrap" style="width: 100%;">
                                            <input type="text" name="ctd_guide_languages[{index}]" value="" placeholder="Language (e.g. English)" class="regular-text" style="width: 100%;" />
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="margin-left: 10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

				</div> <!-- .ctd-tab-fields -->
			</div> <!-- .ctd-tab-general -->

            <!-- Flights Tab -->
            <div id="ctd-tab-flights" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Flights', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Enable Flights', 'cotlas-travel' ); ?></label>
                        <label class="ctd-switch">
                            <input type="checkbox" name="ctd_enable_flights" id="ctd_enable_flights" value="1" <?php checked( $flights_enable, 1 ); ?> />
                            <span class="ctd-slider round"></span>
                        </label>
                    </div>
                    <div class="ctd-flights-wrap" style="<?php echo $flights_enable ? '' : 'display:none;'; ?>">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Flights Section Title', 'cotlas-travel' ); ?></label>
                        <input type="text" name="ctd_flights_title" value="<?php echo esc_attr( get_post_meta( $post->ID, 'ctd_flights_title', true ) ); ?>" class="regular-text" />
                    </div>
                    <?php 
                    // Helper to generate time options
                    $ctd_time_options = function($selected, $type = 'hour') {
                        $html = '';
                        $max = ($type == 'hour') ? 23 : 59;
                        for($i=0; $i<=$max; $i++) {
                            $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                            $sel = ($selected == $val) ? 'selected' : '';
                            $html .= "<option value='{$val}' {$sel}>{$val}</option>";
                        }
                        return $html;
                    };
                    ?>

                    <!-- Outbound Flight -->
                    <div class="ctd-flight-section">
                        <div class="ctd-flight-section-title ctd-section-title" style="font-weight:bold; font-size:1.1em; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px; cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
                            <span><?php _e( 'Outbound Flight', 'cotlas-travel' ); ?></span>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        
                        <div class="ctd-flight-section-content" style="display:none;">
                            <div class="ctd-form-field">
                                <label><?php _e( 'Airline', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[outbound][airline]" value="<?php echo esc_attr( isset($outbound_flight['airline'])?$outbound_flight['airline']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'From (Airport/Location)', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[outbound][from]" value="<?php echo esc_attr( isset($outbound_flight['from'])?$outbound_flight['from']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'Departure Time', 'cotlas-travel' ); ?></label>
                                <div class="ctd-time-selector" >
                                    <select name="ctd_flights[outbound][dep_hour]">
                                        <?php echo $ctd_time_options( isset($outbound_flight['dep_hour'])?$outbound_flight['dep_hour']:'', 'hour' ); ?>
                                    </select> : 
                                    <select name="ctd_flights[outbound][dep_min]">
                                        <?php echo $ctd_time_options( isset($outbound_flight['dep_min'])?$outbound_flight['dep_min']:'', 'minute' ); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'To (Airport/Location)', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[outbound][to]" value="<?php echo esc_attr( isset($outbound_flight['to'])?$outbound_flight['to']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'Arrival Time', 'cotlas-travel' ); ?></label>
                                <div class="ctd-time-selector" style="display:flex; align-items:center; gap:5px;">
                                    <select name="ctd_flights[outbound][arr_hour]">
                                        <?php echo $ctd_time_options( isset($outbound_flight['arr_hour'])?$outbound_flight['arr_hour']:'', 'hour' ); ?>
                                    </select> : 
                                    <select name="ctd_flights[outbound][arr_min]">
                                        <?php echo $ctd_time_options( isset($outbound_flight['arr_min'])?$outbound_flight['arr_min']:'', 'minute' ); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inbound Flight -->
                    <div class="ctd-flight-section">
                        <div class="ctd-flight-section-title ctd-section-title" style="font-weight:bold; font-size:1.1em; margin-top:30px; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:5px; cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
                            <span><?php _e( 'Inbound Flight', 'cotlas-travel' ); ?></span>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        
                        <div class="ctd-flight-section-content" style="display:none;">
                            <div class="ctd-form-field">
                                <label><?php _e( 'Airline', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[inbound][airline]" value="<?php echo esc_attr( isset($inbound_flight['airline'])?$inbound_flight['airline']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'From (Airport/Location)', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[inbound][from]" value="<?php echo esc_attr( isset($inbound_flight['from'])?$inbound_flight['from']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'Departure Time', 'cotlas-travel' ); ?></label>
                                <div class="ctd-time-selector" style="display:flex; align-items:center; gap:5px;">
                                    <select name="ctd_flights[inbound][dep_hour]">
                                        <?php echo $ctd_time_options( isset($inbound_flight['dep_hour'])?$inbound_flight['dep_hour']:'', 'hour' ); ?>
                                    </select> : 
                                    <select name="ctd_flights[inbound][dep_min]">
                                        <?php echo $ctd_time_options( isset($inbound_flight['dep_min'])?$inbound_flight['dep_min']:'', 'minute' ); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'To (Airport/Location)', 'cotlas-travel' ); ?></label>
                                <input type="text" name="ctd_flights[inbound][to]" value="<?php echo esc_attr( isset($inbound_flight['to'])?$inbound_flight['to']:'' ); ?>" class="regular-text" />
                            </div>
                            <div class="ctd-form-field">
                                <label><?php _e( 'Arrival Time', 'cotlas-travel' ); ?></label>
                                <div class="ctd-time-selector" style="display:flex; align-items:center; gap:5px;">
                                    <select name="ctd_flights[inbound][arr_hour]">
                                        <?php echo $ctd_time_options( isset($inbound_flight['arr_hour'])?$inbound_flight['arr_hour']:'', 'hour' ); ?>
                                    </select> : 
                                    <select name="ctd_flights[inbound][arr_min]">
                                        <?php echo $ctd_time_options( isset($inbound_flight['arr_min'])?$inbound_flight['arr_min']:'', 'minute' ); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div><!-- .ctd-flights-wrap -->
                </div>
            </div>

            <!-- Pricing Tab -->
            <div id="ctd-tab-pricing" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Pricing', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Packages', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper" data-repeater="ctd_pricing_packages">
                            <?php 
                            if ( ! empty( $pricing_packages ) && is_array( $pricing_packages ) ) {
                                foreach ( $pricing_packages as $index => $package ) {
                                    $enable_dates = isset($package['enable_dates']) ? $package['enable_dates'] : 0;
                                    $dates = isset($package['dates']) ? $package['dates'] : array();
                                    $general_desc = isset($package['general_desc']) ? $package['general_desc'] : '';
                                    ?>
                                    <div class="ctd-repeater-item">
                                        <div class="ctd-repeater-item-header">
                                            <div class="ctd-input-wrap" style="width: 100%;">
                                                <input type="text" name="ctd_pricing_packages[<?php echo $index; ?>][title]" value="<?php echo esc_attr( isset($package['title']) ? $package['title'] : '' ); ?>" placeholder="Package Name (e.g. Everest Base Camp)" class="regular-text" style="width: 100%;" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="margin-left: 10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                        </div>
                                        <div class="ctd-repeater-item-content">
                                            <!-- Nested Tabs Navigation -->
                                            <ul class="ctd-nested-tabs-nav">
                                                <li class="active" data-target="categories"><?php _e('Price Categories', 'cotlas-travel'); ?></li>
                                                <li data-target="dates"><?php _e('Dates', 'cotlas-travel'); ?></li>
                                                <li data-target="general"><?php _e('General', 'cotlas-travel'); ?></li>
                                            </ul>

                                            <!-- Nested Tab: Price Categories -->
                                            <div class="ctd-nested-tab-pane active" data-tab="categories">
                                                <?php
                                                if ( ! empty( $price_categories ) && ! is_wp_error( $price_categories ) ) {
                                                    foreach ( $price_categories as $category ) {
                                                        $cat_id = $category->term_id;
                                                        $cat_data = isset( $package['prices'][$cat_id] ) ? $package['prices'][$cat_id] : array();
                                                        $regular_price = isset( $cat_data['regular_price'] ) ? $cat_data['regular_price'] : '';
                                                        $pricing_type = isset( $cat_data['pricing_type'] ) ? $cat_data['pricing_type'] : 'person';
                                                        $min_pax = isset( $cat_data['min_pax'] ) ? $cat_data['min_pax'] : '';
                                                        $enabled = isset( $cat_data['enabled'] ) ? intval( $cat_data['enabled'] ) : 0;
                                                        $is_active = false;
                                                        ?>
                                                        <div class="ctd-pricing-category-box <?php echo $is_active ? 'active' : ''; ?>">
                                                            <div class="ctd-pricing-cat-header" style="display:flex; align-items:center; justify-content:space-between;">
                                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                                <strong><?php echo esc_html( $category->name ); ?></strong>
                                                            </div>
                                                            <div class="ctd-pricing-cat-content">
                                                                <div class="ctd-form-row">
                                                                    <div class="ctd-col">
                                                                        <label><?php _e( 'Enable Pricing', 'cotlas-travel' ); ?></label>
                                                                        <label class="ctd-switch">
                                                                            <input type="checkbox" class="ctd-pricing-enable" name="ctd_pricing_packages[<?php echo $index; ?>][prices][<?php echo $cat_id; ?>][enabled]" value="1" <?php checked( $enabled, 1 ); ?> />
                                                                            <span class="ctd-slider round"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="ctd-pricing-fields" style="<?php echo $enabled ? '' : 'display:none;'; ?>">
                                                                    <div class="ctd-form-row">
                                                                        <div class="ctd-col">
                                                                            <label><?php _e( 'Regular Price', 'cotlas-travel' ); ?></label>
                                                                            <div class="ctd-input-group">
                                                                                <span class="ctd-input-prefix">INR</span>
                                                                                <input type="number" name="ctd_pricing_packages[<?php echo $index; ?>][prices][<?php echo $cat_id; ?>][regular_price]" value="<?php echo esc_attr( $regular_price ); ?>" step="0.01" />
                                                                            </div>
                                                                        </div>
                                                                        <div class="ctd-col">
                                                                            <label><?php _e( 'Pricing Type', 'cotlas-travel' ); ?></label>
                                                                            <select name="ctd_pricing_packages[<?php echo $index; ?>][prices][<?php echo $cat_id; ?>][pricing_type]">
                                                                                <option value="person" <?php selected( $pricing_type, 'person' ); ?>><?php _e( 'Per Person', 'cotlas-travel' ); ?></option>
                                                                                <option value="group" <?php selected( $pricing_type, 'group' ); ?>><?php _e( 'Per Group', 'cotlas-travel' ); ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="ctd-form-row">
                                                                        <div class="ctd-col">
                                                                            <label><?php _e( 'Min Pax Per Booking', 'cotlas-travel' ); ?></label>
                                                                            <input type="number" name="ctd_pricing_packages[<?php echo $index; ?>][prices][<?php echo $cat_id; ?>][min_pax]" value="<?php echo esc_attr( $min_pax ); ?>" min="0" />
                                                                            <p class="description"><?php _e( 'Set the minimum number of travelers required.', 'cotlas-travel' ); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    echo '<p>' . __( 'No pricing categories found. Please add them in the Taxonomy.', 'cotlas-travel' ) . '</p>';
                                                }
                                                ?>
                                            </div>

                                            <!-- Nested Tab: Dates -->
                                            <div class="ctd-nested-tab-pane" data-tab="dates">
                                                <div class="ctd-form-row">
                                                    <label for="ctd_pricing_enable_dates_<?php echo $index; ?>"><?php _e('Enable Fixed Dates', 'cotlas-travel'); ?></label>
                                                    <label class="ctd-switch">
                                                        <input type="checkbox" name="ctd_pricing_packages[<?php echo $index; ?>][enable_dates]" id="ctd_pricing_enable_dates_<?php echo $index; ?>" value="1" <?php checked($enable_dates, 1); ?>>
                                                        <span class="ctd-slider round"></span>
                                                    </label>
                                                </div>
                                                <div class="ctd-dates-wrapper" style="<?php echo $enable_dates ? '' : 'display:none;'; ?> margin-top:10px;">
                                                    <label><?php _e('Specific Dates', 'cotlas-travel'); ?></label>
                                                    <!-- Simple Date Repeater -->
                                                    <div class="ctd-sub-repeater" data-repeater="dates">
                                                        <?php if(!empty($dates) && is_array($dates)): foreach($dates as $d_idx => $date): ?>
                                                            <div class="ctd-sub-repeater-item">
                                                                <input type="date" name="ctd_pricing_packages[<?php echo $index; ?>][dates][<?php echo $d_idx; ?>]" value="<?php echo esc_attr($date); ?>">
                                                                <button type="button" class="button ctd-remove-sub-repeater">x</button>
                                                            </div>
                                                        <?php endforeach; endif; ?>
                                                        <button type="button" class="button ctd-add-sub-repeater" data-name="ctd_pricing_packages[<?php echo $index; ?>][dates]"><?php _e('Add Date', 'cotlas-travel'); ?></button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Nested Tab: General -->
                                            <div class="ctd-nested-tab-pane" data-tab="general">
                                                <label><?php _e('General Description', 'cotlas-travel'); ?></label>
                                                <textarea name="ctd_pricing_packages[<?php echo $index; ?>][general_desc]" id="ctd_pricing_desc_<?php echo $index; ?>" class="ctd-isolated-block-editor" rows="5" style="width:100%;"><?php echo esc_textarea($general_desc); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add New Package', 'cotlas-travel' ); ?></button>
                            
                            <!-- Template -->
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item">
                                    <div class="ctd-repeater-item-header">
                                        <div class="ctd-input-wrap" style="width: 100%;">
                                            <input type="text" name="ctd_pricing_packages[{index}][title]" placeholder="Package Name (e.g. Everest Base Camp)" class="regular-text" style="width: 100%;" />
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="margin-left: 10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                    </div>
                                    <div class="ctd-repeater-item-content">
                                         <ul class="ctd-nested-tabs-nav">
                                            <li class="active" data-target="categories"><?php _e('Price Categories', 'cotlas-travel'); ?></li>
                                            <li data-target="dates"><?php _e('Dates', 'cotlas-travel'); ?></li>
                                            <li data-target="general"><?php _e('General', 'cotlas-travel'); ?></li>
                                        </ul>
                                        
                                        <div class="ctd-nested-tab-pane active" data-tab="categories">
                                            <?php
                                            if ( ! empty( $price_categories ) && ! is_wp_error( $price_categories ) ) {
                                                foreach ( $price_categories as $category ) {
                                                    $cat_id = $category->term_id;
                                                    ?>
                                                    <div class="ctd-pricing-category-box">
                                                        <div class="ctd-pricing-cat-header" style="display:flex; align-items:center; justify-content:space-between;">
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                            <strong><?php echo esc_html( $category->name ); ?></strong>
                                                        </div>
                                                        <div class="ctd-pricing-cat-content" style="display:none;">
                                                            <div class="ctd-form-row">
                                                                <div class="ctd-col">
                                                                    <label><?php _e( 'Enable Pricing', 'cotlas-travel' ); ?></label>
                                                                    <label class="ctd-switch">
                                                                        <input type="checkbox" class="ctd-pricing-enable" name="ctd_pricing_packages[{index}][prices][<?php echo $cat_id; ?>][enabled]" value="1">
                                                                        <span class="ctd-slider round"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="ctd-pricing-fields" style="display:none;">
                                                                <div class="ctd-form-row">
                                                                    <div class="ctd-col">
                                                                        <label><?php _e( 'Regular Price', 'cotlas-travel' ); ?></label>
                                                                        <div class="ctd-input-group">
                                                                            <span class="ctd-input-prefix">USD</span>
                                                                            <input type="number" name="ctd_pricing_packages[{index}][prices][<?php echo $cat_id; ?>][regular_price]" step="0.01" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="ctd-col">
                                                                        <label><?php _e( 'Pricing Type', 'cotlas-travel' ); ?></label>
                                                                        <select name="ctd_pricing_packages[{index}][prices][<?php echo $cat_id; ?>][pricing_type]">
                                                                            <option value="person"><?php _e( 'Per Person', 'cotlas-travel' ); ?></option>
                                                                            <option value="group"><?php _e( 'Per Group', 'cotlas-travel' ); ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="ctd-form-row">
                                                                    <div class="ctd-col">
                                                                        <label><?php _e( 'Min Pax Per Booking', 'cotlas-travel' ); ?></label>
                                                                        <input type="number" name="ctd_pricing_packages[{index}][prices][<?php echo $cat_id; ?>][min_pax]" min="0" />
                                                                        <p class="description"><?php _e( 'Set the minimum number of travelers required.', 'cotlas-travel' ); ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="ctd-nested-tab-pane" data-tab="dates">
                                            <div class="ctd-form-row">
                                                <label><?php _e('Enable Fixed Dates', 'cotlas-travel'); ?></label>
                                                <label class="ctd-switch">
                                                    <input type="checkbox" name="ctd_pricing_packages[{index}][enable_dates]" value="1">
                                                    <span class="ctd-slider round"></span>
                                                </label>
                                            </div>
                                            <div class="ctd-dates-wrapper" style="display:none; margin-top:10px;">
                                                <label><?php _e('Specific Dates', 'cotlas-travel'); ?></label>
                                                <div class="ctd-sub-repeater" data-repeater="dates">
                                                    <button type="button" class="button ctd-add-sub-repeater" data-name="ctd_pricing_packages[{index}][dates]"><?php _e('Add Date', 'cotlas-travel'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ctd-nested-tab-pane" data-tab="general">
                                            <label><?php _e('General Description', 'cotlas-travel'); ?></label>
                                            <textarea name="ctd_pricing_packages[{index}][general_desc]" id="ctd_pricing_desc_{index}" class="ctd-isolated-block-editor" rows="5" style="width:100%;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                    


                </div>
            </div>

            <!-- Overview Tab -->
            <div id="ctd-tab-overview" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Overview', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_overview_title" value="<?php echo esc_attr( $overview_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'Trip Description', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <textarea name="ctd_overview_content" id="ctd_overview_content" class="ctd-isolated-block-editor" rows="10"><?php echo esc_textarea( $overview_content ); ?></textarea>
                        </div>
                    </div>
                    
                    <hr />
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Highlights Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_highlights_title" value="<?php echo esc_attr( $highlights_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Trip Highlights', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_highlights">
                             <?php 
                            if ( ! empty( $highlights ) && is_array( $highlights ) ) {
                                foreach ( $highlights as $index => $highlight ) {
                                    ?>
                                    <div class="ctd-repeater-item ctd-simple-list-item">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-input-wrap" style="width:100%;">
                                            <input type="text" name="ctd_highlights[<?php echo $index; ?>]" value="<?php echo esc_attr( $highlight ); ?>" placeholder="Enter highlight" />
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add Highlight', 'cotlas-travel' ); ?></button>
                            
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item ctd-simple-list-item">
                                    <div class="ctd-sort-handle">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                    </div>
                                    <div class="ctd-input-wrap" style="width:100%;">
                                        <input type="text" name="ctd_highlights[{index}]" placeholder="Enter highlight" />
                                    </div>
                                    <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itinerary Tab -->
            <div id="ctd-tab-itinerary" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Itinerary', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_itinerary_title" value="<?php echo esc_attr( $itinerary_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Itinerary Description', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <textarea name="ctd_itinerary_description" id="ctd_itinerary_description" class="ctd-isolated-block-editor" rows="5"><?php echo esc_textarea( $itinerary_desc ); ?></textarea>
                        </div>
                    </div>

                    <div class="ctd-form-field">
                        <label><?php _e( 'Itinerary Details', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_itineraries">
                             <?php 
                            if ( ! empty( $itineraries ) && is_array( $itineraries ) ) {
                                foreach ( $itineraries as $index => $itinerary ) {
                                    $title = isset($itinerary['title']) ? $itinerary['title'] : '';
                                    ?>
                                    <div class="ctd-repeater-item ctd-accordion-item">
                                        <div class="ctd-accordion-header">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-accordion-toggle">
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <div class="ctd-accordion-title">
                                                <span class="ctd-day-badge"><?php echo sprintf( __( 'Day %d', 'cotlas-travel' ), $index + 1 ); ?></span>
                                                <span class="ctd-day-title-text"><?php echo esc_html($title); ?></span>
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                        <div class="ctd-accordion-content">
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Day Title', 'cotlas-travel' ); ?></label>
                                                <input type="text" name="ctd_itineraries[<?php echo $index; ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="ctd-itinerary-title-input" />
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Content', 'cotlas-travel' ); ?></label>
                                                <textarea name="ctd_itineraries[<?php echo $index; ?>][content]" id="ctd_itinerary_content_<?php echo $index; ?>" class="ctd-isolated-block-editor" rows="4"><?php echo esc_textarea( isset($itinerary['content']) ? $itinerary['content'] : '' ); ?></textarea>
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Day Image', 'cotlas-travel' ); ?></label>
                                                <div class="ctd-image-upload-wrapper">
                                                    <?php $image_id = isset($itinerary['image_id']) ? $itinerary['image_id'] : ''; ?>
                                                    <input type="hidden" name="ctd_itineraries[<?php echo $index; ?>][image_id]" value="<?php echo esc_attr( $image_id ); ?>" />
                                                    <div class="ctd-image-preview" style="margin-bottom: 10px;">
                                                        <?php if ( $image_id ) : ?>
                                                            <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button type="button" class="button ctd-upload-itinerary-image"><?php _e( 'Upload Image', 'cotlas-travel' ); ?></button>
                                                    <button type="button" class="button ctd-remove-itinerary-image" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add Day', 'cotlas-travel' ); ?></button>
                            
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item ctd-accordion-item">
                                    <div class="ctd-accordion-header">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-accordion-toggle">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </div>
                                        <div class="ctd-accordion-title">
                                            <span class="ctd-day-badge"><?php _e( 'Day', 'cotlas-travel' ); ?> {day_count}</span>
                                            <span class="ctd-day-title-text"></span>
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                    <div class="ctd-accordion-content">
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Day Title', 'cotlas-travel' ); ?></label>
                                            <input type="text" name="ctd_itineraries[{index}][title]" class="ctd-itinerary-title-input" />
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Content', 'cotlas-travel' ); ?></label>
                                            <textarea name="ctd_itineraries[{index}][content]" id="ctd_itinerary_content_{index}" class="ctd-isolated-block-editor" rows="4"></textarea>
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Day Image', 'cotlas-travel' ); ?></label>
                                            <div class="ctd-image-upload-wrapper">
                                                <input type="hidden" name="ctd_itineraries[{index}][image_id]" value="" />
                                                <div class="ctd-image-preview" style="margin-bottom: 10px;"></div>
                                                <button type="button" class="button ctd-upload-itinerary-image"><?php _e( 'Upload Image', 'cotlas-travel' ); ?></button>
                                                <button type="button" class="button ctd-remove-itinerary-image" style="display:none;"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                    
                    <div class="ctd-form-field" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">
                        <h3 style="margin-top: 0; margin-bottom: 15px;"><?php _e( 'Itinerary Downloader', 'cotlas-travel' ); ?></h3>
                        
                         <div class="ctd-field-header" style="margin-bottom: 15px;">
                            <label for="ctd_enable_itinerary_pdf"><?php _e( 'Enable Itinerary PDF', 'cotlas-travel' ); ?></label>
                            <label class="ctd-switch">
                                <input type="checkbox" name="ctd_enable_itinerary_pdf" id="ctd_enable_itinerary_pdf" value="1" <?php checked( $itinerary_pdf_enable, 1 ); ?>>
                                <span class="ctd-slider round"></span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Meals Details Tab -->
            <div id="ctd-tab-meals" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Meals Details', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Enable Meals Details', 'cotlas-travel' ); ?></label>
                        <label class="ctd-switch">
                            <input type="checkbox" name="ctd_enable_meals" id="ctd_enable_meals" value="1" <?php checked( $meals_enable, 1 ); ?> />
                            <span class="ctd-slider round"></span>
                        </label>
                    </div>
                    <div class="ctd-meals-wrap" style="<?php echo $meals_enable ? '' : 'display:none;'; ?>">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_meals_title" value="<?php echo esc_attr( $meals_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'Meals', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_meals">
                             <?php 
                             if ( ! empty( $meals ) && is_array( $meals ) ) {
                                 foreach ( $meals as $index => $meal ) {
                                     ?>
                                     <div class="ctd-repeater-item ctd-simple-list-item">
                                          <div class="ctd-sort-handle">
                                              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                          </div>
                                          <div class="ctd-input-wrap" style="width:100%;">
                                              <input type="text" name="ctd_meals[<?php echo $index; ?>]" value="<?php echo esc_attr($meal); ?>" class="regular-text" style="width:100%;" placeholder="e.g. 4 Breakfasts" />
                                          </div>
                                          <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                     </div>
                                     <?php
                                 }
                             }
                             ?>
                             <button type="button" class="button ctd-repeater-add"><?php _e('Add Meal Detail', 'cotlas-travel'); ?></button>
                             
                             <!-- Template -->
                             <script type="text/template" class="ctd-repeater-template">
                                 <div class="ctd-repeater-item ctd-simple-list-item">
                                      <div class="ctd-sort-handle">
                                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                      </div>
                                      <div class="ctd-input-wrap" style="width:100%;">
                                          <input type="text" name="ctd_meals[{index}]" class="regular-text" style="width:100%;" placeholder="e.g. 4 Breakfasts" />
                                      </div>
                                      <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                 </div>
                             </script>
                        </div>
                    </div>
                    </div><!-- .ctd-meals-wrap -->
                </div>
            </div>

            <!-- Includes/Excludes Tab -->
            <div id="ctd-tab-inc-exc" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Includes/Excludes', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_inc_exc_title" value="<?php echo esc_attr( $inc_exc_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Cost Includes', 'cotlas-travel' ); ?></label>
                        <div class="ctd-accordion-item" style="width:100%;">
                            <div class="ctd-accordion-header">
                                <div class="ctd-accordion-toggle">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </div>
                                <div class="ctd-accordion-title">
                                    <?php _e( 'Cost Includes Content', 'cotlas-travel' ); ?>
                                </div>
                            </div>
                                <div class="ctd-accordion-content">
                                    <div class="ctd-sub-field">
                                        <label><?php _e( 'Cost Includes Title', 'cotlas-travel' ); ?></label>
                                        <input type="text" name="ctd_cost_includes_title" value="<?php echo esc_attr( $cost_includes_title ); ?>" class="regular-text" />
                                    </div>
                                <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_cost_includes" style="margin-top:10px;">
                                    <?php 
                                    if ( ! empty( $cost_includes ) ) {
                                        $inc_list = is_array( $cost_includes ) ? $cost_includes : array_map( 'trim', explode( "\n", wp_strip_all_tags( $cost_includes ) ) );
                                        $inc_list = array_filter( $inc_list );
                                        foreach ( $inc_list as $index => $inc ) {
                                            ?>
                                            <div class="ctd-repeater-item ctd-simple-list-item">
                                                <div class="ctd-sort-handle">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                                </div>
                                                <div class="ctd-input-wrap" style="width:100%;">
                                                    <input type="text" name="ctd_cost_includes[<?php echo $index; ?>]" value="<?php echo esc_attr( $inc ); ?>" class="regular-text" style="width:100%;" placeholder="Include item" />
                                                </div>
                                                <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <button type="button" class="button ctd-repeater-add"><?php _e('Add Include', 'cotlas-travel'); ?></button>
                                    
                                    <!-- Template -->
                                    <script type="text/template" class="ctd-repeater-template">
                                        <div class="ctd-repeater-item ctd-simple-list-item">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-input-wrap" style="width:100%;">
                                                <input type="text" name="ctd_cost_includes[{index}]" class="regular-text" style="width:100%;" placeholder="Include item" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Cost Excludes', 'cotlas-travel' ); ?></label>
                        <div class="ctd-accordion-item" style="width:100%;">
                            <div class="ctd-accordion-header">
                                <div class="ctd-accordion-toggle">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </div>
                                <div class="ctd-accordion-title">
                                    <?php _e( 'Cost Excludes Content', 'cotlas-travel' ); ?>
                                </div>
                            </div>
                                <div class="ctd-accordion-content">
                                    <div class="ctd-sub-field">
                                        <label><?php _e( 'Cost Excludes Title', 'cotlas-travel' ); ?></label>
                                        <input type="text" name="ctd_cost_excludes_title" value="<?php echo esc_attr( $cost_excludes_title ); ?>" class="regular-text" />
                                    </div>
                                <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_cost_excludes" style="margin-top:10px;">
                                    <?php 
                                    if ( ! empty( $cost_excludes ) ) {
                                        $exc_list = is_array( $cost_excludes ) ? $cost_excludes : array_map( 'trim', explode( "\n", wp_strip_all_tags( $cost_excludes ) ) );
                                        $exc_list = array_filter( $exc_list );
                                        foreach ( $exc_list as $index => $exc ) {
                                            ?>
                                            <div class="ctd-repeater-item ctd-simple-list-item">
                                                <div class="ctd-sort-handle">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                                </div>
                                                <div class="ctd-input-wrap" style="width:100%;">
                                                    <input type="text" name="ctd_cost_excludes[<?php echo $index; ?>]" value="<?php echo esc_attr( $exc ); ?>" class="regular-text" style="width:100%;" placeholder="Exclude item" />
                                                </div>
                                                <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <button type="button" class="button ctd-repeater-add"><?php _e('Add Exclude', 'cotlas-travel'); ?></button>
                                    
                                    <!-- Template -->
                                    <script type="text/template" class="ctd-repeater-template">
                                        <div class="ctd-repeater-item ctd-simple-list-item">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-input-wrap" style="width:100%;">
                                                <input type="text" name="ctd_cost_excludes[{index}]" class="regular-text" style="width:100%;" placeholder="Exclude item" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="margin-left:10px; color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trip Info Tab -->
            <div id="ctd-tab-trip-info" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Trip Info', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_trip_info_title" value="<?php echo esc_attr( $trip_info_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Trip Facts', 'cotlas-travel' ); ?></label>
                        <?php
                        $trip_facts_terms = get_terms( array(
                            'taxonomy'   => 'trip_fact',
                            'hide_empty' => false,
                        ) );
                        ?>
                        <div class="ctd-trip-facts-wrapper">
                             <!-- Selection Area -->
                            <div class="ctd-trip-facts-selection" style="display:flex; gap:10px; margin-bottom:15px; align-items:center;">
                                <select id="ctd_trip_fact_select">
                                    <option value=""><?php _e( 'Select Trip Fact', 'cotlas-travel' ); ?></option>
                                    <?php if(!empty($trip_facts_terms) && !is_wp_error($trip_facts_terms)): foreach($trip_facts_terms as $term): ?>
                                        <option value="<?php echo esc_attr($term->name); ?>"><?php echo esc_html($term->name); ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                                <button type="button" class="button button-primary" id="ctd_add_trip_fact_btn"><?php _e( 'Add Facts', 'cotlas-travel' ); ?></button>
                            </div>
                            <p class="description" style="margin-bottom:15px;"><?php _e( 'Select the trip fact title and click on add fact button to enter trip fact data.', 'cotlas-travel' ); ?></p>

                            
                        </div>
                    </div>
                    <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_trip_facts" id="ctd_trip_facts_container">
                                <?php 
                                if ( ! empty( $trip_facts ) && is_array( $trip_facts ) ) {
                                    foreach ( $trip_facts as $index => $fact ) {
                                        ?>
                                        <div class="ctd-repeater-item ctd-simple-list-item">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-simple-input-wrap" style="display:flex; align-items:center;">
                                                <strong class="ctd-fact-label-display" style="min-width: 150px;"><?php echo esc_html( isset($fact['label']) ? $fact['label'] : '' ); ?></strong>
                                                <input type="hidden" name="ctd_trip_facts[<?php echo $index; ?>][label]" value="<?php echo esc_attr( isset($fact['label']) ? $fact['label'] : '' ); ?>" class="ctd-fact-label-input" />
                                                <input type="text" name="ctd_trip_facts[<?php echo $index; ?>][value]" value="<?php echo esc_attr( isset($fact['value']) ? $fact['value'] : '' ); ?>" class="regular-text" style="flex:1;" placeholder="Value" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                
                                <script type="text/template" class="ctd-repeater-template" id="ctd_trip_fact_template">
                                    <div class="ctd-repeater-item ctd-simple-list-item">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-simple-input-wrap" style="display:flex; align-items:center;">
                                            <strong class="ctd-fact-label-display" style="min-width: 150px;">{label}</strong>
                                            <input type="hidden" name="ctd_trip_facts[{index}][label]" value="{label}" class="ctd-fact-label-input" />
                                            <input type="text" name="ctd_trip_facts[{index}][value]" value="" class="regular-text" style="flex:1;" placeholder="Value" />
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                </script>
                            </div>
                </div>
            </div>

            <!-- Gallery Tab -->
            <div id="ctd-tab-gallery" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Gallery', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <div class="ctd-field-header">
                            <label for="ctd_gallery_enable"><?php _e( 'Enable Image Gallery', 'cotlas-travel' ); ?></label>
                            <label class="ctd-switch">
                                <input type="checkbox" name="ctd_gallery_enable" id="ctd_gallery_enable" value="1" <?php checked( $gallery_enable, 1 ); ?> />
                                <span class="ctd-slider round"></span>
                            </label>
                        </div>
                        
                        <div class="ctd-gallery-wrap" style="<?php echo $gallery_enable ? '' : 'display:none;'; ?>; width: 100%; margin-top: 15px;">
                            <div class="ctd-gallery-images">
                                <?php
                                if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
                                    foreach ( $gallery_images as $image_id ) {
                                        $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
                                        if ( $image_url ) {
                                            ?>
                                            <div class="ctd-gallery-image">
                                                <input type="hidden" name="ctd_gallery_images[]" value="<?php echo esc_attr( $image_id ); ?>" />
                                                <img src="<?php echo esc_url( $image_url ); ?>" />
                                                <span class="ctd-gallery-remove dashicons dashicons-no-alt"></span>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                ?>
                                <div class="ctd-gallery-add">
                                    <span class="dashicons dashicons-plus"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ctd-form-field">
                        <div class="ctd-field-header">
                            <label for="ctd_video_gallery_enable"><?php _e( 'Enable Video Gallery', 'cotlas-travel' ); ?></label>
                            <label class="ctd-switch">
                                <input type="checkbox" name="ctd_video_gallery_enable" id="ctd_video_gallery_enable" value="1" <?php checked( $video_gallery_enable, 1 ); ?> />
                                <span class="ctd-slider round"></span>
                            </label>
                        </div>

                        <div class="ctd-video-gallery-wrap" style="<?php echo $video_gallery_enable ? '' : 'display:none;'; ?>; width: 100%; margin-top: 15px;">
                            <div class="ctd-repeater-wrapper" data-repeater="ctd_video_gallery">
                                <?php 
                                if ( ! empty( $video_gallery ) && is_array( $video_gallery ) ) {
                                    foreach ( $video_gallery as $index => $video ) {
                                        ?>
                                        <div class="ctd-repeater-item">
                                            <div class="ctd-repeater-item-header" style="margin-bottom:0;">
                                                <div class="ctd-input-wrap" style="width:100%;">
                                                    <input type="text" name="ctd_video_gallery[<?php echo $index; ?>]" value="<?php echo esc_attr( $video ); ?>" placeholder="Enter YouTube/Vimeo URL" />
                                                </div>
                                                <a href="#" class="ctd-repeater-remove" style="margin-left:10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <button type="button" class="button ctd-repeater-add"><?php _e( 'Add Video', 'cotlas-travel' ); ?></button>
                                
                                <script type="text/template" class="ctd-repeater-template">
                                    <div class="ctd-repeater-item">
                                        <div class="ctd-repeater-item-header" style="margin-bottom:0;">
                                            <div class="ctd-input-wrap" style="width:100%;">
                                                <input type="text" name="ctd_video_gallery[{index}]" placeholder="Enter YouTube/Vimeo URL" />
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="margin-left:10px;"><?php _e( 'Remove', 'cotlas-travel' ); ?></a>
                                        </div>
                                    </div>
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Tab -->
            <div id="ctd-tab-map" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Map', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_map_title" value="<?php echo esc_attr( $map_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Map Image', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="hidden" name="ctd_map_image" id="ctd_map_image" value="<?php echo esc_attr( $map_image ); ?>" />
                            <div class="ctd-image-preview" id="ctd_map_image_preview">
                                <?php if ( $map_image ) : ?>
                                    <img src="<?php echo esc_url( wp_get_attachment_image_url( $map_image, 'medium' ) ); ?>" style="max-width: 100%; height: auto;" />
                                <?php endif; ?>
                            </div>
                            <div style="margin-top: 10px;">
                                <button type="button" class="button ctd-upload-map-image"><?php _e( 'Upload Image', 'cotlas-travel' ); ?></button>
                                <button type="button" class="button ctd-remove-map-image" style="<?php echo $map_image ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'cotlas-travel' ); ?></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ctd-form-field">
                        <label><?php _e( 'Map Iframe Code', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <textarea name="ctd_map_iframe" rows="5"><?php echo esc_textarea( $map_iframe ); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Extra Services Tab -->
            <div id="ctd-tab-extra-services" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Extra Services', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                     <div class="ctd-form-field">
                        <label><?php _e( 'Enable Extra Services', 'cotlas-travel' ); ?></label>
                        <label class="ctd-switch">
                            <input type="checkbox" name="ctd_enable_extra_services" id="ctd_enable_extra_services" value="1" <?php checked( $extra_services_enable, 1 ); ?>>
                            <span class="ctd-slider round"></span>
                        </label>
                    </div>
                    
                    <div class="ctd-extra-services-wrap" style="<?php echo $extra_services_enable ? '' : 'display:none;'; ?>">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_extra_services_title" value="<?php echo esc_attr( $extra_services_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Description', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <textarea name="ctd_extra_services_desc" id="ctd_extra_services_desc" class="ctd-isolated-block-editor" rows="3"><?php echo esc_textarea( $extra_services_desc ); ?></textarea>
                        </div>
                    </div>

                    <div class="ctd-form-field">
                        <label><?php _e( 'Services', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_extra_services">
                            <?php 
                            if ( ! empty( $extra_services ) && is_array( $extra_services ) ) {
                                foreach ( $extra_services as $index => $service ) {
                                    $service_name = isset($service['name']) ? $service['name'] : '';
                                    ?>
                                    <div class="ctd-repeater-item ctd-accordion-item">
                                        <div class="ctd-accordion-header">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-accordion-toggle">
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <div class="ctd-accordion-title">
                                                <span class="ctd-service-title-text"><?php echo $service_name ? esc_html($service_name) : __('New Service', 'cotlas-travel'); ?></span>
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                        <div class="ctd-accordion-content">
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Service Name', 'cotlas-travel' ); ?></label>
                                                <input type="text" name="ctd_extra_services[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $service_name ); ?>" class="ctd-service-name-input" />
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Price', 'cotlas-travel' ); ?></label>
                                                <input type="number" name="ctd_extra_services[<?php echo $index; ?>][price]" value="<?php echo esc_attr( isset($service['price']) ? $service['price'] : '' ); ?>" step="0.01" />
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Pricing Type', 'cotlas-travel' ); ?></label>
                                                <?php $ptype = isset($service['pricing_type']) ? $service['pricing_type'] : 'per_item'; ?>
                                                <select name="ctd_extra_services[<?php echo $index; ?>][pricing_type]">
                                                    <option value="per_item" <?php selected( $ptype, 'per_item' ); ?>><?php _e('Per Item', 'cotlas-travel'); ?></option>
                                                    <option value="per_day" <?php selected( $ptype, 'per_day' ); ?>><?php _e('Per Day', 'cotlas-travel'); ?></option>
                                                    <option value="per_hour" <?php selected( $ptype, 'per_hour' ); ?>><?php _e('Per Hour', 'cotlas-travel'); ?></option>
                                                    <option value="per_person" <?php selected( $ptype, 'per_person' ); ?>><?php _e('Per Person', 'cotlas-travel'); ?></option>
                                                    <option value="per_group" <?php selected( $ptype, 'per_group' ); ?>><?php _e('Per Group', 'cotlas-travel'); ?></option>
                                                    <option value="per_vehicle" <?php selected( $ptype, 'per_vehicle' ); ?>><?php _e('Per Vehicle', 'cotlas-travel'); ?></option>
                                                    <option value="per_trip" <?php selected( $ptype, 'per_trip' ); ?>><?php _e('Per Trip', 'cotlas-travel'); ?></option>
                                                </select>
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Service Image', 'cotlas-travel' ); ?></label>
                                                <?php $img_id = isset($service['image_id']) ? intval($service['image_id']) : 0; ?>
                                                <input type="hidden" name="ctd_extra_services[<?php echo $index; ?>][image_id]" id="ctd_service_image_<?php echo $index; ?>" value="<?php echo esc_attr( $img_id ); ?>" />
                                                <div id="ctd_service_image_preview_<?php echo $index; ?>">
                                                    <?php if ( $img_id ) echo wp_get_attachment_image( $img_id, 'thumbnail' ); ?>
                                                </div>
                                                <button type="button" class="button ctd-upload-service-image" data-target="#ctd_service_image_<?php echo $index; ?>" data-preview="#ctd_service_image_preview_<?php echo $index; ?>"><?php _e( 'Select Image', 'cotlas-travel' ); ?></button>
                                                <button type="button" class="button ctd-remove-service-image" data-target="#ctd_service_image_<?php echo $index; ?>" data-preview="#ctd_service_image_preview_<?php echo $index; ?>" style="<?php echo $img_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Description', 'cotlas-travel' ); ?></label>
                                                <textarea name="ctd_extra_services[<?php echo $index; ?>][description]" id="ctd_service_desc_<?php echo $index; ?>" class="ctd-isolated-block-editor" rows="3"><?php echo esc_textarea( isset($service['description']) ? $service['description'] : '' ); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add Service', 'cotlas-travel' ); ?></button>
                            
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item ctd-accordion-item">
                                    <div class="ctd-accordion-header">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-accordion-toggle">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </div>
                                        <div class="ctd-accordion-title">
                                            <span class="ctd-service-title-text"><?php _e( 'New Service', 'cotlas-travel' ); ?></span>
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                    <div class="ctd-accordion-content">
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Service Name', 'cotlas-travel' ); ?></label>
                                            <input type="text" name="ctd_extra_services[{index}][name]" class="ctd-service-name-input" />
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Price', 'cotlas-travel' ); ?></label>
                                            <input type="number" name="ctd_extra_services[{index}][price]" step="0.01" />
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Pricing Type', 'cotlas-travel' ); ?></label>
                                            <select name="ctd_extra_services[{index}][pricing_type]">
                                                <option value="per_item"><?php _e('Per Item', 'cotlas-travel'); ?></option>
                                                <option value="per_day"><?php _e('Per Day', 'cotlas-travel'); ?></option>
                                                <option value="per_hour"><?php _e('Per Hour', 'cotlas-travel'); ?></option>
                                                <option value="per_person"><?php _e('Per Person', 'cotlas-travel'); ?></option>
                                                <option value="per_group"><?php _e('Per Group', 'cotlas-travel'); ?></option>
                                                <option value="per_vehicle"><?php _e('Per Vehicle', 'cotlas-travel'); ?></option>
                                                <option value="per_trip"><?php _e('Per Trip', 'cotlas-travel'); ?></option>
                                            </select>
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Service Image', 'cotlas-travel' ); ?></label>
                                            <input type="hidden" name="ctd_extra_services[{index}][image_id]" id="ctd_service_image_{index}" value="" />
                                            <div id="ctd_service_image_preview_{index}"></div>
                                            <button type="button" class="button ctd-upload-service-image" data-target="#ctd_service_image_{index}" data-preview="#ctd_service_image_preview_{index}"><?php _e( 'Select Image', 'cotlas-travel' ); ?></button>
                                            <button type="button" class="button ctd-remove-service-image" data-target="#ctd_service_image_{index}" data-preview="#ctd_service_image_preview_{index}" style="display:none;"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Description', 'cotlas-travel' ); ?></label>
                                            <textarea name="ctd_extra_services[{index}][description]" id="ctd_service_desc_{index}" class="ctd-isolated-block-editor" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                    </div> <!-- End .ctd-extra-services-wrap -->
                </div>
            </div>

            <!-- FAQs Tab -->
            <div id="ctd-tab-faqs" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'FAQs', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_faqs_title" value="<?php echo esc_attr( get_post_meta( $post->ID, 'ctd_faqs_title', true ) ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'Frequently Asked Questions', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_faqs">
                            <?php 
                            if ( ! empty( $faqs ) && is_array( $faqs ) ) {
                                foreach ( $faqs as $index => $faq ) {
                                    $question = isset($faq['question']) ? $faq['question'] : '';
                                    ?>
                                    <div class="ctd-repeater-item ctd-accordion-item">
                                        <div class="ctd-accordion-header">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-accordion-toggle">
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <div class="ctd-accordion-title">
                                                <span class="ctd-faq-question-text"><?php echo $question ? esc_html($question) : __('New FAQ', 'cotlas-travel'); ?></span>
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                        <div class="ctd-accordion-content">
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Question', 'cotlas-travel' ); ?></label>
                                                <input type="text" name="ctd_faqs[<?php echo $index; ?>][question]" value="<?php echo esc_attr( $question ); ?>" class="ctd-faq-question-input" />
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Answer', 'cotlas-travel' ); ?></label>
                                                <textarea name="ctd_faqs[<?php echo $index; ?>][answer]" id="ctd_faq_answer_<?php echo $index; ?>" class="ctd-isolated-block-editor" rows="3"><?php echo esc_textarea( isset($faq['answer']) ? $faq['answer'] : '' ); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add FAQ', 'cotlas-travel' ); ?></button>
                            
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item ctd-accordion-item">
                                    <div class="ctd-accordion-header">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-accordion-toggle">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </div>
                                        <div class="ctd-accordion-title">
                                            <span class="ctd-faq-question-text"><?php _e( 'New FAQ', 'cotlas-travel' ); ?></span>
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                    <div class="ctd-accordion-content">
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Question', 'cotlas-travel' ); ?></label>
                                            <input type="text" name="ctd_faqs[{index}][question]" class="ctd-faq-question-input" />
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Answer', 'cotlas-travel' ); ?></label>
                                            <textarea name="ctd_faqs[{index}][answer]" id="ctd_faq_answer_{index}" class="ctd-isolated-block-editor" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Downloads Tab -->
            <div id="ctd-tab-downloads" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'File Downloads', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                     <div class="ctd-form-field">
                        <label><?php _e( 'Enable Downloads Section', 'cotlas-travel' ); ?></label>
                        <label class="ctd-switch">
                            <input type="checkbox" name="ctd_enable_downloads" id="ctd_enable_downloads" value="1" <?php checked( $downloads_enable, 1 ); ?>>
                            <span class="ctd-slider round"></span>
                        </label>
                    </div>
                    
                    <div class="ctd-downloads-wrap" style="<?php echo $downloads_enable ? '' : 'display:none;'; ?>">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_downloads_title" value="<?php echo esc_attr( $downloads_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'Files', 'cotlas-travel' ); ?></label>
                        <div class="ctd-repeater-wrapper ctd-sortable-list" data-repeater="ctd_downloads">
                            <?php 
                            if ( ! empty( $downloads ) && is_array( $downloads ) ) {
                                foreach ( $downloads as $index => $download ) {
                                    $file_id = isset($download['file_id']) ? $download['file_id'] : '';
                                    $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
                                    $file_name = $file_id ? get_the_title($file_id) : '';
                                    $title = isset($download['title']) ? $download['title'] : '';
                                    ?>
                                    <div class="ctd-repeater-item ctd-accordion-item">
                                        <div class="ctd-accordion-header">
                                            <div class="ctd-sort-handle">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                            </div>
                                            <div class="ctd-accordion-toggle">
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <div class="ctd-accordion-title">
                                                <span class="ctd-download-title-text"><?php echo $title ? esc_html($title) : __('New File', 'cotlas-travel'); ?></span>
                                            </div>
                                            <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                        <div class="ctd-accordion-content">
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'Title', 'cotlas-travel' ); ?></label>
                                                <input type="text" name="ctd_downloads[<?php echo $index; ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="ctd-download-title-input" />
                                            </div>
                                            <div class="ctd-sub-field">
                                                <label><?php _e( 'File', 'cotlas-travel' ); ?></label>
                                                <input type="hidden" name="ctd_downloads[<?php echo $index; ?>][file_id]" value="<?php echo esc_attr( $file_id ); ?>" />
                                                <div class="ctd-file-preview">
                                                    <?php if($file_url): ?>
                                                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($file_name); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="button ctd-upload-file-repeater"><?php _e( 'Upload File', 'cotlas-travel' ); ?></button>
                                                <button type="button" class="button ctd-remove-file-repeater" style="<?php echo $file_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'cotlas-travel' ); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <button type="button" class="button ctd-repeater-add"><?php _e( 'Add File', 'cotlas-travel' ); ?></button>
                            
                            <script type="text/template" class="ctd-repeater-template">
                                <div class="ctd-repeater-item ctd-accordion-item">
                                    <div class="ctd-accordion-header">
                                        <div class="ctd-sort-handle">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.4 14.8C12.4 14.8 12.4 14.8 12.4 14.8C12.4 14.9105 12.3105 15 12.2 15H7.8C7.68954 15 7.6 14.9105 7.6 14.8C7.6 14.8 7.6 14.8 7.6 14.8V13.2C7.6 13.0895 7.68954 13 7.8 13H12.2C12.3105 13 12.4 13.0895 12.4 13.2V14.8ZM12.4 10.8C12.4 10.8 12.4 10.8 12.4 10.8C12.4 10.9105 12.3105 11 12.2 11H7.8C7.68954 11 7.6 10.9105 7.6 10.8C7.6 10.8 7.6 10.8 7.6 10.8V9.2C7.6 9.08954 7.68954 9 7.8 9H12.2C12.3105 9 12.4 9.08954 12.4 9.2V10.8ZM12.4 6.8C12.4 6.8 12.4 6.8 12.4 6.8C12.4 6.91046 12.3105 7 12.2 7H7.8C7.68954 7 7.6 6.91046 7.6 6.8C7.6 6.8 7.6 6.8 7.6 6.8V5.2C7.6 5.08954 7.68954 5 7.8 5H12.2C12.3105 5 12.4 5.08954 12.4 5.2V6.8Z" fill="currentColor"></path></svg>
                                        </div>
                                        <div class="ctd-accordion-toggle">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </div>
                                        <div class="ctd-accordion-title">
                                            <span class="ctd-download-title-text"><?php _e( 'New File', 'cotlas-travel' ); ?></span>
                                        </div>
                                        <a href="#" class="ctd-repeater-remove" style="color:#b32d2e;"><span class="dashicons dashicons-trash"></span></a>
                                    </div>
                                    <div class="ctd-accordion-content">
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'Title', 'cotlas-travel' ); ?></label>
                                            <input type="text" name="ctd_downloads[{index}][title]" class="ctd-download-title-text" />
                                        </div>
                                        <div class="ctd-sub-field">
                                            <label><?php _e( 'File', 'cotlas-travel' ); ?></label>
                                            <input type="hidden" name="ctd_downloads[{index}][file_id]" />
                                            <div class="ctd-file-preview"></div>
                                            <button type="button" class="button ctd-upload-file-repeater"><?php _e( 'Upload File', 'cotlas-travel' ); ?></button>
                                            <button type="button" class="button ctd-remove-file-repeater" style="display:none;"><?php _e( 'Remove', 'cotlas-travel' ); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </script>
                        </div>
                    </div>
                    </div> <!-- End .ctd-downloads-wrap -->
                </div>
            </div>

            <!-- More Info Tab -->
            <div id="ctd-tab-more-info" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'More Info', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Enable More Info Section', 'cotlas-travel' ); ?></label>
                        <label class="ctd-switch">
                            <input type="checkbox" name="ctd_enable_more_info" id="ctd_enable_more_info" value="1" <?php checked( $more_info_enable, 1 ); ?>>
                            <span class="ctd-slider round"></span>
                        </label>
                    </div>
                    
                    <div class="ctd-more-info-wrap" style="<?php echo $more_info_enable ? '' : 'display:none;'; ?>">
                     <div class="ctd-form-field">
                        <label><?php _e( 'Section Title', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_more_info_title" value="<?php echo esc_attr( $more_info_title ); ?>" class="regular-text" />
                        </div>
                    </div>
                    <div class="ctd-form-field">
                        <label><?php _e( 'More Information', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <textarea name="ctd_more_info_content" id="ctd_more_info_content" class="ctd-isolated-block-editor" rows="10"><?php echo esc_textarea( $more_info_content ); ?></textarea>
                        </div>
                    </div>
                    </div> <!-- End .ctd-more-info-wrap -->
                </div>
            </div>

            <!-- Booking Tab -->
            <div id="ctd-tab-booking" class="ctd-tab-pane">
                <div class="ctd-tab-header">
                    <h2 class="ctd-tab-heading"><?php _e( 'Booking Settings', 'cotlas-travel' ); ?></h2>
                </div>
                <div class="ctd-tab-fields">
                    <div class="ctd-form-field">
                         <div style="display: flex; align-items: center; justify-content: space-between;">
                            <label for="ctd_enable_booking" style="font-weight: 600;"><?php _e( 'Enable Booking', 'cotlas-travel' ); ?></label>
                            <label class="ctd-switch">
                                <input type="checkbox" name="ctd_enable_booking" id="ctd_enable_booking" value="1" <?php checked( $booking_enable, 1 ); ?>>
                                <span class="ctd-slider round"></span>
                            </label>
                        </div>
                        <div style="margin-left:10px; margin-top: 10px;">
                            <p class="description"><?php _e( 'Enable to show "Book Now" button linking to a custom URL. Disable for enquiry form.', 'cotlas-travel' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="ctd-booking-url-wrap" style="<?php echo $booking_enable ? '' : 'display:none;'; ?>">
                    <div class="ctd-form-field">
                        <label><?php _e( 'Custom Booking Link', 'cotlas-travel' ); ?></label>
                        <div class="ctd-input-wrap">
                            <input type="text" name="ctd_custom_booking_link" value="<?php echo esc_attr( $custom_booking_link ); ?>" class="regular-text" placeholder="https://..." />
                            <p class="description"><?php _e( 'Enter a custom URL to redirect users when they click "Book Now". Leave empty to use default enquiry form.', 'cotlas-travel' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

		</div>
		<div class="clear"></div>
	</div>
	<?php
}

/**
 * Save Trip Settings Meta Box Data.
 *
 * @param int $post_id Post ID.
 */
function ctd_save_trip_settings( $post_id ) {
	// Check if our nonce is set.
	if ( ! isset( $_POST['ctd_trip_settings_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['ctd_trip_settings_nonce'], 'ctd_save_trip_settings' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'trip' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	} else {
		return;
	}

	/* OK, it's safe for us to save the data now. */

    // Sanitize and save fields
    $fields = array(
		'ctd_trip_code'           => 'sanitize_text_field',
		'ctd_duration_days'       => 'absint',
		'ctd_duration_unit'       => 'sanitize_text_field',
		'ctd_duration_nights'     => 'absint',
		'ctd_min_age'             => 'absint',
		'ctd_max_age'             => 'absint',
		'ctd_min_travellers'      => 'absint',
		'ctd_total_seats'         => 'absint',
        'ctd_overview_title'      => 'sanitize_text_field',
        'ctd_highlights_title'    => 'sanitize_text_field',
        'ctd_itinerary_title'     => 'sanitize_text_field',
        'ctd_inc_exc_title'       => 'sanitize_text_field',
        'ctd_cost_includes_title' => 'sanitize_text_field',
        'ctd_cost_excludes_title' => 'sanitize_text_field',
        'ctd_trip_info_title'     => 'sanitize_text_field',
        'ctd_map_title'           => 'sanitize_text_field',
        'ctd_map_image'           => 'absint',
        'ctd_extra_services_title'=> 'sanitize_text_field',
        // 'ctd_extra_services_desc' => 'sanitize_textarea_field', // Moved to editors for HTML support
        'ctd_more_info_title'     => 'sanitize_text_field',
        'ctd_meals_title'         => 'sanitize_text_field',
        'ctd_flights_title'       => 'sanitize_text_field',
        'ctd_faqs_title'          => 'sanitize_text_field',
        'ctd_downloads_title'     => 'sanitize_text_field',
    );

	foreach ( $fields as $field => $sanitizer ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, $sanitizer( $_POST[ $field ] ) );
		}
	}
    
    // Editors
    $editors = array( 'ctd_overview_content', 'ctd_more_info_content', 'ctd_itinerary_description', 'ctd_extra_services_desc' );
    foreach ( $editors as $editor ) {
        if ( isset( $_POST[ $editor ] ) ) {
            update_post_meta( $post_id, $editor, wp_kses_post( $_POST[ $editor ] ) );
        }
    }

    // Map Iframe Code - Allow iframes
    if ( isset( $_POST['ctd_map_iframe'] ) ) {
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
        update_post_meta( $post_id, 'ctd_map_iframe', wp_kses( $_POST['ctd_map_iframe'], $allowed_tags ) );
    }
    
    // Custom Booking Link
    if ( isset( $_POST['ctd_custom_booking_link'] ) ) {
        update_post_meta( $post_id, 'ctd_custom_booking_link', esc_url_raw( $_POST['ctd_custom_booking_link'] ) );
    }
    
    // Enable Booking
    $enable_booking = isset( $_POST['ctd_enable_booking'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_booking', $enable_booking );
    
    // Enable Itinerary PDF
    $enable_itinerary_pdf = isset( $_POST['ctd_enable_itinerary_pdf'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_itinerary_pdf', $enable_itinerary_pdf );

    // Enable More Info
    $enable_more_info = isset( $_POST['ctd_enable_more_info'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_more_info', $enable_more_info );
    
    // Enable Downloads
    $enable_downloads = isset( $_POST['ctd_enable_downloads'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_downloads', $enable_downloads );
    
    // Enable Extra Services
    $extra_services_enable = isset( $_POST['ctd_enable_extra_services'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_extra_services', $extra_services_enable );

    // Enable Flights
    $flights_enable = isset( $_POST['ctd_enable_flights'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_flights', $flights_enable );

    // Enable Meals Details
    $meals_enable = isset( $_POST['ctd_enable_meals'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_enable_meals', $meals_enable );

    // Itinerary PDF
    if ( isset( $_POST['ctd_itinerary_pdf'] ) ) {
        update_post_meta( $post_id, 'ctd_itinerary_pdf', intval( $_POST['ctd_itinerary_pdf'] ) );
    }

	// Checkbox for Enable Age Limit
	$enable_age = isset( $_POST['ctd_enable_age_limit'] ) ? 1 : 0;
	update_post_meta( $post_id, 'ctd_enable_age_limit', $enable_age );
    
    // Checkbox for Galleries
    $gallery_enable = isset( $_POST['ctd_gallery_enable'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_gallery_enable', $gallery_enable );
    
    $video_gallery_enable = isset( $_POST['ctd_video_gallery_enable'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ctd_video_gallery_enable', $video_gallery_enable );

    // Arrays (Pricing, Highlights, Itineraries, Trip Facts, Video Gallery, Gallery Images)
    
    // Pricing Packages
    if ( isset( $_POST['ctd_pricing_packages'] ) && is_array( $_POST['ctd_pricing_packages'] ) ) {
        $pricing = array();
        foreach ( $_POST['ctd_pricing_packages'] as $package ) {
            $pkg_data = array(
                'title' => isset( $package['title'] ) ? sanitize_text_field( $package['title'] ) : '',
                'enable_dates' => isset( $package['enable_dates'] ) ? 1 : 0,
                'dates' => isset( $package['dates'] ) && is_array($package['dates']) ? array_map('sanitize_text_field', $package['dates']) : array(),
                'general_desc' => isset( $package['general_desc'] ) ? sanitize_textarea_field( $package['general_desc'] ) : '',
                'prices' => array()
            );

            if ( isset( $package['prices'] ) && is_array( $package['prices'] ) ) {
                foreach ( $package['prices'] as $cat_id => $price_data ) {
                     $pkg_data['prices'][ $cat_id ] = array(
                        'regular_price' => isset( $price_data['regular_price'] ) ? floatval( $price_data['regular_price'] ) : '',
                        'pricing_type'  => isset( $price_data['pricing_type'] ) ? sanitize_text_field( $price_data['pricing_type'] ) : 'person',
                        'min_pax'       => isset( $price_data['min_pax'] ) ? absint( $price_data['min_pax'] ) : '',
                        'enabled'       => isset( $price_data['enabled'] ) ? 1 : 0,
                     );
                }
            }
            $pricing[] = $pkg_data;
        }
        update_post_meta( $post_id, 'ctd_pricing_packages', $pricing );
        
        // Calculate and save Min Price for filtering
        $min_price = '';
        if ( ! empty( $pricing ) ) {
            foreach ( $pricing as $pkg ) {
                if ( ! empty( $pkg['prices'] ) ) {
                    foreach ( $pkg['prices'] as $p ) {
                        if ( isset( $p['regular_price'] ) && is_numeric( $p['regular_price'] ) && $p['regular_price'] > 0 ) {
                            if ( $min_price === '' || $p['regular_price'] < $min_price ) {
                                $min_price = $p['regular_price'];
                            }
                        }
                    }
                }
            }
        }
        update_post_meta( $post_id, 'ctd_min_price', $min_price );

    } else {
        update_post_meta( $post_id, 'ctd_pricing_packages', array() );
        update_post_meta( $post_id, 'ctd_min_price', '' );
    }

    // Highlights
    if ( isset( $_POST['ctd_highlights'] ) && is_array( $_POST['ctd_highlights'] ) ) {
        $highlights = array_map( 'sanitize_text_field', $_POST['ctd_highlights'] );
        $highlights = array_filter( $highlights ); // Remove empty
        update_post_meta( $post_id, 'ctd_highlights', $highlights );
    } else {
        update_post_meta( $post_id, 'ctd_highlights', array() );
    }

    // Itineraries
    if ( isset( $_POST['ctd_itineraries'] ) && is_array( $_POST['ctd_itineraries'] ) ) {
        $itineraries = array();
        foreach ( $_POST['ctd_itineraries'] as $itinerary ) {
            $itineraries[] = array(
                'title'   => sanitize_text_field( $itinerary['title'] ),
                'content' => wp_kses_post( $itinerary['content'] ),
                'image_id' => isset($itinerary['image_id']) ? intval($itinerary['image_id']) : '',
            );
        }
        update_post_meta( $post_id, 'ctd_itineraries', $itineraries );
    } else {
        update_post_meta( $post_id, 'ctd_itineraries', array() );
    }

    // Trip Facts
    if ( isset( $_POST['ctd_trip_facts'] ) && is_array( $_POST['ctd_trip_facts'] ) ) {
        $facts = array();
        foreach ( $_POST['ctd_trip_facts'] as $fact ) {
            if ( ! empty( $fact['label'] ) ) {
                $facts[] = array(
                    'label' => sanitize_text_field( $fact['label'] ),
                    'value' => sanitize_text_field( $fact['value'] ),
                );
            }
        }
        update_post_meta( $post_id, 'ctd_trip_facts', $facts );
    } else {
        update_post_meta( $post_id, 'ctd_trip_facts', array() );
    }
    
    // Video Gallery
    if ( isset( $_POST['ctd_video_gallery'] ) && is_array( $_POST['ctd_video_gallery'] ) ) {
        $videos = array_map( 'sanitize_text_field', $_POST['ctd_video_gallery'] );
        $videos = array_filter( $videos );
        update_post_meta( $post_id, 'ctd_video_gallery', $videos );
    } else {
        update_post_meta( $post_id, 'ctd_video_gallery', array() );
    }
    
    // Image Gallery
    if ( isset( $_POST['ctd_gallery_images'] ) && is_array( $_POST['ctd_gallery_images'] ) ) {
        $images = array_map( 'intval', $_POST['ctd_gallery_images'] );
        update_post_meta( $post_id, 'ctd_gallery_images', $images );
    } else {
        update_post_meta( $post_id, 'ctd_gallery_images', array() );
    }
    
    // Extra Services
    if ( isset( $_POST['ctd_extra_services'] ) && is_array( $_POST['ctd_extra_services'] ) ) {
        $services = array();
        foreach ( $_POST['ctd_extra_services'] as $service ) {
            if ( ! empty( $service['name'] ) ) {
                $allowed_ptypes = array( 'per_item','per_day','per_hour','per_person','per_group','per_vehicle','per_trip' );
                $ptype = isset( $service['pricing_type'] ) ? sanitize_text_field( $service['pricing_type'] ) : 'per_item';
                if ( ! in_array( $ptype, $allowed_ptypes, true ) ) {
                    $ptype = 'per_item';
                }
                $services[] = array(
                    'name'        => sanitize_text_field( $service['name'] ),
                    'price'       => floatval( $service['price'] ),
                    'pricing_type'=> $ptype,
                    'image_id'    => isset( $service['image_id'] ) ? intval( $service['image_id'] ) : 0,
                    'description' => wp_kses_post( $service['description'] ),
                );
            }
        }
        update_post_meta( $post_id, 'ctd_extra_services', $services );
    } else {
        update_post_meta( $post_id, 'ctd_extra_services', array() );
    }

    // FAQs
    if ( isset( $_POST['ctd_faqs'] ) && is_array( $_POST['ctd_faqs'] ) ) {
        $faqs = array();
        foreach ( $_POST['ctd_faqs'] as $faq ) {
            if ( ! empty( $faq['question'] ) ) {
                $faqs[] = array(
                    'question' => sanitize_text_field( $faq['question'] ),
                    'answer'   => wp_kses_post( $faq['answer'] ),
                );
            }
        }
        update_post_meta( $post_id, 'ctd_faqs', $faqs );
    } else {
        update_post_meta( $post_id, 'ctd_faqs', array() );
    }

    // Downloads
    if ( isset( $_POST['ctd_downloads'] ) && is_array( $_POST['ctd_downloads'] ) ) {
        $downloads = array();
        foreach ( $_POST['ctd_downloads'] as $download ) {
            if ( ! empty( $download['title'] ) ) {
                $downloads[] = array(
                    'title'   => sanitize_text_field( $download['title'] ),
                    'file_id' => intval( $download['file_id'] ),
                );
            }
        }
        update_post_meta( $post_id, 'ctd_downloads', $downloads );
    } else {
        update_post_meta( $post_id, 'ctd_downloads', array() );
    }

    // Guide Languages
    if ( isset( $_POST['ctd_guide_languages'] ) && is_array( $_POST['ctd_guide_languages'] ) ) {
        $languages = array_map( 'sanitize_text_field', $_POST['ctd_guide_languages'] );
        $languages = array_filter( $languages );
        update_post_meta( $post_id, 'ctd_guide_languages', $languages );
    } else {
        update_post_meta( $post_id, 'ctd_guide_languages', array() );
    }
    
    // Cost Includes (Repeater)
    if ( isset( $_POST['ctd_cost_includes'] ) && is_array( $_POST['ctd_cost_includes'] ) ) {
        $includes = array_map( 'sanitize_text_field', $_POST['ctd_cost_includes'] );
        $includes = array_filter( $includes );
        update_post_meta( $post_id, 'ctd_cost_includes', $includes );
    } else {
        update_post_meta( $post_id, 'ctd_cost_includes', array() );
    }
    
    // Cost Excludes (Repeater)
    if ( isset( $_POST['ctd_cost_excludes'] ) && is_array( $_POST['ctd_cost_excludes'] ) ) {
        $excludes = array_map( 'sanitize_text_field', $_POST['ctd_cost_excludes'] );
        $excludes = array_filter( $excludes );
        update_post_meta( $post_id, 'ctd_cost_excludes', $excludes );
    } else {
        update_post_meta( $post_id, 'ctd_cost_excludes', array() );
    }

    // Meals
    if ( isset( $_POST['ctd_meals'] ) && is_array( $_POST['ctd_meals'] ) ) {
        $meals = array_map( 'sanitize_text_field', $_POST['ctd_meals'] );
        $meals = array_filter( $meals );
        update_post_meta( $post_id, 'ctd_meals', $meals );
    } else {
        update_post_meta( $post_id, 'ctd_meals', array() );
    }

    // Flights
    if ( isset( $_POST['ctd_flights'] ) && is_array( $_POST['ctd_flights'] ) ) {
        $flights = array();
        // Outbound
        if ( isset( $_POST['ctd_flights']['outbound'] ) ) {
            $flights['outbound'] = array_map( 'sanitize_text_field', $_POST['ctd_flights']['outbound'] );
        }
        // Inbound
        if ( isset( $_POST['ctd_flights']['inbound'] ) ) {
            $flights['inbound'] = array_map( 'sanitize_text_field', $_POST['ctd_flights']['inbound'] );
        }
        update_post_meta( $post_id, 'ctd_flights', $flights );
    } else {
        update_post_meta( $post_id, 'ctd_flights', array() );
    }

	// Save Trip Tag
	if ( isset( $_POST['ctd_trip_tag'] ) && '' !== $_POST['ctd_trip_tag'] ) {
		$tag_id = intval( $_POST['ctd_trip_tag'] );
		wp_set_object_terms( $post_id, $tag_id, 'trip_tag' );
	} else {
		// If field is present but empty (deselect), clear terms.
		wp_set_object_terms( $post_id, array(), 'trip_tag' );
	}
}
add_action( 'save_post', 'ctd_save_trip_settings' );
