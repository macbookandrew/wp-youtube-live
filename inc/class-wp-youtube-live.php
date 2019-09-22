<?php
/**
 * WP YouTube Live.
 *
 * @package wp-youtube-live
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP YouTube Live.
 *
 * @since 2.0.0
 */
class WP_YouTube_Live {

	/**
	 * Plugin settings.
	 *
	 * @var array $settings
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * YouTube request parameters.
	 *
	 * @var array $youtube
	 * @since 2.0.0
	 */
	protected $youtube = array(
		'url_base'  => 'https://www.googleapis.com/youtube/v3/',
		'resource'  => 'search',
		'part'      => 'id,snippet',
		'eventType' => 'live',
		'type'      => 'video',
	);

	/**
	 * Default video settings.
	 *
	 * @var array $defaults
	 * @since 2.0.0
	 */
	protected $defaults = array(
		'width'    => 560,
		'height'   => 315,
		'autoplay' => 'true',
		'related'  => 'false',
	);

	/**
	 * Whether channel is currently live or not.
	 *
	 * @var bool $is_live
	 * @since 2.0.0
	 */
	protected $is_live = false;

	/**
	 * Last API response.
	 *
	 * @var stdClass $response
	 * @since 2.0.0
	 */
	protected $response = null;

	/**
	 * Debugging information.
	 *
	 * @var array $debugging
	 * @since 2.0.0
	 */
	protected $debugging = array();

	/**
	 * YouTube errors.
	 *
	 * @var array $youtube_error
	 * @since 2.0.0
	 */
	protected $youtube_error = array();

	/**
	 * Class instance.
	 *
	 * @var WP_YouTube_Live $instance
	 */
	private static $instance = null;

	/**
	 * Return only one instance of this class.
	 *
	 * @return WP_YouTube_Live class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new WP_YouTube_Live();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		// Initialize plugin settings.
		$this->initialize_settings();

		// Handle activation.
		add_action( 'plugins_loaded', array( $this, 'check_version' ) );
		register_activation_hook( WP_YOUTUBE_LIVE_FILE, array( $this, 'activate_plugin' ) );

		// Register assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		// Register shortcode.
		add_shortcode( 'youtube_live', array( $this, 'shortcode' ) );

		// Register ajax handlers.
		add_action( 'wp_ajax_load_youtube_live', array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_load_youtube_live', array( $this, 'ajax' ) );
	}

	/**
	 * Set up class variable.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function initialize_settings() {
		$settings = get_option( 'youtube_live_settings' );

		$this->settings = wp_parse_args( $settings, $this->defaults );
		$this->response = new stdClass();

		// Handle ajax requests.
		if ( wp_doing_ajax() ) {
			$ajax_args = array(
				'width'             => (int) esc_attr( $_POST['width'] ),
				'height'            => (int) esc_attr( $_POST['height'] ),
				'autoplay'          => (bool) esc_attr( $_POST['autoplay'] ),
				'related'           => (bool) esc_attr( $_POST['showRelated'] ),
				'auto_refresh'      => (bool) esc_attr( $_POST['autoRefresh'] ),
				'fallback_behavior' => esc_attr( $_POST['fallback_behavior'] ),
				'fallback_message'  => esc_attr( $_POST['fallback_message'] ),
				'fallback_playlist' => esc_attr( $_POST['fallback_playlist'] ),
				'fallback_video'    => esc_attr( $_POST['fallback_video'] ),
				'no_stream_message' => esc_attr( $_POST['no_stream_message'] ),
			);
   			$this->settings  = wp_parse_args( $ajax_args, $this->settings );
		}
	}

	/**
	 * Check plugin and database version numbers.
	 */
	public function check_version() {
		if ( defined( 'WP_YOUTUBE_LIVE_VERSION' ) && WP_YOUTUBE_LIVE_VERSION !== get_option( 'youtube_live_version' ) ) {
			$this->activate_plugin();
		}
	}

	/**
	 * Handle database upgrades on activation/upgrade.
	 */
	public function activate_plugin() {
		$request_options = get_option( 'youtube_live_settings' );

		// Removed in v1.7.0.
		if ( array_key_exists( 'show_channel_if_dead', $request_options ) && 'true' === $request_options['show_channel_if_dead'] ) {
			$request_options['fallback_behavior'] = 'channel';
		}
		unset( $request_options['show_channel_if_dead'] );

		// Updated in v1.7.0.
		if ( array_key_exists( 'fallback_video', $request_options ) && isset( $request_options['fallback_video'] ) ) {
			$request_options['fallback_behavior'] = 'video';
		}

		// Added in v1.7.0.
		if ( ! array_key_exists( 'autoplay', $request_options ) ) {
			$request_options['autoplay'] = true;
		}

		// Added in v1.7.0.
		if ( ! array_key_exists( 'show_related', $request_options ) ) {
			$request_options['show_related'] = false;
		}

		update_option( 'youtube_live_settings', $request_options );
		update_option( 'youtube_live_version', WP_YOUTUBE_LIVE_VERSION );
	}

