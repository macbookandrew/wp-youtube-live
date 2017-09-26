=== WP YouTube Live ===
Contributors:      macbookandrew
Donate link:       https://cash.me/$AndrewRMinionDesign
Tags:              youtube, live, video, embed
Requires at least: 3.6
Tested up to:      4.8.1
Stable tag:        1.7.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Displays the current YouTube live video from a specified channel.

== Description ==

Displays the current YouTube live video from a specified channel via the shortcode `[youtube_live]`.

If no live video is available, you can display a specified video or a “channel player” showing all your recent videos.

You can also enable auto-refresh to automatically check for a live video (warning: will increase server load, so use with caution).

By default, the server will check YouTube’s API and then cache that response for 30 seconds before checking the API again. If auto-refresh is enabled, clients will check against your server every 30 seconds and likely will hit that cache as well, so it can potentially take up to 60 seconds before a client will get a live video.

The length of both caches can be changed using the `wp_youtube_live_transient_timeout` filter (see below for more information).

= Shortcode Options =

- width: player width in pixels; defaults to what you set on the settings page
- height: player height in pixels; defaults to whaty you set on the settings page
- autoplay: whether or not to start playing immediately on load; defaults to false
- no_stream_message: simple text message to show when no stream is available; use `no_stream_message="no_message"` to show nothing at all.
- auto_refresh: (either `true` or `false`) overrides the auto-refresh setting on the settings page

Example shortcode: `[youtube_live width="720" height="360" autoplay="true"]`

= Filters =

The filter `wp_youtube_live_no_stream_available` will customize the message viewers see if there is no live stream currently playing, and takes effect **after** the `no_stream_message` shortcode attribute is parsed (if `no_stream_message="no_message"` is set in a shortcode, it will override the filter). For example, add this to your theme’s `functions.php` file:

`
add_filter( 'wp_youtube_live_no_stream_available', 'my_ytl_custom_message' );
function my_ytl_custom_message( $message ) {
    $message = '<p>Please check back later or subscribe to <a target="_blank" href="https://youtube.com/channel/UCH…">our YouTube channel</a>.</p>
    <p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
    return $message;
}
`

The filter `wp_youtube_live_transient_timeout` is available to customize the cache timeout length in seconds. For example, add this to your theme’s `functions.php` file to set the cache length to 15 seconds instead of the default 30:

`
add_filter( 'wp_youtube_live_transient_timeout', 'my_ytl_custom_timeout' );
function my_ytl_custom_timeout( $message ) {
    return '15';
}
`

= Event Listener =

When a live stream is loaded, the `wpYouTubeLiveStarted` event is fired; you can use this to create custom front-end features on your site by adding an event listener:

`
window.addEventListener('wpYouTubeLiveStarted', function() {
    /* your code here */
    console.log('stream started');
    /* your code here */
});
`

Development of this plugin is done on [GitHub](https://github.com/macbookandrew/wp-youtube-live/). Pull requests are always welcome.

== Installation ==

1. Upload this folder to the `/wp-content/plugins/` directory or install from the Plugins menu in WordPress
1. Activate the plugin through the Plugins menu in WordPress
1. Add your Google API key and YouTube Channel ID in the settings page (Settings > YouTube Live)
1. Add the shortcode `[youtube_live]` into any post/page to show the live player

== Frequently asked questions ==

= How does this work? =

This plugin uses Google’s [YouTube Data API](https://developers.google.com/youtube/v3/) to search for in-progress live videos and if one is found, embeds it in the page.

= API-what? =

API stands for “Application Programming Interface,” which basically means computer code that is able to talk to other computer systems and get or send information. Most API providers require an API key of some sort (similar to a username and password) to ensure that only authorized people are able to use their services.

= What info is sent or received? =

When the shortcode is used in a page, your web server makes a request to YouTube’s servers asking for information about the videos in your channel, using your channel ID and API key to authenticate. If you don’t have an API key set up or it’s not authorized for the YouTube Data API, the request will be denied.

For more information on setting up an API key, see the [YouTube Data API reference](https://developers.google.com/youtube/registering_an_application); for purposes of this plugin, you’ll need a “browser key.”

= Why doesn’t my live stream show up immediately? =

Generally, it can take up to a minute or two for the streaming page with the shortcode to recognize that you have a live stream, for several reasons:

1. YouTube’s API caches information about your videos for a short time (I couldn’t find any documentation on how long exactly, but seems to be around 30 seconds)
2. To help you from exceeding the free API quota, this plugin caches YouTube’s API response for 30 seconds (configurable using the `wp_youtube_live_transient_timeout` filter) instead of checking the API every time an update is requested
3. If you are using a caching plugin (WP Super Cache, W3 Total Cache, etc.), the page content is cached. However, this plugin provides a workaround by sending an Ajax request from the user’s browser when the page is loaded, and then every 30 seconds thereafter until a live video is available (also configurable using the `wp_youtube_live_transient_timeout` filter).

In short, there’s a tradeoff between showing the live video immediately and minimizing API quota and server resource usage, and I’ve tried to strike a reasonable balance, while allowing you the ability to tweak the cache timeouts yourself to fit your needs.

== Screenshots ==

1. Settings screen

== Changelog ==

= 1.7.0 =
- This update sponsored by [International Podcast Day](https://internationalpodcastday.com/)
- Improve fallback behavior by adding these options:
    - Next upcoming video
    - Most recently-completed live video
    - All videos in a channel
    - A specified playlist
    - A specified video
    - A custom message
    - Nothing at all
- Improve transient cache handling

= 1.6.4 =
- Fix error handling

= 1.6.3 =
- Add error handling for API key issues
- Fix some miscellaneous PHP issues

= 1.6.2 =
- Add a JS event for custom uses

= 1.6.1 =
- Add settings for default width and height
- Add setting for auto-refresh feature
- Add support for a fallback video if no live stream is available

= 1.6.0 =
- Add support for a channel player if no live stream is available
- Automatically recheck every 30 seconds to see if a live stream is available

= 1.5.4 =
- Minor fix for `no_stream_message` attribute handling for real this time

= 1.5.3 =
- Minor fix for `no_stream_message` attribute handling

= 1.5.2 =
- Minor fix for `no_stream_message` attribute handling

= 1.5.1 =
- Minor fix for an upgrade issue if the subdomain was not set after an upgrade

= 1.5 =
- Add support for pre-shortcode “no stream available” message
- Add support for gaming.youtube.com subdomain

= 1.4.2 =
- Fix minor readme formatting issues

= 1.4.1 =
- Fix minor issues

= 1.4 =
- Use curl instead of file_get_contents as it didn’t work reliably on some hosting environments.
- Add a visual spinner when checking via Ajax
- Cache results to reduce API calls (defaults to 30-second expiration)

= 1.3 =
- Add Ajax button to check from client-side for live video

= 1.2 =
- Add debugging information for logged-in users

= 1.1 =
- Use PHP class instead of unreliable client-side JS to search for live videos

= 1.0 =
- Initial release
