<?php
/**
 * Admin functions and Custom Meta Fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom fields to taxonomy add screen.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function ctd_add_term_fields( $taxonomy ) {
	?>
	<div class="form-field term-newly-launched-wrap">
		<label for="ctd-newly-launched">
			<input type="checkbox" name="ctd_newly_launched" id="ctd-newly-launched" value="1" />
			<?php _e( 'Newly Launched', 'cotlas-travel' ); ?>
		</label>
		<p class="description"><?php _e( 'Check this if the term is newly launched.', 'cotlas-travel' ); ?></p>
	</div>

	<div class="form-field term-short-description-wrap">
		<label for="term-short-description"><?php _e( 'Short Description', 'cotlas-travel' ); ?></label>
		<textarea name="ctd_short_description" id="term-short-description" rows="5" cols="40"></textarea>
		<p class="description"><?php _e( 'A short description for the term.', 'cotlas-travel' ); ?></p>
	</div>

	<div class="form-field term-image-wrap">
		<label for="term-image"><?php _e( 'Image/Icon', 'cotlas-travel' ); ?></label>
		<input type="hidden" name="ctd_image_id" id="ctd-image-id" value="">
		<div id="ctd-image-preview" style="margin-bottom: 10px;"></div>
		<button type="button" class="button ctd-upload-image"><?php _e( 'Upload/Add Image', 'cotlas-travel' ); ?></button>
		<button type="button" class="button ctd-remove-image" style="display:none;"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
		<p class="description"><?php _e( 'Upload an image or icon for this term.', 'cotlas-travel' ); ?></p>
	</div>
	<?php
}

/**
 * Add custom fields to taxonomy edit screen.
 *
 * @param WP_Term $term     Current term object.
 * @param string  $taxonomy Current taxonomy slug.
 */
