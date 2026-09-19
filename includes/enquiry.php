<?php
/**
 * Trip enquiry form: settings, rendering, Turnstile check and submission handler.
 *
 * @package CotlasTravel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enquiry settings, merged over sensible defaults.
 *
 * @return array
 */
function ctd_enquiry_get_settings() {
	$defaults = array(
		'from_name'          => get_bloginfo( 'name' ),
		'from_email'         => get_option( 'admin_email' ),
		'reply_to_email'     => get_option( 'admin_email' ),
		'subject'            => 'New Trip Enquiry',
		'admin_email'        => get_option( 'admin_email' ),
		'turnstile_site_key' => '',
		'turnstile_secret_key' => '',
	);

	$opts = get_option( 'ctd_enquiry_settings', array() );

	return wp_parse_args( $opts, $defaults );
}

add_action( 'admin_menu', function() {
	add_options_page( 'Travel Enquiry', 'Travel Enquiry', 'manage_options', 'ctd-enquiry', 'ctd_enquiry_settings_page' );
} );

add_action( 'admin_init', function() {
	register_setting( 'ctd_enquiry', 'ctd_enquiry_settings' );
	add_settings_section( 'ctd_enquiry_main', '', '__return_false', 'ctd-enquiry' );

	$fields = array(
		'from_name'            => 'From Name',
		'from_email'           => 'From Email',
		'reply_to_email'       => 'Reply-To Email',
		'subject'              => 'Mail Subject',
		'admin_email'          => 'Admin Recipient Email',
		'turnstile_site_key'   => 'Turnstile Site Key',
		'turnstile_secret_key' => 'Turnstile Secret Key',
	);

	foreach ( $fields as $key => $label ) {
		add_settings_field(
			$key,
			$label,
			function() use ( $key ) {
				$opts = ctd_enquiry_get_settings();
				printf( '<input type="text" name="ctd_enquiry_settings[%s]" value="%s" class="regular-text" />', esc_attr( $key ), esc_attr( $opts[ $key ] ) );
			},
			'ctd-enquiry',
			'ctd_enquiry_main'
		);
	}
} );

/**
 * Render the enquiry settings screen.
 */
function ctd_enquiry_settings_page() {
	echo '<div class="wrap"><h1>Travel Enquiry Settings</h1><form method="post" action="options.php">';
	settings_fields( 'ctd_enquiry' );
	do_settings_sections( 'ctd-enquiry' );
	submit_button();
	echo '</form></div>';
}

/**
 * Verify a Cloudflare Turnstile token.
 *
 * @param string $token Turnstile response token.
 * @return bool
 */
function ctd_turnstile_verify( $token ) {
	$settings = ctd_enquiry_get_settings();

	if ( empty( $settings['turnstile_secret_key'] ) ) {
		return false;
	}

	$body = array(
		'secret'   => $settings['turnstile_secret_key'],
		'response' => $token,
		'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
	);

	$resp = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'body'    => $body,
			'timeout' => 10,
		)
	);

	if ( is_wp_error( $resp ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $resp ), true );

	return isset( $data['success'] ) && true === $data['success'];
}

