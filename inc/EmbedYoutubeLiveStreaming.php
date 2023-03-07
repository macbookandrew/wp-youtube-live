<?php
/**
 * YouTube embed class
 */

// phpcs:disable WordPress.NamingConventions, Squiz.Commenting.VariableComment

/**
 * YouTube Embed class.
 */
class EmbedYoutubeLiveStreaming {
	public $channelId;
	public $API_Key;

	public $jsonResponse; // pure server response.
	public $objectResponse; // response decoded as object.
	public $arrayResponse; // response decoded as array.

	public $errorMessage; // error message.
	public $errorArray; // all error codes.

	public $isLive; // true if there is a live streaming at the channel.

	public $queryData; // query values as an array.
	public $getAddress; // address to request GET.
	public $getQuery; // data to request, encoded.

	public $queryString; // Address + Data to request.

	public $part;
	public $eventType;
	public $type;

	public $subdomain;

	public $default_embed_width;
	public $default_embed_height;
	public $default_ratio;

	public $embed_code; // contain the embed code.
	public $embed_autoplay;
	public $embed_width;
	public $embed_height;
	public $show_related;

	public $live_video_id;
	public $live_video_title;
	public $live_video_description;

	public $live_video_publishedAt;

	public $live_video_thumb_default;
	public $live_video_thumb_medium;
	public $live_video_thumb_high;

	public $resource_type;

	public $uploads_id;

	public $channel_title;

	public $completed_video_id;

	/**
	 * Set up the query
	 *
	 * @param string    $ChannelID  YouTube channel ID.
	 * @param string    $API_Key    Google Developers API key.
	 * @param boolean [ $autoQuery = true]  whether to automatically run the query.
	 */
	public function __construct( $ChannelID, $API_Key, $autoQuery = true ) {
		$this->channelId = $ChannelID;
		$this->API_Key   = $API_Key;

		$this->part      = 'id,snippet';
		$this->eventType = 'live';
		$this->type      = 'video';

		$this->getAddress = 'https://www.googleapis.com/youtube/v3/';
		$this->resource   = 'search';

		$this->default_embed_width  = '560';
		$this->default_embed_height = '315';
		$this->default_ratio        = $this->default_embed_width / $this->default_embed_height;

		$this->embed_width  = $this->default_embed_width;
		$this->embed_height = $this->default_embed_height;

		$this->embed_autoplay = true;

		if ( true === $autoQuery ) {
			$this->getVideoInfo();
		}
	}

