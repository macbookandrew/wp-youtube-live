<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'EmbedYoutubeLiveStreaming.php';

/**
 * Enqueue backend assets
 */
function youtube_live_backend_assets() {
	wp_enqueue_script( 'wp-youtube-live-backend', plugin_dir_url( __FILE__ ) . '../js/wp-youtube-live-backend.min.js', array( 'jquery' ), WP_YOUTUBE_LIVE_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'youtube_live_backend_assets' );

/**
 * Add settings page
 */
add_action( 'admin_menu', 'youtube_live_add_admin_menu' );
add_action( 'admin_init', 'youtube_live_settings_init' );

/**
 * Add settings page to admin menu
 */
function youtube_live_add_admin_menu() {
	add_submenu_page( 'options-general.php', 'YouTube Live', 'YouTube Live Settings', 'manage_options', 'youtube-live', 'youtube_live_options_page' );
}

/**
 * Add settings section and fields
 */
function youtube_live_settings_init() {
	register_setting( 'youtube_live_options', 'youtube_live_settings' );

	// API settings.
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
		'youtube_live_player_settings',
		__( 'Default Player Settings', 'youtube_live' ),
		'youtube_live_player_settings_render',
		'youtube_live_options',
		'youtube_live_options_keys_section'
	);

	add_settings_field(
		'fallback_behavior',
		__( 'Fallback Behavior', 'youtube_live' ),
		'fallback_behavior_render',
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
		'transient_timeout',
		__( 'Transient Timeout and Check Frequency', 'youtube_live' ),
		'youtube_live_transient_timeout_render',
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

	add_settings_field(
		'youtube_live_tools',
		__( 'Tools', 'youtube_live' ),
		'youtube_live_tools_render',
		'youtube_live_options',
		'youtube_live_options_keys_section'
	);

	add_settings_field(
		'youtube_live_terms',
		__( 'Terms of Service and Privacy Policy', 'youtube_live' ),
		'youtube_live_terms_render',
		'youtube_live_options',
		'youtube_live_options_keys_section'
	);
}

/**
 * Print API Key field
 */
function youtube_live_api_key_render() {
	$options = get_option( 'youtube_live_settings' ); ?>
	<input type="text" name="youtube_live_settings[youtube_live_api_key]" placeholder="AIzaSyD4iE2xVSpkLLOXoyqT-RuPwURN3ddScAI" size="45" value="<?php echo esc_attr( $options['youtube_live_api_key'] ); ?>">

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

/**
 * Print Channel ID field
 */
function youtube_live_channel_id_render() {
	$options = get_option( 'youtube_live_settings' );
	?>
	<input type="text" name="youtube_live_settings[youtube_live_channel_id]" placeholder="UcZliPwLMjeJbhOAnr1Md4gA" size="45" value="<?php echo esc_attr( $options['youtube_live_channel_id'] ); ?>">

	<p>Go to <a href="https://youtube.com/account_advanced/" target="_blank">YouTube Advanced Settings</a> to find your YouTube Channel ID.</p>
	<?php
}

/**
 * Print subdomain field
 */
function youtube_live_subdomain_render() {
	$options = get_option( 'youtube_live_settings', array( 'subdomain' => 'www' ) );
	?>
	<label><select name="youtube_live_settings[subdomain]">
		<option value="www" <?php selected( $options['subdomain'], 'www' ); ?>>Default (www.youtube.com)</option>
		<option value="gaming" <?php selected( $options['subdomain'], 'gaming' ); ?>>Gaming (gaming.youtube.com)</option>
	</select></label>
	<?php
}

/**
 * Print player settings fields
 */
function youtube_live_player_settings_render() {
	$options = get_option( 'youtube_live_settings' );
	if ( ! array_key_exists( 'default_width', $options ) || is_null( $options['default_width'] ) ) {
		$options['default_width'] = 720;
	}
	if ( ! array_key_exists( 'default_height', $options ) || is_null( $options['default_height'] ) ) {
		$options['default_height'] = 480;
	}
	if ( ! array_key_exists( 'autoplay', $options ) ) {
		$options['autoplay'] = true;
	}
	if ( ! array_key_exists( 'show_related', $options ) ) {
		$options['show_related'] = false;
	}
	?>
	<p>
		<label>Width: <input type="number" name="youtube_live_settings[default_width]" placeholder="720" value="<?php echo esc_attr( $options['default_width'] ); ?>">px</label><br/>
		<label>Height: <input type="number" name="youtube_live_settings[default_height]" placeholder="480" value="<?php echo esc_attr( $options['default_height'] ); ?>">px</label>
	</p>
	<p>
		Should the player auto-play when a live video is available? <label><input type="radio" name="youtube_live_settings[autoplay]" value="true" <?php checked( $options['autoplay'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[autoplay]" value="false" <?php checked( $options['autoplay'], 'false' ); ?>> No</label><br/>
		<span style="font-size: 85%;">Note: if this is not working correctly for you, please read <a href="https://developers.google.com/web/updates/2017/09/autoplay-policy-changes" target="_blank">this note</a> about Google Chrome&rsquo;s autoplay policies.</span>
	</p>
	<p>
		Should the player show related videos when a video finishes? <label><input type="radio" name="youtube_live_settings[show_related]" value="true" <?php checked( $options['show_related'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[show_related]" value="false" <?php checked( $options['show_related'], 'false' ); ?>> No</label>
	</p>
	<?php
}

/**
 * Print fallback behavior fields
 */
function fallback_behavior_render() {
	$options = get_option( 'youtube_live_settings' );
	if ( ! array_key_exists( 'fallback_behavior', $options ) ) {
		$options['fallback_behavior'] = 'message';
	}
	if ( ! array_key_exists( 'fallback_message', $options ) ) {
		$options['fallback_message'] = '<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target="_blank" href="' . esc_url( 'https://youtube.com/channel/' . $options['youtube_live_channel_id'] ) . '">all of our videos</a>.</p>
<p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
	}
	?>
	<p>
		<label for="youtube_live_settings[fallback_behavior]">If no live videos are available, what should be displayed?</label>
		<select name="youtube_live_settings[fallback_behavior]">
			<option value="message" <?php selected( esc_attr( $options['fallback_behavior'] ), 'message' ); ?>>Show a custom HTML message (no additional quota cost)</option>
			<option value="upcoming" <?php selected( esc_attr( $options['fallback_behavior'] ), 'upcoming' ); ?>>Show scheduled live videos (adds a quota unit cost of 100)</option>
			<option value="completed" <?php selected( esc_attr( $options['fallback_behavior'] ), 'completed' ); ?>>Show last completed live video (adds a quota unit cost of 100)</option>
			<option value="channel" <?php selected( esc_attr( $options['fallback_behavior'] ), 'channel' ); ?>>Show recent videos from my channel (adds a quota unit cost of at least 3)</option>
			<option value="playlist" <?php selected( esc_attr( $options['fallback_behavior'] ), 'playlist' ); ?>>Show a specified playlist (adds a quota unit cost of at least 3)</option>
			<option value="video" <?php selected( esc_attr( $options['fallback_behavior'] ), 'video' ); ?>>Show a specified video (no additional quota cost)</option>
			<option value="no_message" <?php selected( esc_attr( $options['fallback_behavior'] ), 'no_message' ); ?>>Show nothing at all (no additional quota cost)</option>
		</select>
	</p>

	<p class="fallback message">
		<label for="youtube_live_settings[fallback_message]">Custom HTML message:</label><br/>
		<textarea cols="50" rows="8" name="youtube_live_settings[fallback_message]" placeholder="<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target='_blank' href='<?php echo esc_url( 'https://youtube.com/channel/' . $options['youtube_live_channel_id'] ); ?>'>all of our videos</a>.</p>
		<p><button type='button' class='button' id='check-again'>Check again</button><span class='spinner' style='display:none;'></span></p>."><?php echo wp_kses_post( $options['fallback_message'] ); ?></textarea>
	</p>

	<div class="fallback upcoming">
		<p>This option will fetch all your upcoming scheduled live videos from the YouTube API and cache them for 24 hours or until the first video is scheduled to begin, whichever is soonest. If you schedule more live videos, press the button below to manually flush the server’s cache. <strong>Note:</strong> if you have no upcoming scheduled videos, the last scheduled video will be shown instead.</p>

		<?php
		$upcoming_cache = get_transient( 'youtube-live-upcoming-videos' );
		if ( false === $upcoming_cache ) {
			$upcoming_cache = json_decode( refresh_youtube_live_upcoming_cache( 'updatewpYTUpcomingCache', wp_create_nonce( 'wpYTcache_nonce' ) ) );
		}
		?>

		<div class="wp-youtube-live-upcoming-cache"><?php echo wp_kses_post( format_upcoming_videos( $upcoming_cache ) ); ?></div>

		<p>
			<button type="button" class="button-primary" id="updatewpYTUpcomingCache" data-action="updatewpYTUpcomingCache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpYTcache_nonce' ) ); ?>">Clear Cached Upcoming Videos</button> (costs 100 quota units each time)<span class="spinner" style="visibility: hidden;float: none;"></span>
		</p>
		<!-- TODO: add secondary fallback if no upcoming videos are scheduled -->
	</div>

	<p class="fallback playlist">
		<label for="youtube_live_settings[fallback_playlist]">Fallback Playlist URL:</label><br/>
		<input type="text" name="youtube_live_settings[fallback_playlist]" size="45" placeholder="https://www.youtube.com/watch?v=abc123…&list=PLABC123…" value="<?php echo esc_attr( $options['fallback_playlist'] ); ?>" />
	</p>

	<p class="fallback video">
		<label for="youtube_live_settings[fallback_video]">Fallback Video URL:</label><br/>
		<input type="text" name="youtube_live_settings[fallback_video]" size="45" placeholder="https://youtu.be/dQw4w9WgXcQ" value="<?php echo esc_attr( $options['fallback_video'] ); ?>" />
	</p>

	<p>For more information on quota usage, read the <a href="https://github.com/macbookandrew/wp-youtube-live#quota-units">plugin documentation</a> as well as the <a href="https://developers.google.com/youtube/v3/getting-started#quota" target="_blank">YouTube API documentation</a>.</p>
	<?php
}

/**
 * Print auto-refresh field
 */
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

/**
 * Print transient timeout field
 */
function youtube_live_transient_timeout_render() {
	$options = get_option( 'youtube_live_settings' );
	if ( ! array_key_exists( 'transient_timeout', $options ) ) {
		$options['transient_timeout'] = 900;
	}
	?>
	<p id="transient-timeout"><label><input type="number" name="youtube_live_settings[transient_timeout]" placeholder="900" value="<?php echo esc_attr( $options['transient_timeout'] ); ?>"> seconds</label></p>
	<p>YouTube enforces a daily limit on API usage. To stay within this limit, the plugin caches the YouTube response for this many seconds.</p>
	<p>A value of 900 (15 minutes) should stay pretty close to the default daily quota. If you have low or no traffic during “off hours” (when you’re not likely to be broadcasting a live event), you may want to experiment and set this lower, since the quota won’t be consumed as much during the off hours.</p>
	<p>To see your actual quota usage in real time, visit the <a href="https://console.developers.google.com/apis/api/youtube/usage">API Usage page</a>.</p>
	<p>For more information on quota usage, read the <a href="https://github.com/macbookandrew/wp-youtube-live#quota-units">plugin documentation</a> as well as the <a href="https://developers.google.com/youtube/v3/getting-started#quota" target="_blank">YouTube API documentation</a>.</p>
	<?php
}

/**
 * Print debugging field
 */
function youtube_live_debugging_render() {
	$options = get_option( 'youtube_live_settings' );
	if ( ! array_key_exists( 'debugging', $options ) ) {
		$options['debugging'] = false;
	}

	/**
	 * Filters the capability required to see debug output.
	 *
	 * @since 1.10.0
	 *
	 * @var string $capability The capability required.
	 *
	 * @return string
	 */
	$capability = apply_filters( 'wp_youtube_live_debug_user_capability', 'manage_options' );

	?>
	Show debugging information in an HTML comment for logged-in users with the <code><?php echo esc_attr( $capability ); ?></code> capability? <label><input type="radio" name="youtube_live_settings[debugging]" value="true" <?php checked( $options['debugging'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[debugging]" value="false" <?php checked( $options['debugging'], 'false' ); ?>> No</label>
	<?php
}

/**
 * Print API settings field
 */
function youtube_live_api_settings_section_callback() {
	echo wp_kses_post( __( 'Enter your YouTube details below. Once you&rsquo;ve entered the required details below, add the shortcode <code>[youtube_live]</code> to any post/page to display the live player.', 'youtube_live' ) );
}

/**
 * Print settings form
 */
function youtube_live_options_page() {
	?>
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

/**
 * Manually clear upcoming video cache
 *
 * @param string $action action to perform.
 * @param string $nonce  security nonce.
 * @return string|void JSON string of upcoming videos
 */
function refresh_youtube_live_upcoming_cache( $action = null, $nonce = null ) {

	if ( ! $action && isset( $_POST['action'] ) ) {
		$action = sanitize_key( wp_unslash( $_POST['action'] ) );
	}

	if ( ! $nonce && isset( $_POST['nonce'] ) ) {
		$nonce = sanitize_key( wp_unslash( $_POST['nonce'] ) );
	}

	if ( ! wp_verify_nonce( $nonce, 'wpYTcache_nonce' ) ) {
		die( 'Invalid nonce.' );
	}

	$youtube_options = get_option( 'youtube_live_settings' );
	$youtube_live    = new EmbedYoutubeLiveStreaming( $youtube_options['youtube_live_channel_id'], $youtube_options['youtube_live_api_key'] );

	if ( 'updatewpytupcomingcache' === $action ) { // sanitize_key converts to lower-case.
		if ( $youtube_live->clearUpcomingVideoInfo() ) {
			$output = wp_json_encode( format_upcoming_videos( get_transient( 'youtube-live-upcoming-videos' ) ) );
			if ( $_POST ) {
				echo wp_kses_post( $output );
				die();
			} else {
				return $output;
			}
		}
	}
}
add_action( 'wp_ajax_updatewpYTUpcomingCache', 'refresh_youtube_live_upcoming_cache' );

/**
 * Return list of video IDs and start times
 *
 * @param  array $input possibly serialized array of $id => $start_time values.
 * @return string HTML output
 */
function format_upcoming_videos( $input ) {
	if ( $input ) {
		$video_array = maybe_unserialize( $input );
	}

	global $wpdb;
	$transient_expire_time = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- no functions exist to get the transient expiration time, and caching would defeat the purpose of determining the expiration time.
		$wpdb->prepare(
			'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = "%1$s";',
			'_transient_timeout_youtube-live-upcoming-videos'
		),
		0
	);

	$upcoming_list = '<h3>Cache Contents</h3>
    <p>Cache valid until ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $transient_expire_time[0] ) . '.</p>
    <ul>';
	if ( is_array( $video_array ) && count( $video_array ) > 0 ) {
		foreach ( $video_array as $id => $start_time ) {
			$upcoming_list .= '<li>Video ID <code>' . esc_attr( $id ) . '</code> starting ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), esc_attr( $start_time ) ) . '</li>';
		}
	} else {
		$upcoming_list .= '<li>Cache is currently empty. Make sure you have some videos scheduled, then press the button below to manually update the cache.</li>';
	}
	$upcoming_list .= '</ul>';

	return $upcoming_list;
}

/**
 * Render tools button.
 *
 * @return void
 */
function youtube_live_tools_render() {
	?>
	<p><a class="btn primary" target="_blank" href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=youtube_live_flush_cache' ) ); ?>">Flush Cache</a></p>
	<?php
}

/**
 * Render terms.
 *
 * @return void
 */
function youtube_live_terms_render() {
	?>
	<p>This plugin stores your channel ID and API token in your WordPress options table, but does not store or collect any other information.</p>

	<p>Because this plugin helps you use the YouTube service, you should refer to these documents as well:</p>

	<ul>
		<li><a href="https://www.youtube.com/t/terms" target="_blank">YouTube Terms of Service</a></li>
		<li><a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
	</ul>

	<?php
}

/**
 * Admin notices.
 */
if ( is_admin() && get_option( 'wp-youtube-live-1714-notice-dismissed', true ) === false ) {
	add_action( 'admin_notices', 'wp_youtube_live_admin_notices_1714' );
	add_action( 'wp_ajax_wp_youtube_live_dismiss_notice_1714', 'wp_youtube_live_dismiss_notice_1714' );
}


/**
 * Add admin notice about quota and checking frequency changes.
 *
 * @since 1.7.14
 */
function wp_youtube_live_admin_notices_1714() {
	?>
	<div class="notice notice-error wp-youtube-live-notice is-dismissible" data-version="1714">
		<h2>YouTube Live Notice</h2>
		<p>Due to YouTube Data API changes, this plugin now checks for new live videos every <strong>15 minutes</strong> rather than every 30 seconds.</p>
		<p>You can change this setting on the <a href="<?php echo esc_url( admin_url( 'options-general.php?page=youtube-live#transient-timeout' ) ); ?>">plugin settings page</a>.</p>
	</div>
	<?php
}

/**
 * Update option for WP YouTube Live 1.7.14 notes.
 *
 * @since 1.8.0
 */
function wp_youtube_live_dismiss_notice_1714() {
	update_option( 'wp-youtube-live-1714-notice-dismissed', true, false );
}