add_action( 'wp_enqueue_scripts', function() {
	$settings = ctd_enquiry_get_settings();
	if ( ! empty( $settings['turnstile_site_key'] ) ) {
		wp_register_script( 'ctd-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
	}
} );

/**
 * Render the enquiry form.
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $context 'page' or 'sidebar'.
 * @return string
 */
function ctd_render_enquiry_form( $atts, $context = 'page' ) {
	// The form is often placed in page content rather than on a trip page, so
	// pull in the plugin stylesheet whenever it renders.
	if ( wp_style_is( 'ctd-front-style', 'registered' ) ) {
		wp_enqueue_style( 'ctd-front-style' );
	}

	$settings = ctd_enquiry_get_settings();
	$site_key = $settings['turnstile_site_key'];
	$post_id  = isset( $atts['post_id'] ) && $atts['post_id'] ? intval( $atts['post_id'] ) : get_the_ID();
	$trip_title = $post_id ? get_the_title( $post_id ) : '';
	$prefill  = 'I\'d like to book "' . $trip_title . '". Please contact me with the booking details.';
	$action   = esc_url( admin_url( 'admin-post.php' ) );
	$base_class = 'sidebar' === $context ? 'ctd-enquiry-form ctd-enquiry-form--sidebar' : 'ctd-enquiry-form ctd-enquiry-form--page';
	$extra_class = ! empty( $atts['class'] ) ? ' ' . sanitize_html_class( $atts['class'] ) : '';
	$class = $base_class . $extra_class;

	if ( ! empty( $site_key ) ) {
		wp_enqueue_script( 'ctd-turnstile' );
	}

	$html  = '<form method="post" action="' . $action . '" class="' . esc_attr( $class ) . '">';
	$html .= '<input type="hidden" name="action" value="ctd_enquiry_submit" />';
	$html .= wp_nonce_field( 'ctd_enquiry_submit', 'ctd_enquiry_nonce', true, false );
	$html .= '<input type="hidden" name="post_id" value="' . esc_attr( $post_id ) . '" />';

	if ( 'sidebar' === $context ) {
		$html .= '<div class="ctd-form-stack">';
		$html .= '<div class="ctd-form-field ctd-field ctd-field-name"><input class="ctd-input" type="text" name="ctd_name" placeholder="Name" required /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-field-email"><input class="ctd-input" type="email" name="ctd_email" placeholder="Email" required /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-field-city"><input class="ctd-input" type="text" name="ctd_city" placeholder="City" /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-field-mobile"><input class="ctd-input" type="text" name="ctd_mobile" placeholder="Mobile" required /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-field-message"><textarea class="ctd-textarea" name="ctd_message" rows="4" placeholder="Message">' . esc_textarea( $prefill ) . '</textarea></div>';
		if ( ! empty( $site_key ) ) {
			$html .= '<div class="ctd-form-field ctd-field ctd-field-turnstile"><div class="cf-turnstile" data-sitekey="' . esc_attr( $site_key ) . '"></div></div>';
		}
		$html .= '<div class="ctd-form-field ctd-field ctd-field-submit"><button type="submit" class="button ctd-button">Send an Enquiry</button></div>';
		$html .= '</div>';
	} else {
		$html .= '<div class="ctd-enquiry-grid" style="display:grid;grid-template-rows:auto auto auto auto;gap:12px;">';
		$html .= '<div class="ctd-form-row ctd-row-1" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-name"><input class="ctd-input" type="text" name="ctd_name" placeholder="Name" required /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-email"><input class="ctd-input" type="email" name="ctd_email" placeholder="Email" required /></div>';
		$html .= '</div>';
		$html .= '<div class="ctd-form-row ctd-row-2" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-mobile"><input class="ctd-input" type="text" name="ctd_mobile" placeholder="Mobile" required /></div>';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-city"><input class="ctd-input" type="text" name="ctd_city" placeholder="City" /></div>';
		$html .= '</div>';
		$html .= '<div class="ctd-form-row ctd-row-3" style="display:grid;grid-template-columns:1fr;gap:12px;align-items:start;">';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-message"><textarea class="ctd-textarea" name="ctd_message" rows="6" placeholder="Message">' . esc_textarea( $prefill ) . '</textarea>';
		if ( ! empty( $site_key ) ) {
			$html .= '<div class="ctd-turnstile-wrap"><div class="cf-turnstile" data-sitekey="' . esc_attr( $site_key ) . '"></div></div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="ctd-form-row ctd-row-4" style="display:grid;grid-template-columns:1fr;gap:12px;">';
		$html .= '<div class="ctd-form-field ctd-field ctd-col ctd-col-submit"><button type="submit" class="button ctd-button" style="width:100%;">Send and Enquiry</button></div>';
		$html .= '</div>';
		$html .= '</div>';
	}

	$html .= '</form>';

	return $html;
}

add_shortcode( 'ctd_enquiry_form', function( $atts ) {
	return ctd_render_enquiry_form( shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts ), 'page' );
} );

add_shortcode( 'ctd_enquiry_form_sidebar', function( $atts ) {
	return ctd_render_enquiry_form( shortcode_atts( array( 'post_id' => '', 'class' => '' ), $atts ), 'sidebar' );
} );

add_action( 'admin_post_nopriv_ctd_enquiry_submit', 'ctd_handle_enquiry_submit' );
add_action( 'admin_post_ctd_enquiry_submit', 'ctd_handle_enquiry_submit' );

/**
 * Handle an enquiry submission: validate, email the admin and the enquirer.
 */
function ctd_handle_enquiry_submit() {
	if ( ! isset( $_POST['ctd_enquiry_nonce'] ) || ! wp_verify_nonce( $_POST['ctd_enquiry_nonce'], 'ctd_enquiry_submit' ) ) {
		wp_redirect( add_query_arg( 'enquiry_status', 'failed', wp_get_referer() ) );
		exit;
	}

	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( $_POST['cf-turnstile-response'] ) : '';
	if ( ! ctd_turnstile_verify( $token ) ) {
		wp_redirect( add_query_arg( 'enquiry_status', 'captcha_failed', wp_get_referer() ) );
		exit;
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$trip_title = $post_id ? get_the_title( $post_id ) : '';
	$name    = isset( $_POST['ctd_name'] ) ? sanitize_text_field( $_POST['ctd_name'] ) : '';
	$email   = isset( $_POST['ctd_email'] ) ? sanitize_email( $_POST['ctd_email'] ) : '';
	$mobile  = isset( $_POST['ctd_mobile'] ) ? sanitize_text_field( $_POST['ctd_mobile'] ) : '';
	$city    = isset( $_POST['ctd_city'] ) ? sanitize_text_field( $_POST['ctd_city'] ) : '';
	$message = isset( $_POST['ctd_message'] ) ? sanitize_textarea_field( $_POST['ctd_message'] ) : '';

	if ( empty( $name ) || empty( $email ) ) {
		wp_redirect( add_query_arg( 'enquiry_status', 'invalid', wp_get_referer() ) );
		exit;
	}

	$settings = ctd_enquiry_get_settings();
	$headers  = array();

	if ( ! empty( $settings['from_email'] ) ) {
		$from      = $settings['from_name'] ? $settings['from_name'] : get_bloginfo( 'name' );
		$headers[] = 'From: ' . $from . ' <' . $settings['from_email'] . '>';
	}
	if ( ! empty( $settings['reply_to_email'] ) ) {
		$headers[] = 'Reply-To: ' . $settings['reply_to_email'];
	}
	$headers[] = 'Content-Type: text/html; charset=UTF-8';

	$admin_to = ! empty( $settings['admin_email'] ) ? $settings['admin_email'] : get_option( 'admin_email' );
	$subject  = $settings['subject'];

	$admin_body  = '<h2>New Trip Enquiry</h2>';
	$admin_body .= '<p><strong>Trip:</strong> ' . esc_html( $trip_title ) . '</p>';
	$admin_body .= '<p><strong>Name:</strong> ' . esc_html( $name ) . '</p>';
	$admin_body .= '<p><strong>Email:</strong> ' . esc_html( $email ) . '</p>';
	$admin_body .= '<p><strong>Mobile:</strong> ' . esc_html( $mobile ) . '</p>';
	if ( ! empty( $city ) ) {
		$admin_body .= '<p><strong>City:</strong> ' . esc_html( $city ) . '</p>';
	}
	$admin_body .= '<p><strong>Message:</strong><br>' . nl2br( esc_html( $message ) ) . '</p>';

	wp_mail( $admin_to, $subject, $admin_body, $headers );

	if ( $email ) {
		$user_body  = '<p>Hi ' . esc_html( $name ) . ',</p>';
		$user_body .= '<p>We have received your enquiry regarding "' . esc_html( $trip_title ) . '". Someone from our team will contact you soon.</p>';
		$user_body .= '<p>Regards,<br>' . esc_html( get_bloginfo( 'name' ) ) . '</p>';

		wp_mail( $email, 'We received your enquiry', $user_body, $headers );
	}

	wp_redirect( add_query_arg( 'enquiry_status', 'success', wp_get_referer() ) );
	exit;
}
