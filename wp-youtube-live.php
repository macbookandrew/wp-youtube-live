<?php
/*
Plugin Name: YouTube Live
Plugin URI: https://github.com/macbookandrew/wp-youtube-live
Description: Displays the current YouTube live video from a specified channel
Version: 1.0.0
Author: Andrew Minion
Author URI: https://andrewrminion.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

// Register scripts
add_action( 'wp_enqueue_scripts', 'youtube_live_enqueue_scripts' );
function youtube_live_enqueue_scripts() {
    wp_register_script( 'youtube-live-iframe', 'https://www.youtube.com/player_api', NULL, NULL, true );
    wp_register_script( 'youtube-live-player', plugins_url( '/js/youtube-live-player.min.js', __FILE__ ), array( 'youtube-live-iframe' ), NULL, true );
}

// Add settings page
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
}

// Print API Key field
function youtube_live_api_key_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_api_key]" placeholder="AIzaSyD4iE2xVSpkLLOXoyqT-RuPwURN3ddScAI" size="45" value="<?php echo $options['youtube_live_api_key']; ?>">

    <p>Don&rsquo;t have an API key?</p>
    <ol>
        <li>Go to the <a href="https://console.developers.google.com/apis/" target="_blank">Google APIs developers console</a> (create an account if necessary)</li>
        <li>Create a new project (if necessary)</li>
        <li>Enable the YouTube Data API v3</li>
        <li>Go to Credentials, click the blue button, and choose &ldquo;API key&rdquo;</li>
        <li>Enter referrers if you wish to limit use to your website(s) (recommended)</li>
        <li>Enter your API key above</li>
    </ol>

    <?php
}

// Print channel ID field
function youtube_live_channel_id_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <input type="text" name="youtube_live_settings[youtube_live_channel_id]" placeholder="UcZliPwLMjeJbhOAnr1Md4gA" size="45" value="<?php echo $options['youtube_live_channel_id']; ?>">

    <p>Go to <a href="https://youtube.com/account_advanced/" target="_blank">YouTube Advanced Settings</a> to find your YouTube Channel ID.</p>
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

// Add shortcode
add_shortcode( 'youtube_live', 'output_youtube_live' );
function output_youtube_live( $atts ) {
    // get shortcode attributes
    $shortcode_attributes = shortcode_atts( array (
        'not_live_text'     => NULL,
        'autoplay'          => false,
        'width'             => 640,
        'height'            => 360,
        'autoplay'          => 0,
        'modestbranding'    => 0,
        'playsinline'       => 0,
    ), $atts );

    // get saved options
    $youtube_options = get_option( 'youtube_live_settings' );
    $youtube_api_key = $youtube_options['youtube_live_api_key'];
    $youtube_channel_id = $youtube_options['youtube_live_channel_id'];
    $youtube_live_array = json_encode( array(
        'apiKey'            => $youtube_api_key,
        'channelId'         => $youtube_channel_id,
        'width'             => esc_attr( $shortcode_attributes['width'] ),
        'height'            => esc_attr( $shortcode_attributes['height'] ),
        'autoplay'          => ( esc_attr($shortcode_attributes['autoplay'] ) == true ) ? 1 : 0,
        'modestbranding'    => ( esc_attr( $shortcode_attributes['modestbranding'] ) == 'true' ) ? 1 : 0,
        'playsinline'       => ( esc_attr( $shortcode_attributes['playsinline'] ) == 'true' ) ? 1 : 0,
    ));

    // start output
    $shortcode_content = '<div id="youtube-live-player">Getting the latest video&hellip;</div>';
    wp_enqueue_script( 'youtube-live-player' );
    wp_add_inline_script( 'youtube-live-player', 'var youtubeLiveSettings = ' . $youtube_live_array );

    return $shortcode_content;
}