	/**
	 * Register frontend scripts.
	 *
	 * @since 2.0.0
	 */
	public function register_scripts() {
		wp_register_script( 'wp-youtube-live', plugin_dir_url( WP_YOUTUBE_LIVE_FILE ) . 'js/wp-youtube-live.min.js', array( 'jquery' ), WP_YOUTUBE_LIVE_VERSION, true );
		wp_register_style( 'wp-youtube-live', plugin_dir_url( WP_YOUTUBE_LIVE_FILE ) . 'css/wp-youtube-live.css', array(), WP_YOUTUBE_LIVE_VERSION );
		wp_register_script( 'youtube-iframe-api', 'https://www.youtube.com/iframe_api', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Handle shortcode.
	 *
	 * @param array $atts Shortcode parameters.
	 *
	 * @return string     HTML markup.
	 * @since 2.0.0
	 */
	public function shortcode( $atts ) {

		// Get shortcode attributes.
		$shortcode_attributes = shortcode_atts(
			array(
				'width'             => $this->settings['default_width'],
				'height'            => $this->settings['default_height'],
				'autoplay'          => $this->settings['autoplay'],
				'showRelated'       => $this->settings['show_related'],
				'js_only'           => false,
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'auto_refresh'      => $this->settings['auto_refresh'],
				'fallback_behavior' => $this->settings['fallback_behavior'],
				'fallback_message'  => ( array_key_exists( 'no_stream_message', $this->settings ) ? $this->settings['no_stream_message'] : $this->settings['fallback_message'] ),
				'no_stream_message' => null,
				'fallback_playlist' => $this->settings['fallback_playlist'],
				'fallback_video'    => $this->settings['fallback_video'],

				/**
				 * Filters the transient timeout length.
				 * This also controls how often the frontend Javascript will check for live videos.
				 *
				 * @param int $seconds Number of seconds to cache response.
				 *
				 * @return int         Number of seconds to cache response.
				 */
				'refreshInterval'   => apply_filters( 'wp_youtube_live_transient_timeout', 30 ),
			),
			$atts
		);

		// Handle legacy parameter.
		if ( isset( $shortcode_attributes['no_stream_message'] ) ) {
			$shortcode_attributes['fallback_message'] = $shortcode_attributes['no_stream_message'];
			unset( $shortcode_attributes['no_stream_message'] );
		}

		// Enqueue assets.
		wp_enqueue_script( 'youtube-iframe-api' );
		wp_enqueue_script( 'wp-youtube-live' );
		wp_enqueue_style( 'wp-youtube-live' );
		wp_add_inline_script( 'wp-youtube-live', 'var wpYouTubeLiveSettings = ' . wp_json_encode( $shortcode_attributes ), 'before' );

		return $this->get_markup( $shortcode_attributes );
	}

	/**
	 * Handle ajax requests.
	 *
	 * @return void Echos JSON for Ajax request.
	 * @since 2.0.0
	 */
	public function ajax() {
		wp_send_json_success(
			array(
				'content'   => $this->get_markup(),
				'error'     => $this->youtube_error,
				'debugging' => ( $this->settings['debugging'] && is_user_logged_in() ? $this->debugging : array( 'message' => 'Debugging is disabled.' ) ),
			),
			200
		);
		wp_die();
	}

	/**
	 * Return video player markup.
	 *
	 * @param array $options Shortcode attributes.
	 *
	 * @return string        HTML markup.
	 * @since 2.0.0
	 */
	public function get_markup( $options ) {

		$html = '<div class="wp-youtube-live ' . ( $this->is_live() ? 'live' : 'dead' ) . '">';

		if ( $this->is_live() ) {
			$html .= $this->get_live_markup();
		} else {
			add_filter( 'oembed_result', array( $this, 'set_oembed_id' ) );
			add_filter( 'embed_defaults', array( $this, 'set_embed_size' ) );
			add_filter( 'oembed_result', array( $this, 'add_player_attributes_result' ), 10, 3 );

			$fallback = $this->get_fallback();

			if ( 'message' === $fallback['type'] ) {
				/**
				 * Filters the fallback message.
				 *
				 * @param string $message Message displayed when there is no content.
				 *
				 * @return string         Message displayed when there is no content.
				 * @since 1.0.0
				 */
				$html .= apply_filters( 'wp_youtube_live_no_stream_available', $fallback['content'] );
			} elseif ( 'no_message' === $fallback['type'] ) {
				$html .= '';
			} else {
				$html .= $fallback['content'];
			}
		}

		// User must be logged in so we don’t give out API keys.
		if ( $this->settings['debugging'] && is_user_logged_in() ) {
			$html .= '<!-- WP YouTube Live debugging: ' . wp_json_encode( $this->debugging, JSON_PRETTY_PRINT ) . ' -->';
		}

		// FIXME: handle video finish logic.
		// if ( $_POST && $_POST['isAjax'] && array_key_exists( 'completedVideoID', $_POST ) ) {
		// 	$completed_id = esc_attr( $_POST['completedVideoID'] );
		// }

		// Error handling.
		$error_message = '';
		if ( ! empty( $this->youtube_error ) ) {
			$error_message = '<p><strong>WP YouTube Live error:</strong></p><ul>';
			foreach ( $this->youtube_error->errors as $error ) {
				$error_message .= '<li><strong>Domain:</strong> ' . $error->domain . '</li>
				<li><strong>Reason:</strong> ' . $error->reason . '</li>
				<li><strong>Message:</strong> ' . $error->message . '</li>
				<li><strong>Extended help:</strong> ' . $error->extendedHelp . '</li>'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName -- this comes from the YouTube API.
			}
			if ( 'video' === $this->settings['fallback_behavior'] && empty( $this->settings['fallback_video'] ) ) {
				$error_message .= '<li>Please double-check that you have set a fallback video.</li>';
			}
			$error_message .= '</ul>';
		}

		$html .= $error_message;

		$html .= '</div><!-- .wp-youtube-live -->';

		return $html;
	}

	/**
	 * Get markup for live video.
	 *
	 * @return string HTML markup.
	 * @since 2.0.0
	 */
	private function get_live_markup() {
		return '<div id="wpYouTubeLive" width="' . $this->settings['width'] . '" height="' . $this->settings['height'] . '"></div>
			<script>
				var wpYTPlayer;
				function onYouTubeIframeAPIReady() {
					wpYTPlayer = new YT.Player("wpYouTubeLive", {
						videoId: "' . $this->get_live_id() . '",
						playerVars: {
							"autoplay": ' . $this->settings['autoplay'] . ',
							"rel": ' . $this->settings['related'] . '
						},
						events: {
							"onReady": wpYTonPlayerReady,
							"onStateChange": wpYTonPlayerStateChange
						}
					});
				}
			</script>';
	}

	/**
	 * Get live status.
	 *
	 * @return bool Whether channel is live or not.
	 * @since 2.0.0
	 */
	public function is_live() {
		$response = $this->get_last_response();
		return $response->pageInfo->totalResults > 0; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}

	/**
	 * Get ID of live video.
	 *
	 * @return string|false
	 * @since 2.0.0
	 */
	private function get_live_id() {
		if ( ! $this->is_live() || empty( $this->get_last_response()->items ) ) {
			return false;
		}

		return $this->get_last_response()->items[0]->id->videoId;
	}

	/**
	 * Check the transient and query the API if necessary.
	 *
	 * @param array $options Optional query options.
	 *
	 * @return array         API response.
	 * @since 2.0.0
	 */
	private function get_last_response( $options = array() ) {
		$key = md5( wp_json_encode( $options ) );
		if ( ! isset( $this->response->{$key} ) ) {
			$transient = json_decode( get_transient( 'wp-youtube-live-api-response' ) );
			if ( ! is_null( $transient ) ) {
				$this->response = (object) array_merge( (array) $this->response, (array) $transient );
			}

			if ( ! property_exists( $this->response, $key ) ) {
				$this->response->{$key} = $this->query_api( $options );

				/**
				 * Filters the transient timeout length.
				 *
				 * @param int $seconds Number of seconds to cache response.
				 *
				 * @return int         Number of seconds to cache response.
				 */
				set_transient( 'wp-youtube-live-api-response', wp_json_encode( $this->response ), apply_filters( 'wp_youtube_live_transient_timeout', 30 ) );
			} else {
				$this->set_debugging( array( 'message' => 'Loaded from transient.' ) );
			}
		}

		return $this->response->{$key};
	}

	/**
	 * Query the API.
	 *
	 * @param array $options Options for the query.
	 *
	 * @return stdClass      Response.
	 *
	 * @throws Exception     Exception on API error.
	 * @since 2.0.0
	 */
	private function query_api( $options = array() ) {

		$args = array();

		if ( array_key_exists( 'path', $options ) ) {
			$path = $options['path'];
		} else {
			$path = $this->youtube['resource'];
		}

		if ( ! array_key_exists( 'query', $options ) ) {
			$options['query'] = array();
		}

		$query = wp_parse_args(
			$options['query'],
			array(
				'key'       => $this->settings['youtube_live_api_key'],
				'channelId' => $this->settings['youtube_live_channel_id'],
				'part'      => $this->youtube['part'],
				'eventType' => $this->youtube['eventType'],
				'type'      => $this->youtube['type'],
			)
		);

		$url = $this->youtube['url_base'] . $path . '?' . http_build_query( $query );

		$response = wp_remote_get( $url, $args );

		if ( $this->settings['debugging'] ) {
			$debug_response         = $response;
			$debug_response['body'] = json_decode( $response['body'] );

			$this->set_debugging(
				array(
					'query'    => $query,
					'response' => $debug_response,
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			$this->set_debugging(
				array(
					'error' => array(
						'message' => $response->get_error_message,
						'code'    => $response->get_error_code,
					),
				)
			);
			throw new Exception( $response->get_error_message, $response->get_error_code );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = wp_remote_retrieve_response_message( $response );
			$code    = wp_remote_retrieve_response_code( $response );

			$this->set_debugging(
				array(
					'error' => array(
						'message' => $message,
						'code'    => $code,
					),
				)
			);
			throw new Exception( $message, $code );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( property_exists( $body, 'error' ) ) {
			$this->youtube_error = $body->error;

			$this->set_debugging(
				array(
					'youtubeErrors' => $this->youtube_error,
				)
			);
		} else {
			$this->youtube_error = array();
		}

		return $body;
	}

	/**
	 * Get fallback type and content.
	 *
	 * @return array Fallback type and content.
	 * @since 2.0.0
	 */
	private function get_fallback() {
		$fallback = array(
			'type'    => $this->settings['fallback_behavior'],
			'content' => '',
		);

		switch ( $fallback['type'] ) {

			case 'message':
				$fallback['content'] = wp_kses_post( $this->settings['fallback_message'] );
				break;

			case 'completed':
				$query_results = $this->get_last_completed();
				$last_video    = $query_results->items[0];

				$fallback['content'] = wp_oembed_get(
					'https://' . $this->settings['subdomain'] . '.youtube.com/watch?v=' . $last_video->id->videoId,
					array(
						'width'  => $this->settings['width'],
						'height' => $this->settings['height'],
					)
				);
				break;

			case 'channel':
				$uploads_id = $this->get_channel_playlist_id();

				$fallback['content'] = '<iframe id="wpYouTubeLive" width="' . $this->settings['width'] . '" height="' . $this->settings['height'] . '" src="https://' . $this->settings['subdomain'] . '.youtube.com/embed/videoseries?list=' . $uploads_id . '&autoplay=' . $this->settings['autoplay'] . '&rel=' . $this->settings['related'] . '" frameborder="0" allowfullscreen></iframe>';
				break;

			case 'playlist':
				$fallback['content'] = wp_oembed_get(
					$this->settings['fallback_playlist'],
					array(
						'width'  => $this->settings['width'],
						'height' => $this->settings['height'],
					)
				);
				break;

			case 'video':
				$fallback['content'] = wp_oembed_get(
					$this->settings['fallback_video'],
					array(
						'width'  => $this->settings['width'],
						'height' => $this->settings['height'],
					)
				);
				break;

			case 'upcoming':
				$next_video          = $this->get_upcoming_video_id();
				$fallback['content'] = wp_oembed_get(
					'https://' . $this->settings['subdomain'] . '.youtube.com/watch?v=' . $next_video,
					array(
						'width'  => $this->settings['width'],
						'height' => $this->settings['height'],
					)
				);
				break;

			case 'no_message':
			default:
				$fallback['content'] = '';
				break;
		}

		return $fallback;
	}

	/**
	 * Get channel information.
	 *
	 * This is cached in a transient for 24 hours.
	 *
	 * @return string Playlist ID.
	 * @since 1.0.0
	 */
	private function get_channel_playlist_id() {
		$playlist_id = get_transient( 'wp-youtube-live-channel-details' );
		if ( ! $playlist_id ) {
			$options      = array(
				'path'  => 'channels',
				'query' => array(
					'id'   => $this->settings['youtube_live_channel_id'],
					'key'  => $this->settings['youtube_live_api_key'],
					'part' => 'contentDetails',
				),
			);
			$channel_info = $this->get_last_response( $options );

			$playlist_id = $channel_info->items[0]->contentDetails->relatedPlaylists->uploads;

			set_transient( 'wp-youtube-live-channel-details', $playlist_id, 86400 );
		}

		return $playlist_id;
	}

	/**
	 * Get the last-completed video.
	 *
	 * @return stdClass API query response.
	 * @since 2.0.0
	 */
	private function get_last_completed() {
		return $this->get_last_response(
			array(
				'query' => array(
					'eventType'  => 'completed',
					'order'      => 'date',
					'maxResults' => '1',
				),
			)
		);
	}

	/**
	 * Get the next upcoming scheduled video.
	 *
	 * @return string Next video ID.
	 * @since 2.0.0
	 */
	private function get_upcoming_video_id() {
		return $this->get_upcoming_cache()[0];
	}

	/**
	 * Get upcoming scheduled videos from cache.
	 *
	 * @return array|bool Array of timestamp/ID pairs or false.
	 * @since 2.0.0
	 */
	public function get_upcoming_cache() {
		$future_videos = get_transient( 'youtube-live-upcoming-videos' );

		if ( empty( $future_videos ) ) {

			$upcoming = $this->get_last_response(
				array(
					'query' => array(
						'eventType'  => 'upcoming',
						'maxResults' => 50,
					),
				)
			);

			$future_videos = array();

			foreach ( $upcoming->items as $video ) {

				$video_details = $this->get_last_response(
					array(
						'path'  => 'videos',
						'query' => array(
							'id'   => $video->id->videoId,
							'part' => 'liveStreamingDetails',
						),
					)
				);

				$start_time = strtotime( $video_details->items[0]->liveStreamingDetails->scheduledStartTime );

				// Only include videos scheduled in the future, minus a 15-minute grace period.
				if ( '0' !== $start_time && $start_time > ( time() - 900 ) ) {
					$future_videos[ $start_time ] = $video->id->videoId;
				}
			}

			// Sort by date.
			ksort( $future_videos );

			// Cache until first video starts.
			$next_video_time = key( $future_videos );
			if ( $next_video_time > time() ) {
				$cache_length = $next_video_time - time() + 900;  // Add 15-minute “grace period” in case broadcast starts late.
			} else {
				$cache_length = 600;
			}

			set_transient( 'youtube-live-upcoming-videos', wp_json_encode( $future_videos ), $cache_length );
		}

		return $future_videos;
	}

	/**
	 * Clear the upcoming videos cache.
	 *
	 * @return bool Whether the cache was cleared.
	 * @since 2.0.0
	 */
	public function clear_upcoming_cache() {
		return delete_transient( 'youtube-live-upcoming-videos' );
	}

	/**
	 * Add id to oembedded iframe.
	 *
	 * @param  string $html HTML oembed output.
	 * @return string       HTML oembed output.
	 */
	public function set_oembed_id( $html ) {
		$html = str_replace( '<iframe', '<iframe id="wpYouTubeLive"', $html );

		return $html;
	}

	/**
	 * Set default oembed size for video/playlist fallback behavior.
	 *
	 * @param  array $size Default oembed sizes.
	 * @return array       Modified oembed size.
	 */
	public function set_embed_size( $size ) {
		$size['width']  = $this->settings['width'];
		$size['height'] = $this->settings['height'];

		return $size;
	}

	/**
	 * Add autoplay and related parameters to oembedded videos.
	 *
	 * @param  string $data2html HTML embed code.
	 * @param  string $url       URL to be embedded.
	 * @param  array  $args      Extra arguments passed to wp_oembed_get function.
	 *
	 * @return string            HTML embed code.
	 */
	public function add_player_attributes_result( $data2html, $url, $args ) {
		$player_settings = '';
		foreach ( $args as $key => $value ) {
			if ( is_null( $value ) ) {
				$value = 1;
			}
			$player_settings .= '&' . $key . '=' . $value;
		}

		$data2html = str_replace( '?feature=oembed', '?feature=oembed' . $player_settings, $data2html );

		return $data2html;
	}

	/**
	 * Set debugging data.
	 *
	 * @param array $data Debugging data to add.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function set_debugging( $data = array() ) {
		$key = gmdate( 'Ymd-His' );
		if ( ! array_key_exists( $key, $this->debugging ) ) {
			$this->debugging[ $key ] = array();
		}
		$this->debugging[ $key ] = array_merge( $this->debugging[ $key ], $data );
	}
}
