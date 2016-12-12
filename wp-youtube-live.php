<?php
/*
Plugin Name: YouTube Live
Plugin URI: https://github.com/macbookandrew/wp-youtube-live
Description: Displays the current YouTube live video from a specified channel
Version: 1.4.1
Author: Andrew Minion
Author URI: https://andrewrminion.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

CONST WP_YOUTUBE_LIVE_VERSION = '1.4';

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

// Print debugging field
function youtube_live_debugging_render() {
    $options = get_option( 'youtube_live_settings' ); ?>
    <label><input type="checkbox" name="youtube_live_settings[debugging]" value="true" <?php checked( $options['debugging'], 'true' ); ?>> Show debugging information in an HTML comment for logged-in users?</label>
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

// Add assets
add_action( 'wp_enqueue_scripts', 'youtube_live_scripts' );
function youtube_live_scripts() {
    wp_register_script( 'wp-youtube-live', plugin_dir_url( __FILE__ ) . 'js/wp-youtube-live.min.js', array( 'jquery' ), WP_YOUTUBE_LIVE_VERSION, true );
    wp_register_style( 'wp-youtube-live', plugin_dir_url( __FILE__ ) . 'css/wp-youtube-live.css', array(), WP_YOUTUBE_LIVE_VERSION );
}

// Add shortcode
add_shortcode( 'youtube_live', 'output_youtube_live' );
function output_youtube_live( $atts ) {
    // enqueue asstes
    wp_enqueue_script( 'wp-youtube-live' );
    wp_enqueue_style( 'wp-youtube-live' );

    // get shortcode attributes
    $shortcode_attributes = shortcode_atts( array (
        'width'     => 640,
        'height'    => 360,
        'autoplay'  => 0,
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
    ), $atts );

    wp_add_inline_script( 'wp-youtube-live', 'var wpYouTubeLive = ' . json_encode( $shortcode_attributes ) );

    return get_youtube_live_content( $shortcode_attributes );
}

// Add Ajax handler
add_action( 'wp_ajax_load_youtube_live', 'get_youtube_live_content' );
add_action( 'wp_ajax_nopriv_load_youtube_live', 'get_youtube_live_content' );

// Output YouTube Live embed code
function get_youtube_live_content( $youtube_settings ) {
    // load embed class
    require_once( 'inc/EmbedYoutubeLiveStreaming.php' );

    // get saved options
    $youtube_options = get_option( 'youtube_live_settings' );

    // set up player
    $youtube_live = new EmbedYoutubeLiveStreaming( $youtube_options['youtube_live_channel_id'], $youtube_options['youtube_live_api_key'] );
    $youtube_live->embed_width = ( $_POST['isAjax'] ? esc_attr( $_POST['width'] ) : $youtube_settings['width'] );
    $youtube_live->embed_height = ( $_POST['isAjax'] ? esc_attr( $_POST['height'] ) : $youtube_settings['height'] );
    $youtube_live->embed_autoplay = ( $_POST['isAjax'] ? esc_attr( $_POST['autoplay'] ) : $youtube_settings['autoplay'] );

    // start output
    ob_start();
    echo '<span class="wp-youtube-live">';
    if ( $youtube_live->isLive ) {
        echo $youtube_live->embedCode();
    } else {
        echo apply_filters( 'wp_youtube_live_no_stream_available', '<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target="_blank" href="https://youtube.com/channel/' . $youtube_options['youtube_live_channel_id'] . '">all our videos</a>.</p>
        <p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>' );
    }

    // debugging
    $debugging_code = var_export( $youtube_live, true );
    if ( get_option( 'youtube_live_settings', 'debugging' ) && is_user_logged_in() ) {
        echo '<!-- YouTube Live debugging: ' . "\n" . $debugging_code . "\n" . ' -->';
    }
    echo '</span>';

    // handle ajax
    if ( $_POST['isAjax'] ) {
        echo ob_get_clean();
        wp_die();
    } else {
        return ob_get_clean();
    }
}
