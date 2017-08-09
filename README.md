# WP YouTube Live #
**Contributors:** [macbookandrew](https://profiles.wordpress.org/macbookandrew)  
**Donate link:**       https://cash.me/$AndrewRMinionDesign  
**Tags:**              youtube, live, video, embed  
**Requires at least:** 3.6  
**Tested up to:**      4.6.1  
**Stable tag:**        1.6.0  
**License:**           GPLv2 or later  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

Displays the current YouTube live video from a specified channel.

## Description ##

Displays the current YouTube live video from a specified channel via the shortcode `[youtube_live]`.

### Shortcode Options ###

- width: player width in pixels; defaults to 640
- height: player height in pixels; defaults to 360
- autoplay: whether or not to start playing immediately on load; defaults to false
- no_stream_message: simple text message to show when no stream is available; use `no_stream_message="no_message"` to show nothing at all.

Example shortcode: `[youtube_live width="720" height="360" autoplay="true"]`

The filter `wp_youtube_live_no_stream_available` is also available to customize the message viewers see if there is no live stream currently playing, and takes effect **before** the `no_stream_message` shortcode attribute is parsed (if `no_stream_message="no_message"` is set, it will override the filter). For example, add this to your theme’s `functions.php` file:


	add_filter( 'wp_youtube_live_no_stream_available', 'my_ytl_custom_message' );
	function my_ytl_custom_message( $message ) {
	    $message = '<p>Please check back later or subscribe to <a target="_blank" href="https://youtube.com/channel/' . $youtube_options['youtube_live_channel_id'] . '">our YouTube channel</a>.</p>
	    <p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
	    return $message;
	}


The filter `wp_youtube_live_transient_timeout` is available to customize the cache timeout length in seconds. For example, add this to your theme’s `functions.php` file to set the cache length to 15 seconds instead of the default 30:


	add_filter( 'wp_youtube_live_transient_timeout', 'my_ytl_custom_timeout' );
	function my_ytl_custom_timeout( $message ) {
	    return '15';
	}


Development of this plugin is done on [GitHub](https://github.com/macbookandrew/wp-youtube-live/). Pull requests are always welcome.

## Installation ##

1. Upload this folder to the `/wp-content/plugins/` directory or install from the Plugins menu in WordPress
1. Activate the plugin through the Plugins menu in WordPress
1. Add your Google API key and YouTube Channel ID in the settings page (Settings > YouTube Live)
1. Add the shortcode `[youtube_live]` into any post/page to show the live player

## Frequently asked questions ##

### How does this work? ###

This plugin uses Google’s [YouTube Data API](https://developers.google.com/youtube/v3/) to search for in-progress live videos and if one is found, embeds it in the page.

### API-what? ###

API stands for “Application Programming Interface,” which basically means computer code that is able to talk to other computer systems and get or send information. Most API providers require an API key of some sort (similar to a username and password) to ensure that only authorized people are able to use their services.

### What info is sent or received? ###

When the shortcode is used in a page, your web server makes a request to YouTube’s servers asking for information about the videos in your channel, using your channel ID and API key to authenticate. If you don’t have an API key set up or it’s not authorized for the YouTube Data API, the request will be denied.

For more information on setting up an API key, see the [YouTube Data API reference](https://developers.google.com/youtube/registering_an_application); for purposes of this plugin, you’ll need a “browser key.”

## Screenshots ##

### 1. Settings screen ###
![Settings screen](http://ps.w.org/wp-youtube-live/assets/screenshot-1.png)


## Changelog ##

### 1.6.0 ###
- Add support for a channel player if no live stream is available
- Automatically recheck every 30 seconds to see if a live stream is available

### 1.5.4 ###
- Minor fix for `no_stream_message` attribute handling for real this time

### 1.5.3 ###
- Minor fix for `no_stream_message` attribute handling

### 1.5.2 ###
- Minor fix for `no_stream_message` attribute handling

### 1.5.1 ###
- Minor fix for an upgrade issue if the subdomain was not set after an upgrade

### 1.5 ###
- Add support for pre-shortcode “no stream available” message
- Add support for gaming.youtube.com subdomain

### 1.4.2 ###
- Fix minor readme formatting issues

### 1.4.1 ###
- Fix minor issues

### 1.4 ###
- Use curl instead of file_get_contents as it didn’t work reliably on some hosting environments.
- Add a visual spinner when checking via Ajax
- Cache results to reduce API calls (defaults to 30-second expiration)

### 1.3 ###
- Add Ajax button to check from client-side for live video

### 1.2 ###
- Add debugging information for logged-in users

### 1.1 ###
- Use PHP class instead of unreliable client-side JS to search for live videos

### 1.0 ###
- Initial release