	/**
	 * Get video info
	 *
	 * @param string [ $resource_type           = 'live'] type of video resource (live, video, channel, etc.).
	 * @param string [ $event_type              = 'live'] type of event (live, upcoming, completed).
	 */
	public function getVideoInfo( $resource_type = 'live', $event_type = 'live' ) {
		// check transient before performing query.
		$upcoming_cache = get_transient( 'wp-youtube-live-api-response' );
		if ( false === $upcoming_cache ) {
			$this->cacheUpcomingVideoInfo();
			$upcoming_cache = get_transient( 'wp-youtube-live-api-response' );
		}
		$wp_youtube_live_api_transient = maybe_unserialize( $upcoming_cache );

		if ( ! $this->resource_type || $resource_type !== $this->resource_type ) {
			$this->resource_type = $resource_type;
		}

		if ( ! $this->eventType || $event_type !== $this->eventType ) {
			$this->eventType = $event_type;
		}

		// remove completed live video from top of upcoming cache.
		if ( isset( $this->completed_video_id ) ) {
			$this->removeFromUpcomingCache( $this->completed_video_id );
		}

		if ( ! isset( $this->completed_video_id ) && $wp_youtube_live_api_transient && array_key_exists( $this->eventType, $wp_youtube_live_api_transient ) ) {
			// 30-second transient is set and is valid
			reset( $wp_youtube_live_api_transient );
			$key_name                                 = key( $wp_youtube_live_api_transient );
			$this->jsonResponse                       = $wp_youtube_live_api_transient[ $key_name ];
			$this->objectResponse                     = json_decode( $this->jsonResponse );
			$this->objectResponse->fromTransientCache = true;
		} elseif ( 'upcoming' === $this->eventType || ( isset( $this->completed_video_id ) && '' !== $this->completed_video_id ) ) {
			// get info for this video.
			$this->resource = 'videos';

			$this->queryData = array(
				'key'  => $this->API_Key,
				'part' => 'id,snippet',
				'id'   => $this->getUpcomingVideoInfo(),
			);

			// run the query.
			$this->queryAPI();

			// save to 30-second transient to reduce API calls.
			$API_results = array( $this->eventType => $this->jsonResponse );
			if ( is_array( $wp_youtube_live_api_transient ) ) {
				$API_results = array_merge( $API_results, $wp_youtube_live_api_transient );
			}
			set_transient( 'wp-youtube-live-api-response', maybe_serialize( $API_results ), $this->getTransientTimeout() );
		} else {
			// no 30-second transient is set.

			// set up query data.
			$this->queryData = array(
				'part'      => $this->part,
				'channelId' => $this->channelId,
				'eventType' => $this->eventType,
				'type'      => $this->type,
				'key'       => $this->API_Key,
			);

			// set up additional query data for last live video.
			if ( 'completed' === $this->eventType ) {
				$additional_data = array(
					'part'       => 'id,snippet',
					'eventType'  => 'completed',
					'order'      => 'date',
					'maxResults' => '1',
				);

				$this->queryData = array_merge( $this->queryData, $additional_data );
			}

			// run the query.
			$this->queryAPI();

			// save to 30-second transient to reduce API calls.
			$API_results = array( $this->eventType => $this->jsonResponse );
			if ( is_array( $wp_youtube_live_api_transient ) ) {
				$API_results = array_merge( $API_results, $wp_youtube_live_api_transient );
			}
			set_transient( 'wp-youtube-live-api-response', maybe_serialize( $API_results ), $this->getTransientTimeout() );
		}

		if ( isset( $this->objectResponse->items ) && count( $this->objectResponse->items ) > 0 && ( ( 'live' === $this->resource_type && $this->isLive() ) || ( 'live' === $this->resource_type && in_array( $this->eventType, array( 'upcoming', 'completed', true ) ) ) ) ) {
			if ( is_object( $this->objectResponse->items[0]->id ) ) {
				$this->live_video_id = $this->objectResponse->items[0]->id->videoId;
			} else {
				$this->live_video_id = $this->objectResponse->items[0]->id;
			}
			$this->live_video_title       = $this->objectResponse->items[0]->snippet->title;
			$this->live_video_description = $this->objectResponse->items[0]->snippet->description;

			$this->live_video_published_at  = $this->objectResponse->items[0]->snippet->publishedAt;
			$this->live_video_thumb_default = $this->objectResponse->items[0]->snippet->thumbnails->default->url;
			$this->live_video_thumb_medium  = $this->objectResponse->items[0]->snippet->thumbnails->medium->url;
			$this->live_video_thumb_high    = $this->objectResponse->items[0]->snippet->thumbnails->high->url;

			$this->channel_title = $this->objectResponse->items[0]->snippet->channelTitle;
			$this->embedCode();
		} elseif ( 'channel' === $this->resource_type ) {
			$this->resource  = 'channels';
			$this->queryData = array(
				'id'   => $this->channelId,
				'key'  => $this->API_Key,
				'part' => 'contentDetails',
			);
			$this->queryAPI();

			if ( $this->objectResponse ) {
				$this->uploads_id    = $this->objectResponse->items[0]->contentDetails->relatedPlaylists->uploads;
				$this->resource_type = 'channel';
			}

			$this->embedCode();
		}
	}

	/**
	 * Manually clear upcoming video cache
	 *
	 * @return boolean whether the transient was successfully set
	 */
	public function clearUpcomingVideoInfo() {
		if ( get_transient( 'youtube-live-upcoming-videos' ) ) {
			delete_transient( 'youtube-live-upcoming-videos' );
		}

		return $this->cacheUpcomingVideoInfo();
	}

