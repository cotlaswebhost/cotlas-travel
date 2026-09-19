<?php
/**
 * Admin Page for Plugin Documentation & Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Admin Menus
 */
function ctd_register_admin_menus() {
    // Main Menu
    add_menu_page(
        __( 'Cotlas Travel', 'cotlas-travel' ),
        __( 'Cotlas Travel', 'cotlas-travel' ),
        'manage_options',
        'ctd-dashboard',
        'ctd_render_dashboard_page',
        'dashicons-airplane', // Or custom icon
        2
    );

    // Submenu: Dashboard (Default)
    add_submenu_page(
        'ctd-dashboard',
        __( 'Dashboard', 'cotlas-travel' ),
        __( 'Dashboard', 'cotlas-travel' ),
        'manage_options',
        'ctd-dashboard',
        'ctd_render_dashboard_page'
    );

    // Submenu: Plugin Home
    add_submenu_page(
        'ctd-dashboard',
        __( 'Plugin Home', 'cotlas-travel' ),
        __( 'Plugin Home', 'cotlas-travel' ),
        'manage_options',
        'ctd-plugin-home',
        'ctd_render_plugin_home_page'
    );

    // Submenu: Shortcode Info
    add_submenu_page(
        'ctd-dashboard',
        __( 'Shortcode Info', 'cotlas-travel' ),
        __( 'Shortcode Info', 'cotlas-travel' ),
        'manage_options',
        'ctd-shortcode-info',
        'ctd_render_shortcode_info_page'
    );

    // Submenu: Dynamic Tags Info
    add_submenu_page(
        'ctd-dashboard',
        __( 'Dynamic Tags Info', 'cotlas-travel' ),
        __( 'Dynamic Tags Info', 'cotlas-travel' ),
        'manage_options',
        'ctd-dynamic-tags-info',
        'ctd_render_dynamic_tags_info_page'
    );

    // Submenu: Hero Settings
    add_submenu_page(
        'ctd-dashboard',
        __( 'Hero Settings', 'cotlas-travel' ),
        __( 'Hero Settings', 'cotlas-travel' ),
        'manage_options',
        'ctd-hero-settings',
        'ctd_render_hero_settings_page'
    );
}
add_action( 'admin_menu', 'ctd_register_admin_menus' );

/**
 * Register Settings
 */
function ctd_register_settings() {
    register_setting( 'ctd_hero_settings_group', 'ctd_hero_image_1' );
    register_setting( 'ctd_hero_settings_group', 'ctd_hero_image_2' );
    register_setting( 'ctd_hero_settings_group', 'ctd_hero_image_3' );
    register_setting( 'ctd_hero_settings_group', 'ctd_hero_image_4' );
}
add_action( 'admin_init', 'ctd_register_settings' );

/**
 * Render Hero Settings Page
 */
function ctd_render_hero_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Hero Section Settings', 'cotlas-travel' ); ?></h1>
        <div class="ctd-admin-content" style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <form method="post" action="options.php">
                <?php settings_fields( 'ctd_hero_settings_group' ); ?>
                <?php do_settings_sections( 'ctd_hero_settings_group' ); ?>
                
                <table class="form-table">
                    <?php for ( $i = 1; $i <= 4; $i++ ) : 
                        $option_name = 'ctd_hero_image_' . $i;
                        $image_id = get_option( $option_name );
                        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php printf( __( 'Hero Image %d', 'cotlas-travel' ), $i ); ?></th>
                        <td>
                            <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $image_id ); ?>" />
                            <div id="<?php echo esc_attr( $option_name ); ?>_preview" style="margin-bottom: 10px;">
                                <?php if ( $image_url ) : ?>
                                    <img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; height: auto;" />
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button ctd-upload-hero-image" data-target="#<?php echo esc_attr( $option_name ); ?>" data-preview="#<?php echo esc_attr( $option_name ); ?>_preview"><?php _e( 'Upload/Choose Image', 'cotlas-travel' ); ?></button>
                            <button type="button" class="button ctd-remove-hero-image" data-target="#<?php echo esc_attr( $option_name ); ?>" data-preview="#<?php echo esc_attr( $option_name ); ?>_preview" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'cotlas-travel' ); ?></button>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Render Dashboard Page
 */
