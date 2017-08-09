<?php

if (!defined('ABSPATH')) {
    exit;
}

// Add settings page
/**
 * Enqueue backend assets
 */
function youtube_live_backend_assets() {
    wp_register_script( 'wp-youtube-live-backend', plugin_dir_url( __FILE__ ) . '../js/wp-youtube-live-backend.min.js', array( 'jquery' ), NULL, true );
}
add_action( 'admin_enqueue_scripts', 'youtube_live_backend_assets' );

add_action( 'admin_menu', 'youtube_live_add_admin_menu' );
add_action( 'admin_init', 'youtube_live_settings_init' );

// Add to menu
function youtube_live_add_admin_menu() {
    add_submenu_page( 'options-general.php', 'YouTube Live', 'YouTube Live Settings', 'manage_options', 'youtube-live', 'youtube_live_options_page' );
}

// Add settings section and fields
function youtube_live_settings_init() {
    register_setting( 'youtube_live_options', 'youtube_live_settings' );

    // API settings
    add_settings_section(
        'youtube_live_options_keys_section',
        __( 'YouTube Details', 'youtube_live' ),
        'youtube_live_api_settings_section_callback',
        'youtube_live_options'
    );

    add_settings_field(
        'youtube_live_api_key',
        __( 'YouTube API Key', 'youtube_live' ),
        'youtube_live_api_key_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_channel_id',
        __( 'YouTube Channel ID', 'youtube_live' ),
        'youtube_live_channel_id_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_subdomain',
        __( 'YouTube Subdomain', 'youtube_live' ),
        'youtube_live_subdomain_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_default_width',
        __( 'Default Player Width', 'youtube_live' ),
        'youtube_live_default_width_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_default_height',
        __( 'Default Player Height', 'youtube_live' ),
        'youtube_live_default_height_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'show_channel_if_dead',
        __( 'Show Channel Player', 'youtube_live' ),
        'youtube_live_show_channel_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'fallback_video',
        __( 'Show Fallback Video', 'youtube_live' ),
        'youtube_live_show_video_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'auto_refresh',
        __( 'Auto-Refresh', 'youtube_live' ),
        'youtube_live_auto_refresh_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );

    add_settings_field(
        'youtube_live_debugging',
        __( 'Debugging', 'youtube_live' ),
        'youtube_live_debugging_render',
        'youtube_live_options',
        'youtube_live_options_keys_section'
    );
}

// Print API Key field
function youtube_live_api_key_render() {
    wp_enqueue_script( 'wp-youtube-live-backend' );

    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_api_key]" placeholder="AIzaSyD4iE2xVSpkLLOXoyqT-RuPwURN3ddScAI" size="45" value="<?php echo $options['youtube_live_api_key']; ?>">

    <p>Don&rsquo;t have an API key?</p>
    <ol>
        <li>Go to the <a href="https://console.developers.google.com/apis/" target="_blank">Google APIs developers console</a> (create an account if necessary).</li>
        <li>Create a new project (if necessary).</li>
        <li>Enable the YouTube Data API v3.</li>
        <li>Go to Credentials, click the blue button, and choose &ldquo;API key&rdquo;.</li>
        <li>Enter referrers if you wish to limit use to your website(s) (highly recommended).</li>
        <li>Enter your API key above.</li>
    </ol>
    <p>See <a href="https://developers.google.com/youtube/registering_an_application" target="_blank">this page</a> for more information.</p>

    <?php
}

// Print channel ID field
function youtube_live_channel_id_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_channel_id]" placeholder="UcZliPwLMjeJbhOAnr1Md4gA" size="45" value="<?php echo $options['youtube_live_channel_id']; ?>">

    <p>Go to <a href="https://youtube.com/account_advanced/" target="_blank">YouTube Advanced Settings</a> to find your YouTube Channel ID.</p>
    <?php
}

// Print subdomain field
function youtube_live_subdomain_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <label><select name="youtube_live_settings[subdomain]">
        <option value="www" <?php selected( $options['subdomain'], 'www' ); ?>>Default (www.youtube.com)</option>
        <option value="gaming" <?php selected( $options['subdomain'], 'gaming' ); ?>>Gaming (gaming.youtube.com)</option>
    </select></label>
    <?php
}

// Print default width field
function youtube_live_default_width_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'default_width', $options ) || is_null( $options['default_width'] ) ) {
        $options['default_width'] = 720;
    }
    ?>
    <input type="number" name="youtube_live_settings[default_width]" placeholder="720" value="<?php echo $options['default_width']; ?>">
    <?php
}

// Print default height field
function youtube_live_default_height_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'default_height', $options ) || is_null( $options['default_height'] ) ) {
        $options['default_height'] = 480;
    }
    ?>
    <input type="number" name="youtube_live_settings[default_height]" placeholder="480" value="<?php echo $options['default_height']; ?>">
    <?php
}

// Print show channel field
function youtube_live_show_channel_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'show_channel_if_dead', $options ) ) {
        $options['show_channel_if_dead'] = false;
    }
    ?>
    If you are not live-streaming, show a player with your recent videos? <label><input type="radio" name="youtube_live_settings[show_channel_if_dead]" value="true" <?php checked( $options['show_channel_if_dead'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[show_channel_if_dead]" value="false" <?php checked( $options['show_channel_if_dead'], 'false' ); ?>> No</label>
    <?php
}

// Print auto-refresh field
/**
 * Print fallback video field
 */
function youtube_live_show_video_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'fallback_video', $options ) ) {
        $options['fallback_video'] = false;
    }
    ?>
    If you are not live-streaming, show this video instead: <label><input type="text" name="youtube_live_settings[fallback_video]" size="60" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ" value="<?php echo $options['fallback_video']; ?>"></label>
    <?php
}

function youtube_live_auto_refresh_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'auto_refresh', $options ) ) {
        $options['auto_refresh'] = false;
    }
    ?>
    Should the player page automatically check every 30 seconds until a live video is available? <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="true" <?php checked( $options['auto_refresh'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="false" <?php checked( $options['auto_refresh'], 'false' ); ?>> No</label>
    <p><strong>Warning:</strong> depending on how many users are on the page, this may overload your server with requests.</p>
    <?php
}

// Print debugging field
function youtube_live_debugging_render() {
    $options = get_option( 'youtube_live_settings' );
    if ( ! array_key_exists( 'debugging', $options ) ) {
        $options['debugging'] = false;
    }
    ?>
    Show debugging information in an HTML comment for logged-in users? <label><input type="radio" name="youtube_live_settings[debugging]" value="true" <?php checked( $options['debugging'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[debugging]" value="false" <?php checked( $options['debugging'], 'false' ); ?>> No</label>
    <?php
}

// Print API settings description
function youtube_live_api_settings_section_callback() {
    echo __( 'Enter your YouTube details below. Once you&rsquo;ve entered the required details below, add the shortcode <code>[youtube_live]</code> to any post/page to display the live player.', 'youtube_live' );
}

// Print form
function youtube_live_options_page() { ?>
    <div class="wrap">
        <form action="options.php" method="post">
            <?php
            settings_fields( 'youtube_live_options' );
            do_settings_sections( 'youtube_live_options' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