	/**
	 * Cache info for all scheduled upcoming videos
	 *
	 * @return boolean whether 24-hour transient was set
	 */
	public function cacheUpcomingVideoInfo() {
		// set up query data.
		$this->queryData = array(
			'channelId'  => $this->channelId,
			'key'        => $this->API_Key,
			'part'       => 'id',
			'eventType'  => 'upcoming',
			'type'       => 'video',
			'maxResults' => 50,
		);

		// run the query.
		$all_upcoming_videos = json_decode( $this->queryAPI() );
		$all_videos_array    = array();

		$previous_resource_type = $this->resource;
		if ( property_exists( $all_upcoming_videos, 'items' ) && is_array( $all_upcoming_videos->items ) ) {
			foreach ( $all_upcoming_videos->items as $video ) {
				$this->resource  = 'videos';
				$this->queryData = array(
					'channelId' => $this->channelId,
					'key'       => $this->API_Key,
					'id'        => $video->id->videoId,
					'part'      => 'liveStreamingDetails',
				);

				$this_video = json_decode( $this->queryAPI() );
				$start_time = date( 'U', strtotime( $this_video->items[0]->liveStreamingDetails->scheduledStartTime ) );

				if ( '0' !== $start_time && $start_time > ( time() - 900 ) ) { // only include videos scheduled in the future, minus a 15-minute grace period.
					$all_videos_array[ $video->id->videoId ] = $start_time;
				}
			}
		}
		$this->resource = $previous_resource_type;

		// sort by date.
		asort( $all_videos_array );

		// cache until first video starts.
		$key          = key( $all_videos_array );
		$cache_length = 600;
		if ( $key ) {
			$next_video = $all_videos_array[ $key ];
			if ( $next_video > time() ) {
				$cache_length = $next_video - time() + 900;  // add 15-minute “grace period” in case breadcast starts late.
			}
		}

		return set_transient( 'youtube-live-upcoming-videos', maybe_serialize( $all_videos_array ), $cache_length );
	}

	/**
	 * Check if current live video is in upcoming cache and remove
	 *
	 * @param string $videoID video ID to remove.
	 */
	public function removeFromUpcomingCache( $videoID ) {
		$upcoming_videos = maybe_unserialize( get_transient( 'youtube-live-upcoming-videos' ) );

		if ( is_countable( $upcoming_videos ) && count( $upcoming_videos ) > 1 ) {
			unset( $upcoming_videos[ $videoID ] );
			$cache_length = reset( $upcoming_videos );

			// set to max of 24 hours.
			if ( $cache_length > time() && ( $cache_length - time() ) < 86400 ) {
				$cache_length = $cache_length - time();
			} else {
				$cache_length = 86400;
			}

			set_transient( 'youtube-live-upcoming-videos', maybe_serialize( $upcoming_videos ), $cache_length );
		}
	}

	/**
	 * Get next scheduled upcoming video
	 *
	 * @return string video ID
	 */
	public function getUpcomingVideoInfo() {
		$now = time();

		$upcoming_videos = get_transient( 'youtube-live-upcoming-videos' );
		$videos_array    = maybe_unserialize( $upcoming_videos );
		$next_video      = '';

		if ( ! $upcoming_videos ) {
			$this->cacheUpcomingVideoInfo();
		} else {
			foreach ( $videos_array as $id => $start_time ) {
				if ( $start_time > time() ) {
					$next_video = $id;
					break;
				}
			}
			if ( ! $next_video ) {
				end( $videos_array );
				$next_video = key( $videos_array );
			}
		}

		return $next_video;
	}