function ctd_render_dashboard_page() {
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'about';
    ?>
    <div class="wrap">
        <h1><?php _e( 'Cotlas Travel - Dashboard', 'cotlas-travel' ); ?></h1>
        
        <nav class="nav-tab-wrapper">
            <a href="?page=ctd-dashboard&tab=about" class="nav-tab <?php echo $active_tab == 'about' ? 'nav-tab-active' : ''; ?>"><?php _e( 'About Us', 'cotlas-travel' ); ?></a>
            <a href="?page=ctd-dashboard&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Support', 'cotlas-travel' ); ?></a>
        </nav>
        
        <div class="ctd-admin-content" style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <?php if ( $active_tab == 'about' ) : ?>
                <h2><?php _e( 'Welcome to Cotlas Travel Desk', 'cotlas-travel' ); ?></h2>
                <p><?php _e( 'Cotlas Travel Desk is your all-in-one solution for managing trips, itineraries, and bookings.', 'cotlas-travel' ); ?></p>
                <p><?php _e( 'Developed by Cotlas Team.', 'cotlas-travel' ); ?></p>
            
            <?php elseif ( $active_tab == 'support' ) : ?>
                <h2><?php _e( 'Support', 'cotlas-travel' ); ?></h2>
                <p><?php _e( 'Need help? Fill out the form below or contact us directly.', 'cotlas-travel' ); ?></p>
                
                <form action="" method="post" style="max-width: 600px;">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ctd_name"><?php _e( 'Name', 'cotlas-travel' ); ?></label></th>
                            <td><input type="text" name="ctd_name" id="ctd_name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ctd_email"><?php _e( 'Email', 'cotlas-travel' ); ?></label></th>
                            <td><input type="email" name="ctd_email" id="ctd_email" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ctd_message"><?php _e( 'Message', 'cotlas-travel' ); ?></label></th>
                            <td><textarea name="ctd_message" id="ctd_message" rows="5" class="large-text" required></textarea></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="ctd_support_submit" id="submit" class="button button-primary" value="<?php _e( 'Send Message', 'cotlas-travel' ); ?>">
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Render Plugin Home Page
 */
function ctd_render_plugin_home_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Plugin Home', 'cotlas-travel' ); ?></h1>
        <div class="ctd-admin-content" style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e( 'Getting Started', 'cotlas-travel' ); ?></h2>
            <p><?php _e( 'Welcome to the Cotlas Travel Desk plugin. Use the menu on the left to navigate through your trips, bookings, and settings.', 'cotlas-travel' ); ?></p>
            <ul>
                <li><a href="<?php echo admin_url('edit.php?post_type=trip'); ?>"><?php _e( 'Manage Trips', 'cotlas-travel' ); ?></a></li>
                <li><a href="<?php echo admin_url('edit-tags.php?taxonomy=destination&post_type=trip'); ?>"><?php _e( 'Destinations', 'cotlas-travel' ); ?></a></li>
                <li><a href="<?php echo admin_url('edit-tags.php?taxonomy=activities&post_type=trip'); ?>"><?php _e( 'Activities', 'cotlas-travel' ); ?></a></li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Render Shortcode Info Page
 */
function ctd_render_shortcode_info_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Shortcode Information', 'cotlas-travel' ); ?></h1>
        <div class="ctd-admin-content" style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e( 'Available Shortcodes', 'cotlas-travel' ); ?></h2>
            <p><?php _e( 'Use these shortcodes to display trip data in your content or templates. All shortcodes support the <code>class</code> attribute to add custom CSS classes.', 'cotlas-travel' ); ?></p>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Shortcode', 'cotlas-travel' ); ?></th>
                        <th><?php _e( 'Description', 'cotlas-travel' ); ?></th>
                        <th><?php _e( 'Attributes', 'cotlas-travel' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[ctd_trip_data field="FIELD_NAME"]</code></td>
                        <td><?php _e( 'Displays raw value of any meta field.', 'cotlas-travel' ); ?></td>
                        <td><code>field</code> (required), <code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_meals]</code></td>
                        <td><?php _e( 'Displays the meals list (icon style).', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_highlights]</code></td>
                        <td><?php _e( 'Displays the list of highlights (Checkmark style).', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_itinerary]</code></td>
                        <td><?php _e( 'Displays the itinerary (Timeline style).', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_faqs]</code></td>
                        <td><?php _e( 'Displays the FAQs (Accordion style).', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code>, <code>expand_all</code> (true/false)</td>
                    </tr>
                    <tr>
                         <td><code>[ctd_pricing_table]</code></td>
                         <td><?php _e( 'Displays the pricing packages table.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_trip_facts]</code></td>
                         <td><?php _e( 'Displays the trip facts grid.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_gallery]</code></td>
                         <td><?php _e( 'Displays the image gallery.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_video_gallery]</code></td>
                         <td><?php _e( 'Displays the video gallery.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_downloads]</code></td>
                         <td><?php _e( 'Displays the downloadable files list.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_extra_services]</code></td>
                         <td><?php _e( 'Displays the extra services list.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                         <td><code>[ctd_overview]</code></td>
                         <td><?php _e( 'Displays Overview Title + Content.', 'cotlas-travel' ); ?></td>
                         <td><code>post_id</code>, <code>class</code></td>
                     </tr>
                     <tr>
                        <td><code>[ctd_more_info]</code></td>
                        <td><?php _e( 'Displays More Info Title + Content.', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_booking_url]</code></td>
                        <td><?php _e( 'Displays the Custom Booking Link (only if Booking is enabled).', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_itinerary_pdf]</code></td>
                        <td><?php _e( 'Displays the URL of the uploaded Itinerary PDF.', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_term_data field="FIELD_NAME"]</code></td>
                        <td><?php _e( 'Displays taxonomy term data (Title, Description, Image, etc.).', 'cotlas-travel' ); ?></td>
                        <td><code>field</code> (required), <code>term_id</code>, <code>taxonomy</code>, <code>class</code>, <code>image_size</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_outbound_flight field="FIELD"]</code></td>
                        <td><?php _e( 'Displays outbound flight data.', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code>, <code>field</code> (airline|from|departure_time|to|arrival_time)</td>
                    </tr>
                    <tr>
                        <td><code>[ctd_inbound_flight field="FIELD"]</code></td>
                        <td><?php _e( 'Displays inbound flight data.', 'cotlas-travel' ); ?></td>
                        <td><code>post_id</code>, <code>class</code>, <code>field</code> (airline|from|departure_time|to|arrival_time)</td>
                    </tr>
                    <tr>
                        <td><code>[ctd_search_bar]</code></td>
                        <td><?php _e( 'Displays the Trip Search Bar with Keyword, Destination, and Activity fields.', 'cotlas-travel' ); ?></td>
                        <td><code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_filter_sidebar]</code></td>
                        <td><?php _e( 'Displays the Filter Sidebar (Price, Duration, Taxonomies).', 'cotlas-travel' ); ?></td>
                        <td><code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_archive_search]</code></td>
                        <td><?php _e( 'Displays the Archive Search Bar (Keyword, Destination, Activity).', 'cotlas-travel' ); ?></td>
                        <td><code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_sort_dropdown]</code></td>
                        <td><?php _e( 'Displays the Sort Order dropdown.', 'cotlas-travel' ); ?></td>
                        <td><code>class</code></td>
                    </tr>
                    <tr>
                        <td><code>[ctd_hero_image index="1"]</code></td>
                        <td><?php _e( 'Displays the Hero Image from settings.', 'cotlas-travel' ); ?></td>
                        <td><code>index</code> (1-4), <code>class</code></td>
                    </tr>
                </tbody>
            </table>

            <hr style="margin: 30px 0;">

            <h2><?php _e( 'Setup Guide for Your Template', 'cotlas-travel' ); ?></h2>
            <div style="background: #f9f9f9; padding: 20px; border-left: 4px solid #0073aa;">
                <h3><?php _e( '1. Sidebar (Desktop)', 'cotlas-travel' ); ?></h3>
                <p><?php _e( 'Place <code>[ctd_filter_sidebar]</code> in your left sidebar container.', 'cotlas-travel' ); ?></p>

                <h3><?php _e( '2. Mobile Off-Canvas', 'cotlas-travel' ); ?></h3>
                <p><?php _e( 'Place <code>[ctd_filter_sidebar]</code> inside your mobile off-canvas container/popup.', 'cotlas-travel' ); ?></p>

                <h3><?php _e( '3. Main Content Top Bar', 'cotlas-travel' ); ?></h3>
                <p><?php _e( 'Use a Grid/Flex container:', 'cotlas-travel' ); ?></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong><?php _e( 'Left Col:', 'cotlas-travel' ); ?></strong> <code>[ctd_archive_search]</code></li>
                    <li><strong><?php _e( 'Right Col:', 'cotlas-travel' ); ?></strong> <code>[ctd_sort_dropdown]</code></li>
                    <li><?php _e( '(Add your "Filter" button for mobile that triggers the off-canvas).', 'cotlas-travel' ); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Dynamic Tags Info Page
 */
function ctd_render_dynamic_tags_info_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Dynamic Tags Information', 'cotlas-travel' ); ?></h1>
        <div class="ctd-admin-content" style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e( 'GenerateBlocks Dynamic Data Integration', 'cotlas-travel' ); ?></h2>
            <p><?php _e( 'This plugin integrates with GenerateBlocks Pro Dynamic Data. You can use the "Dynamic Data" option in Headline, Button, and Container blocks.', 'cotlas-travel' ); ?></p>
            
            <h3><?php _e( 'How to use:', 'cotlas-travel' ); ?></h3>
            <ol>
                <li><?php _e( 'Select a GenerateBlocks block (e.g., Headline).', 'cotlas-travel' ); ?></li>
                <li><?php _e( 'Enable "Dynamic Data" in the block settings.', 'cotlas-travel' ); ?></li>
                <li><?php _e( 'In "Data Source", choose <strong>Current Post</strong>.', 'cotlas-travel' ); ?></li>
                <li><?php _e( 'In "Content Source", you will see two new options under "Post Meta":', 'cotlas-travel' ); ?>
                    <ul style="list-style: disc; margin-left: 20px; margin-top: 10px;">
                        <li><strong><?php _e( 'Trip Meta Data', 'cotlas-travel' ); ?></strong>: <?php _e( 'Use this for single values like Trip Code, Duration, Titles, Descriptions, etc.', 'cotlas-travel' ); ?></li>
                        <li><strong><?php _e( 'Trip Component', 'cotlas-travel' ); ?></strong>: <?php _e( 'Use this to inject complex components like the Itinerary, Pricing Table, or Gallery.', 'cotlas-travel' ); ?></li>
                    </ul>
                </li>
            </ol>
            
            <h3><?php _e( 'Available Dynamic Fields', 'cotlas-travel' ); ?></h3>
            <p><?php _e( 'When you select "Trip Meta Data", you can choose from the following fields:', 'cotlas-travel' ); ?></p>
            <ul style="columns: 2;">
                <li>Trip Code</li>
                <li>Duration (Days/Nights)</li>
                <li>Min/Max Age</li>
                <li>Min Travellers</li>
                <li>Total Seats</li>
                <li>Overview Title & Content</li>
                <li>Highlights Title</li>
                <li>Itinerary Title & Description</li>
                <li>Includes/Excludes Titles & Content</li>
                <li>Trip Info Title</li>
                <li>Map Title & Iframe</li>
                <li>Extra Services Title & Description</li>
                <li>More Info Title & Content</li>
                <li>Meals Section Title</li>
                <li>Custom Booking Link</li>
                <li>Itinerary PDF URL</li>
                <li>Flights Section Title</li>
                <li>Search Bar (Trip Component)</li>
                <li>Hero Image (1-4)</li>
            </ul>
            
            <hr style="margin: 20px 0;">

            <h3><?php _e( 'Repeater Field Access (Advanced)', 'cotlas-travel' ); ?></h3>
            <p><?php _e( 'You can also access specific items within a repeater field (e.g., FAQ #1, Itinerary Day #2) using the following Dynamic Tags:', 'cotlas-travel' ); ?></p>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Tag Name', 'cotlas-travel' ); ?></th>
                        <th><?php _e( 'Fields', 'cotlas-travel' ); ?></th>
                        <th><?php _e( 'Usage', 'cotlas-travel' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php _e( 'Trip FAQ Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Question, Answer</td>
                        <td>Select the "Item Number" (e.g., 1 for the first FAQ, 2 for the second).</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Itinerary Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Day Title, Description, Day Label, Day Image (URL)</td>
                        <td>Use "Item Number" to target a specific day (e.g., 1 for Day 1).</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Service Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Service Name, Price, Description</td>
                        <td>Use "Item Number" to target a specific service.</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Fact Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Label, Value, Image (Icon)</td>
                        <td>Use "Item Number" to target a specific fact.</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Package Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Title, Description, Price</td>
                        <td>Use "Package Number" to target a package. <br><em>Note: For "Price", you must also provide the "Price Category ID".</em></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Meal Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Value</td>
                        <td>Use "Item Number" to target a specific meal detail.</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Taxonomy Term Data', 'cotlas-travel' ); ?></strong></td>
                        <td>Title, Description, Image URL, URL, Count</td>
                        <td>Use this on archive pages or to fetch specific term data.</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e( 'Trip Flight Info', 'cotlas-travel' ); ?></strong></td>
                        <td>Airline, From, Departure Time, To, Arrival Time</td>
                        <td>Select Direction (Outbound/Inbound) and Field.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
