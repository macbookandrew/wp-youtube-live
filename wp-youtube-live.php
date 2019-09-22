<?php
/**
 * Plugin Name: YouTube Live
 * Plugin URI: https://github.com/macbookandrew/wp-youtube-live
 * Description: Displays the current YouTube live video from a specified channel
 * Version: 2.0.0
 * Author: Andrew Minion
 * Author URI: https://andrewrminion.com/
 *
 * @package wp-youtube-live
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_YOUTUBE_LIVE_VERSION', '2.0.0' );
define( 'WP_YOUTUBE_LIVE_FILE', __FILE__ );

if ( is_admin() && ! class_exists( 'WP_YouTube_Live' ) ) {
	require_once 'inc/class-wp-youtube-live-admin.php';
	WP_YouTube_Live_Admin::get_instance();
}

if ( ! class_exists( 'WP_YouTube_Live' ) ) {
	require_once 'inc/class-wp-youtube-live.php';
	WP_YouTube_Live::get_instance();
}