	/**
	 * Query the YouTube API
	 *
	 * @return string JSON API response
	 */
	public function queryAPI() {
		$this->getQuery    = http_build_query( $this->queryData ); // transform array of data in url query.
		$this->queryString = $this->getAddress . $this->resource . '?' . $this->getQuery;

		// request from API via curl.
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $this->queryString );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_CAINFO, plugin_dir_path( __FILE__ ) . 'cacert.pem' );
		curl_setopt( $curl, CURLOPT_CAPATH, plugin_dir_path( __FILE__ ) );
		curl_setopt( $curl, CURLOPT_REFERER, home_url() );
		$this->jsonResponse = curl_exec( $curl );
		curl_close( $curl );

		// FUTURE: add If-None-Match etag header to improve performance.

		$this->objectResponse = json_decode( $this->jsonResponse ); // decode as object.
		$this->arrayResponse  = json_decode( $this->jsonResponse, true ); // decode as array.

		if ( property_exists( $this->objectResponse, 'error' ) ) {
			$this->errorMessage = $this->objectResponse->error->message;
			$this->errorArray   = $this->arrayResponse['error']['errors'];
		} else {
			$this->errorMessage = null;
			$this->errorArray   = array();
		}

		return $this->jsonResponse;
	}

	/**
	 * Determine whether there is a live video or not
	 *
	 * @param  boolean [ $getOrNot = false] whether to run the query or not.
	 * @return boolean whether or not a video is live
	 */
	public function isLive( $getOrNot = false ) {
		if ( $getOrNot ) {
			$this->getVideoInfo();
		}

		if ( $this->objectResponse ) {
			$live_items = count( $this->objectResponse->items );

			if ( $live_items > 0 ) {
				$this->isLive = true;
				return true;
			} else {
				$this->isLive = false;
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Calculate embed size by width
	 *
	 * @param integer   $width        width in pixels.
	 * @param boolean [ $refill_code = true] whether to generate embed code or not.
	 */
	public function setEmbedSizeByWidth( $width, $refill_code = true ) {
		$ratio              = $this->default_embed_width / $this->default_embed_height;
		$this->embed_width  = $width;
		$this->embed_height = $width / $ratio;

		if ( $refill_code ) {
			$this->embedCode();
		}
	}

	/**
	 * Calculate embed size by height
	 *
	 * @param integer   $height       height in pixels.
	 * @param boolean [ $refill_code = true] whether to generate embed code or not.
	 */
	public function setEmbedSizeByHeight( $height, $refill_code = true ) {
		$ratio              = $this->default_embed_width / $this->default_embed_height;
		$this->embed_height = $height;
		$this->embed_width  = $height * $ratio;

		if ( $refill_code ) {
			$this->embedCode();
		}
	}

	/**
	 * Generate embed code
	 *
	 * @return string HTML embed code
	 */
	public function embedCode() {
		$autoplay = 'true' === $this->embed_autoplay ? 1 : 0;
		$related  = $this->show_related ? 1 : 0;
		if ( 'channel' === $this->resource_type ) {
			$this->embed_code = '<iframe
                id="wpYouTubeLive"
                width="' . esc_attr( $this->embed_width ) . '"
                height="' . esc_attr( $this->embed_height ) . '"
                src="https://' . esc_attr( $this->subdomain ) . '.youtube.com/embed?listType=playlist&list=' . esc_attr( $this->uploads_id ) . '&autoplay=' . esc_attr( $autoplay ) . '&rel=' . esc_attr( $related ) . '"
                frameborder="0"
                allowfullscreen>
            </iframe>';
		} else {
			ob_start(); ?>
				<div id="wpYouTubeLive" width="<?php echo esc_attr( $this->embed_width ); ?>" height="<?php echo esc_attr( $this->embed_height ); ?>"></div>
				<script>
					var wpYTPlayer;
					function onYouTubeIframeAPIReady() {
						wpYTPlayer = new YT.Player('wpYouTubeLive', {
							videoId: '<?php echo esc_attr( $this->live_video_id ); ?>',
							playerVars: {
								'autoplay': <?php echo esc_attr( $autoplay ); ?>,
								'rel': <?php echo esc_attr( $related ); ?>
							},
							events: {
								'onReady': wpYTonPlayerReady,
								'onStateChange': wpYTonPlayerStateChange
							}
						});
					}
				</script>
			<?php
			$this->embed_code = ob_get_clean();
		}

		return $this->embed_code;
	}

	/**
	 * Get error message string
	 *
	 * @return string error message
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Get detailed array of error messages
	 *
	 * @return array array of all messages
	 */
	public function getAllErrors() {
		return $this->errorArray;
	}

	/**
	 * Get transient timeout length.
	 *
	 * @return int Number of seconds to retain transient.
	 */
	public function getTransientTimeout() {
		$settings = get_option( 'youtube_live_settings' );
		if ( ! array_key_exists( 'transient_timeout', $settings ) || empty( $settings['transient_timeout'] ) ) {
			$settings['transient_timeout'] = 900;
		}

		return apply_filters( 'wp_youtube_live_transient_timeout', $settings['transient_timeout'] );
	}
}
