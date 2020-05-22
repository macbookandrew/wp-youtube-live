<?php
/**
 * WP YouTube Live Admin.
 *
 * @package wp-youtube-live
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP YouTube Live Admin.
 *
 * @since 2.0.0
 */
class WP_YouTube_Live_Admin {

	/**
	 * Saved plugin settings.
	 *
	 * @var array $settings
	 * @since 2.0.0
	 */
	protected $settings = array();

	/**
	 * Class instance.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return WP_YouTube_Live_Admin class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new WP_YouTube_Live_Admin();
		}

		return self::$instance;
	}

	/**
	 * Load actions and hooks.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		// Get options.
		$this->settings = get_option( 'youtube_live_settings' );

		// Register assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_assets' ) );

		// Register admin page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_action( 'wp_ajax_update_youtube_upcoming_cache', array( $this, 'upcoming_cache' ) );
	}

	/**
	 * Enqueue backend assets
	 */
	public function backend_assets() {
		wp_register_script( 'wp-youtube-live-backend', plugin_dir_url( WP_YOUTUBE_LIVE_FILE ) . 'js/wp-youtube-live-backend.min.js', array( 'jquery' ), WP_YOUTUBE_LIVE_VERSION, true );
	}

	/**
	 * Add settings page to admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page( 'options-general.php', 'YouTube Live', 'YouTube Live Settings', 'manage_options', 'youtube-live', array( $this, 'options_page' ) );
	}

	/**
	 * Add settings section and fields
	 */
	public function settings_init() {
		register_setting( 'youtube_live_options', 'youtube_live_settings' );

		// API settings.
		add_settings_section(
			'youtube_live_options_keys_section',
			__( 'YouTube Details', 'wp-youtube-live' ),
			array( $this, 'section_callback' ),
			'youtube_live_options'
		);

		add_settings_field(
			'youtube_live_api_key',
			__( 'YouTube API Key', 'wp-youtube-live' ),
			array( $this, 'render_api_key' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'youtube_live_channel_id',
			__( 'YouTube Channel ID', 'wp-youtube-live' ),
			array( $this, 'render_channel_id' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'youtube_subdomain',
			__( 'YouTube Subdomain', 'wp-youtube-live' ),
			array( $this, 'render_subdomain' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'youtube_live_player_settings',
			__( 'Default Player Settings', 'wp-youtube-live' ),
			array( $this, 'render_player_settings' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'fallback_behavior',
			__( 'Fallback Behavior', 'wp-youtube-live' ),
			array( $this, 'render_fallback' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'auto_refresh',
			__( 'Auto-Refresh', 'wp-youtube-live' ),
			array( $this, 'render_auto_refresh' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'youtube_live_debugging',
			__( 'Debugging', 'wp-youtube-live' ),
			array( $this, 'render_debugging' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);

		add_settings_field(
			'youtube_live_terms_of_service',
			__( 'Debugging', 'wp-youtube-live' ),
			array( $this, 'render_terms_of_service' ),
			'youtube_live_options',
			'youtube_live_options_keys_section'
		);
	}

	/**
	 * Print API Key field.
	 */
	public function render_api_key() {
		wp_enqueue_script( 'wp-youtube-live-backend' );
		?>
		<input type="text" name="youtube_live_settings[youtube_live_api_key]" placeholder="AIzaSyD4iE2xVSpkLLOXoyqT-RuPwURN3ddScAI" size="45" value="<?php echo esc_attr( $this->settings['youtube_live_api_key'] ); ?>">

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
	 * Print Channel ID field.
	 */
	public function render_channel_id() {
		?>
		<input type="text" name="youtube_live_settings[youtube_live_channel_id]" placeholder="UcZliPwLMjeJbhOAnr1Md4gA" size="45" value="<?php echo esc_attr( $this->settings['youtube_live_channel_id'] ); ?>">

		<p>Go to <a href="https://youtube.com/account_advanced/" target="_blank">YouTube Advanced Settings</a> to find your YouTube Channel ID.</p>
		<?php
	}

	/**
	 * Print subdomain field.
	 */
	public function render_subdomain() {
		?>
		<label><select name="youtube_live_settings[subdomain]">
			<option value="www" <?php selected( $this->settings['subdomain'], 'www' ); ?>>Default (www.youtube.com)</option>
			<option value="gaming" <?php selected( $this->settings['subdomain'], 'gaming' ); ?>>Gaming (gaming.youtube.com)</option>
		</select></label>
		<?php
	}

	/**
	 * Print player settings fields.
	 */
	public function render_player_settings() {
		if ( ! array_key_exists( 'default_width', $this->settings ) || is_null( $this->settings['default_width'] ) ) {
			$this->settings['default_width'] = 720;
		}
		if ( ! array_key_exists( 'default_height', $this->settings ) || is_null( $this->settings['default_height'] ) ) {
			$this->settings['default_height'] = 480;
		}
		if ( ! array_key_exists( 'autoplay', $this->settings ) ) {
			$this->settings['autoplay'] = true;
		}
		if ( ! array_key_exists( 'show_related', $this->settings ) ) {
			$this->settings['show_related'] = false;
		}
		?>
		<p>
			<label>Width: <input type="number" name="youtube_live_settings[default_width]" placeholder="720" value="<?php echo esc_attr( $this->settings['default_width'] ); ?>">px</label><br/>
			<label>Height: <input type="number" name="youtube_live_settings[default_height]" placeholder="480" value="<?php echo esc_attr( $this->settings['default_height'] ); ?>">px</label>
		</p>
		<p>
			Should the player auto-play when a live video is available? <label><input type="radio" name="youtube_live_settings[autoplay]" value="true" <?php checked( $this->settings['autoplay'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[autoplay]" value="false" <?php checked( $this->settings['autoplay'], 'false' ); ?>> No</label><br/>
			<span style="font-size: 85%;">Note: if this is not working correctly for you, please read <a href="https://developers.google.com/web/updates/2017/09/autoplay-policy-changes" target="_blank">this note</a> about Google Chrome&rsquo;s autoplay policies.</span>
		</p>
		<p>
			Should the player show related videos when a video finishes? <label><input type="radio" name="youtube_live_settings[show_related]" value="true" <?php checked( $this->settings['show_related'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[show_related]" value="false" <?php checked( $this->settings['show_related'], 'false' ); ?>> No</label>
		</p>
		<?php
	}

	/**
	 * Print fallback behavior fields.
	 */
	public function render_fallback() {
		if ( ! array_key_exists( 'fallback_behavior', $this->settings ) ) {
			$this->settings['fallback_behavior'] = 'message';
		}
		if ( ! array_key_exists( 'fallback_message', $this->settings ) ) {
			$this->settings['fallback_message'] = '<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target="_blank" href="https://youtube.com/channel/' . $this->settings['youtube_live_channel_id'] . '">all of our videos</a>.</p>
	<p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
		}
		?>
		<p>
			<label for="youtube_live_settings[fallback_behavior]">If no live videos are available, what should be displayed?</label>
			<select name="youtube_live_settings[fallback_behavior]">
				<option value="message" <?php selected( $this->settings['fallback_behavior'], 'message' ); ?>>Show a custom HTML message (no additional quota cost)</option>
				<option value="upcoming" <?php selected( $this->settings['fallback_behavior'], 'upcoming' ); ?>>Show scheduled live videos (adds a quota unit cost of 100)</option>
				<option value="completed" <?php selected( $this->settings['fallback_behavior'], 'completed' ); ?>>Show last completed live video (adds a quota unit cost of 100)</option>
				<option value="channel" <?php selected( $this->settings['fallback_behavior'], 'channel' ); ?>>Show recent videos from my channel (adds a quota unit cost of at least 3)</option>
				<option value="playlist" <?php selected( $this->settings['fallback_behavior'], 'playlist' ); ?>>Show a specified playlist (adds a quota unit cost of at least 3)</option>
				<option value="video" <?php selected( $this->settings['fallback_behavior'], 'video' ); ?>>Show a specified video (no additional quota cost)</option>
				<option value="no_message" <?php selected( $this->settings['fallback_behavior'], 'no_message' ); ?>>Show nothing at all (no additional quota cost)</option>
			</select>
		</p>

		<p class="fallback message">
			<label for="youtube_live_settings[fallback_message]">Custom HTML message:</label><br/>
			<textarea cols="50" rows="8" name="youtube_live_settings[fallback_message]" placeholder="<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target='_blank' href='<?php echo esc_url( 'https://youtube.com/channel/' . $this->settings['youtube_live_channel_id'] ); ?>'>all of our videos</a>.</p>
			<p><button type='button' class='button' id='check-again'>Check again</button><span class='spinner' style='display:none;'></span></p>."><?php echo wp_kses_post( $this->settings['fallback_message'] ); ?></textarea>
		</p>

		<div class="fallback upcoming">
			<p>This option will fetch all your upcoming scheduled live videos from the YouTube API and cache them for 24 hours or until the first video is scheduled to begin, whichever is soonest. If you schedule more live videos, press the button below to manually flush the server’s cache. <strong>Note:</strong> if you have no upcoming scheduled videos, the last scheduled video will be shown instead.</p>

			<?php
			$youtube_live   = WP_YouTube_Live::get_instance();
			$upcoming_cache = $youtube_live->get_upcoming_cache();
			?>

			<div class="wp-youtube-live-upcoming-cache"><?php echo wp_kses_post( $this->format_upcoming_videos( $upcoming_cache ) ); ?></div>

			<p>
				<button type="button" class="button-primary" id="update_youtube_upcoming_cache" data-action="update_youtube_upcoming_cache" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpYTcache_nonce' ) ); ?>">Clear Cached Upcoming Videos</button> (costs 100 quota units each time)<span class="spinner" style="visibility: hidden;float: none;"></span>
			</p>
			<!-- TODO: add secondary fallback if no upcoming videos are scheduled -->
		</div>

		<p class="fallback playlist">
			<label for="youtube_live_settings[fallback_playlist]">Fallback Playlist URL:</label><br/>
			<input type="text" name="youtube_live_settings[fallback_playlist]" size="45" placeholder="https://www.youtube.com/watch?v=abc123…&list=PLABC123…" value="<?php echo esc_url( $this->settings['fallback_playlist'] ); ?>" />
		</p>

		<p class="fallback video">
			<label for="youtube_live_settings[fallback_video]">Fallback Video URL:</label><br/>
			<input type="text" name="youtube_live_settings[fallback_video]" size="45" placeholder="https://youtu.be/dQw4w9WgXcQ" value="<?php echo esc_url( $this->settings['fallback_video'] ); ?>" />
		</p>

		<p>For more information on quota usage, read the <a href="https://github.com/macbookandrew/wp-youtube-live#quota-units">plugin documentation</a> as well as the <a href="https://developers.google.com/youtube/v3/getting-started#quota" target="_blank">YouTube API documentation</a>.</p>
		<?php
	}

	/**
	 * Print auto-refresh field.
	 */
	public function render_auto_refresh() {
		if ( ! array_key_exists( 'auto_refresh', $this->settings ) ) {
			$this->settings['auto_refresh'] = false;
		}

		/**
		 * Filters the transient timeout.
		 * This filter is documented in class-wp-youtube-live.php.
		 */
		$frequency = apply_filters( 'wp_youtube_live_transient_timeout', 30 );
		?>
		Should the player page automatically check every <?php echo esc_attr( $frequency ); ?> seconds until a live video is available? <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="true" <?php checked( $this->settings['auto_refresh'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[auto_refresh]" value="false" <?php checked( $this->settings['auto_refresh'], 'false' ); ?>> No</label>
		<p><strong>Warning:</strong> depending on how many users are on the page, this may overload your server with requests.</p>
		<?php
	}

	/**
	 * Print debugging field.
	 */
	public function render_debugging() {
		if ( ! array_key_exists( 'debugging', $this->settings ) ) {
			$this->settings['debugging'] = false;
		}
		?>
		Show debugging information in an HTML comment for logged-in users? <label><input type="radio" name="youtube_live_settings[debugging]" value="true" <?php checked( $this->settings['debugging'], 'true' ); ?>> Yes</label> <label><input type="radio" name="youtube_live_settings[debugging]" value="false" <?php checked( $this->settings['debugging'], 'false' ); ?>> No</label>
		<?php
	}

	/**
	 * Print terms of service field.
	 */
	public function render_terms_of_service() {
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
	 * Print API settings field.
	 */
	public function section_callback() {
		echo esc_html__( 'Enter your YouTube details below. Once you&rsquo;ve entered the required details below, add the shortcode [youtube_live] to any post/page to display the live player.', 'wp-youtube-live' );
	}

	/**
	 * Print settings form.
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
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
	 * Manually clear upcoming video cache.
	 *
	 * @return string JSON string of upcoming videos.
	 */
	public function upcoming_cache() {
		if ( ! array_key_exists( 'nonce', $_POST ) || ! array_key_exists( 'action', $_POST ) || ! wp_verify_nonce( $_POST['nonce'], 'wpYTcache_nonce' ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ), 401 );
			wp_die();
		}

		if ( 'update_youtube_upcoming_cache' === sanitize_key( $_POST['action'] ) ) {
			$youtube_live = WP_YouTube_Live::get_instance();
			$youtube_live->clear_upcoming_cache();
			$output = wp_json_encode( $this->format_upcoming_videos( $youtube_live->get_upcoming_cache() ) );
			if ( $_POST ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
				die();
			} else {
				return $output;
			}
		}
	}

	/**
	 * Return list of video IDs and start times
	 *
	 * @param  array $videos Cached upcoming videos.
	 * @return string        HTML output.
	 */
	public function format_upcoming_videos( $videos ) {

		global $wpdb;
		$transient_expire_time = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s;",
				'_transient_timeout_youtube-live-upcoming-videos'
			),
			0
		);

		$upcoming_list = '<h3>Cache Contents</h3>
		<p>Cache valid until ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $transient_expire_time[0] ) . '.</p>
		<ul>';
		if ( count( $videos ) > 0 ) {
			foreach ( $videos as $start_time => $id ) {
				$upcoming_list .= '<li>Video ID <code>' . $id . '</code> starting ' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $start_time ) . '</li>';
			}
		} else {
			$upcoming_list .= '<li>Cache is currently empty. Make sure you have some videos scheduled, then press the button below to manually update the cache.</li>';
		}
		$upcoming_list .= '</ul>';

		return $upcoming_list;
	}
}
