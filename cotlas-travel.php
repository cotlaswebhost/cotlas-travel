<?php
/**
 * Plugin Name: Cotlas Travel
 * Plugin URI:  https://github.com/cotlaswebhost/cotlas-travel
 * Description: Travel desk plugin for Cotlas Travel sites. Registers the Trip post type and taxonomies, trip meta boxes, trip shortcodes, GenerateBlocks dynamic tags and the trip enquiry form.
 * Version:     1.1.0
 * Author:      Vinay Shukla
 * Author URI:  https://cotlas.net/vinay404
 * Update URI:  https://api.github.com/repos/cotlaswebhost/cotlas-travel/releases/latest
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: cotlas-travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants.
define( 'CTD_VERSION', '1.1.0' );
define( 'CTD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// GitHub Updater - automatic updates from GitHub releases.
define( 'COTLAS_TRAVEL_FILE', __FILE__ );

// Include required files.
require_once CTD_PLUGIN_DIR . 'includes/github-updater.php';
require_once CTD_PLUGIN_DIR . 'includes/post-types.php';
require_once CTD_PLUGIN_DIR . 'includes/taxonomies.php';
require_once CTD_PLUGIN_DIR . 'includes/admin.php';
require_once CTD_PLUGIN_DIR . 'includes/metaboxes.php';
require_once CTD_PLUGIN_DIR . 'includes/shortcodes.php';
require_once CTD_PLUGIN_DIR . 'includes/dynamic-tags.php';
require_once CTD_PLUGIN_DIR . 'includes/admin-page.php';
require_once CTD_PLUGIN_DIR . 'includes/filters.php';
require_once CTD_PLUGIN_DIR . 'includes/trip-search.php';
require_once CTD_PLUGIN_DIR . 'includes/enquiry.php';
require_once CTD_PLUGIN_DIR . 'includes/class-ctd-pdf-generator.php';

// Initialize the plugin.
function ctd_init() {
    // Load text domain if needed.
    load_plugin_textdomain( 'cotlas-travel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ctd_init' );

// Enqueue admin scripts.
function ctd_enqueue_admin_scripts( $hook ) {
    global $post_type;

    // Enqueue on Taxonomy pages
    if ( 'term.php' === $hook || 'edit-tags.php' === $hook || ( isset( $_GET['page'] ) && $_GET['page'] === 'ctd-hero-settings' ) ) {
        wp_enqueue_media();
        wp_enqueue_script( 'ctd-admin-script', CTD_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), CTD_VERSION, true );
        wp_enqueue_style( 'ctd-admin-style', CTD_PLUGIN_URL . 'assets/css/admin.css', array(), CTD_VERSION );
    }

    // Enqueue on Post Edit pages for 'trip' CPT
    if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'trip' === $post_type ) {
        wp_enqueue_media(); // In case we need it later for metaboxes
        
        // Enqueue Select2
        wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
        wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

        wp_enqueue_script( 'ctd-admin-script', CTD_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'select2' ), CTD_VERSION, true );
        wp_enqueue_style( 'ctd-admin-style', CTD_PLUGIN_URL . 'assets/css/admin.css', array( 'select2' ), CTD_VERSION );

        // Enqueue Isolated Block Editor
        $deps = array(
            'wp-plugins',
            'wp-element',
            'wp-edit-post',
            'wp-i18n',
            'wp-api-request',
            'wp-data',
            'wp-hooks',
            'wp-components',
            'wp-blocks',
            'wp-editor',
            'wp-compose',
            'wp-block-editor'
        );
        wp_enqueue_script( 'ctd-isolated-block-editor', CTD_PLUGIN_URL . 'assets/js/isolated-block-editor.js', $deps, CTD_VERSION, true );
        // Enqueue WordPress default styles for blocks
        wp_enqueue_style( 'wp-block-library' );
        wp_enqueue_style( 'wp-block-library-theme' );
        wp_enqueue_style( 'wp-block-editor' );
        wp_enqueue_style( 'wp-components' );
        wp_enqueue_style( 'wp-editor' );
    }
}
add_action( 'admin_enqueue_scripts', 'ctd_enqueue_admin_scripts' );