function ctd_edit_term_fields( $term, $taxonomy ) {
	$short_description = get_term_meta( $term->term_id, 'ctd_short_description', true );
	$image_id          = get_term_meta( $term->term_id, 'ctd_image_id', true );
	$image_url         = $image_id ? wp_get_attachment_url( $image_id ) : '';
	$newly_launched    = get_term_meta( $term->term_id, 'ctd_newly_launched', true );
	?>
	<tr class="form-field term-newly-launched-wrap">
		<th scope="row"><label for="ctd-newly-launched"><?php _e( 'Newly Launched', 'cotlas-travel' ); ?></label></th>
		<td>
			<label for="ctd-newly-launched">
				<input type="checkbox" name="ctd_newly_launched" id="ctd-newly-launched" value="1" <?php checked( $newly_launched, 1 ); ?> />
				<?php _e( 'Newly Launched', 'cotlas-travel' ); ?>
			</label>
			<p class="description"><?php _e( 'Check this if the term is newly launched.', 'cotlas-travel' ); ?></p>
		</td>
	</tr>

	<tr class="form-field term-short-description-wrap">
		<th scope="row"><label for="term-short-description"><?php _e( 'Short Description', 'cotlas-travel' ); ?></label></th>
		<td>
			<textarea name="ctd_short_description" id="term-short-description" rows="5" cols="40"><?php echo esc_textarea( $short_description ); ?></textarea>
			<p class="description"><?php _e( 'A short description for the term.', 'cotlas-travel' ); ?></p>
		</td>
	</tr>

	<tr class="form-field term-image-wrap">
		<th scope="row"><label for="term-image"><?php _e( 'Image/Icon', 'cotlas-travel' ); ?></label></th>
		<td>
			<input type="hidden" name="ctd_image_id" id="ctd-image-id" value="<?php echo esc_attr( $image_id ); ?>">
			<div id="ctd-image-preview" style="margin-bottom: 10px;">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; height: auto;" />
				<?php endif; ?>
			</div>
			<button type="button" class="button ctd-upload-image"><?php _e( 'Upload/Add Image', 'cotlas-travel' ); ?></button>
			<button type="button" class="button ctd-remove-image" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove Image', 'cotlas-travel' ); ?></button>
			<p class="description"><?php _e( 'Upload an image or icon for this term.', 'cotlas-travel' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save custom fields.
 *
 * @param int $term_id Term ID.
 */
function ctd_save_term_fields( $term_id ) {
	if ( isset( $_POST['ctd_newly_launched'] ) ) {
		update_term_meta( $term_id, 'ctd_newly_launched', 1 );
	} else {
		// Only delete if we are in a context where the field would have been present (avoid accidental deletion in quick edit if field not there)
		// Similar check as pricing category save logic could be applied, but standard practice often just checks if not set.
		// However, for checkboxes, unchecked means not sent.
		if ( isset( $_POST['action'] ) && ( 'add-tag' === $_POST['action'] || 'editedtag' === $_POST['action'] ) ) {
			delete_term_meta( $term_id, 'ctd_newly_launched' );
		}
	}

	if ( isset( $_POST['ctd_short_description'] ) ) {
		update_term_meta( $term_id, 'ctd_short_description', sanitize_textarea_field( $_POST['ctd_short_description'] ) );
	}

	if ( isset( $_POST['ctd_image_id'] ) ) {
		update_term_meta( $term_id, 'ctd_image_id', absint( $_POST['ctd_image_id'] ) );
	}
}

/**
 * Add Pricing Category specific fields to add screen.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function ctd_add_pricing_category_fields( $taxonomy ) {
	?>
	<div class="form-field term-primary-pricing-wrap">
		<label for="ctd-is-primary-pricing">
			<input type="checkbox" name="ctd_is_primary_pricing" id="ctd-is-primary-pricing" value="1" />
			<?php _e( 'Set as Primary Pricing Category', 'cotlas-travel' ); ?>
		</label>
		<p class="description"><?php _e( 'If checked, this category will be treated as primary pricing category in packages and trip price will be the price of this category.', 'cotlas-travel' ); ?></p>
	</div>

	<div class="form-field term-age-group-wrap">
		<label for="ctd-age-group"><?php _e( 'Age Group', 'cotlas-travel' ); ?></label>
		<input type="text" name="ctd_age_group" id="ctd-age-group" value="" size="40" />
		<p class="description"><?php _e( 'Age Group of the category. (e.g. 18-30)', 'cotlas-travel' ); ?></p>
	</div>
	<?php
}

/**
 * Add Pricing Category specific fields to edit screen.
 *
 * @param WP_Term $term     Current term object.
 * @param string  $taxonomy Current taxonomy slug.
 */
function ctd_edit_pricing_category_fields( $term, $taxonomy ) {
	$is_primary = get_term_meta( $term->term_id, 'ctd_is_primary_pricing', true );
	$age_group  = get_term_meta( $term->term_id, 'ctd_age_group', true );
	?>
	<tr class="form-field term-primary-pricing-wrap">
		<th scope="row"><label for="ctd-is-primary-pricing"><?php _e( 'Set as Primary Pricing Category', 'cotlas-travel' ); ?></label></th>
		<td>
			<label for="ctd-is-primary-pricing">
				<input type="checkbox" name="ctd_is_primary_pricing" id="ctd-is-primary-pricing" value="1" <?php checked( $is_primary, 1 ); ?> />
				<?php _e( 'Set as Primary Pricing Category', 'cotlas-travel' ); ?>
			</label>
			<p class="description"><?php _e( 'If checked, this category will be treated as primary pricing category in packages and trip price will be the price of this category.', 'cotlas-travel' ); ?></p>
		</td>
	</tr>

	<tr class="form-field term-age-group-wrap">
		<th scope="row"><label for="ctd-age-group"><?php _e( 'Age Group', 'cotlas-travel' ); ?></label></th>
		<td>
			<input type="text" name="ctd_age_group" id="ctd-age-group" value="<?php echo esc_attr( $age_group ); ?>" size="40" />
			<p class="description"><?php _e( 'Age Group of the category. (e.g. 18-30)', 'cotlas-travel' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save Pricing Category fields.
 *
 * @param int $term_id Term ID.
 */
function ctd_save_pricing_category_fields( $term_id ) {
	if ( isset( $_POST['ctd_is_primary_pricing'] ) ) {
		update_term_meta( $term_id, 'ctd_is_primary_pricing', 1 );
	} else {
		// Checkbox unchecked or not present in POST (if form submitted)
		// We only want to delete if we are actually saving the form.
		// For 'created_{taxonomy}' and 'edited_{taxonomy}', $_POST is populated.
		// However, beware of quick edit which might not have these fields.
		// But quick edit doesn't fire edited_{taxonomy} with the same form data usually.
		// Safer to check nonce, but for simplicity here:
		if ( isset( $_POST['action'] ) && ( 'add-tag' === $_POST['action'] || 'editedtag' === $_POST['action'] ) ) {
             delete_term_meta( $term_id, 'ctd_is_primary_pricing' );
        }
	}

	if ( isset( $_POST['ctd_age_group'] ) ) {
		update_term_meta( $term_id, 'ctd_age_group', sanitize_text_field( $_POST['ctd_age_group'] ) );
	}
}

// Hook for Pricing Category
add_action( 'pricing_category_add_form_fields', 'ctd_add_pricing_category_fields' );
add_action( 'pricing_category_edit_form_fields', 'ctd_edit_pricing_category_fields', 10, 2 );
add_action( 'created_pricing_category', 'ctd_save_pricing_category_fields' );
add_action( 'edited_pricing_category', 'ctd_save_pricing_category_fields' );

// Taxonomies that need these fields.
$taxonomies = array( 'destination', 'activities', 'trip_types', 'difficulty', 'trip_fact' );

foreach ( $taxonomies as $taxonomy ) {
	add_action( "{$taxonomy}_add_form_fields", 'ctd_add_term_fields' );
	add_action( "{$taxonomy}_edit_form_fields", 'ctd_edit_term_fields', 10, 2 );
	add_action( "created_{$taxonomy}", 'ctd_save_term_fields' );
	add_action( "edited_{$taxonomy}", 'ctd_save_term_fields' );
	
	// Add Image Column to Taxonomy List
	add_filter( "manage_edit-{$taxonomy}_columns", 'ctd_add_taxonomy_columns' );
	add_filter( "manage_{$taxonomy}_custom_column", 'ctd_manage_taxonomy_custom_column', 10, 3 );
}

/**
 * Add Image column to taxonomy list.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function ctd_add_taxonomy_columns( $columns ) {
	$new_columns = array();
	$image_added = false;
	
	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;
		// Try to insert after checkbox or before name, but standard is usually after CB.
		if ( 'cb' === $key ) {
			$new_columns['ctd_image'] = __( 'Image', 'cotlas-travel' );
			$image_added = true;
		}
	}
	
	if ( ! $image_added ) {
		$new_columns = array_merge( array( 'ctd_image' => __( 'Image', 'cotlas-travel' ) ), $columns );
	}
	
	return $new_columns;
}

/**
 * Display Image in taxonomy list column.
 *
 * @param string $content Column content.
 * @param string $column_name Column name.
 * @param int    $term_id Term ID.
 * @return string Modified content.
 */
function ctd_manage_taxonomy_custom_column( $content, $column_name, $term_id ) {
	if ( 'ctd_image' !== $column_name ) {
		return $content;
	}

	$image_id = get_term_meta( $term_id, 'ctd_image_id', true );
	
	if ( $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
		if ( $image_url ) {
			$content = '<img src="' . esc_url( $image_url ) . '" class="ctd-admin-term-image" />';
		}
	} else {
		$content = '<span aria-hidden="true">—</span>';
	}

	return $content;
}

/**
 * Allow SVG uploads.
 *
 * @param array $mimes Allowed mime types.
 * @return array Modified mime types.
 */
function ctd_allow_svg_upload( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'ctd_allow_svg_upload' );
