=== WP YouTube Live ===
Contributors:      macbookandrew
Donate link:       https://cash.me/$AndrewRMinionDesign
Tags:              youtube, live, video, embed
Requires at least: 3.6
Tested up to:      6.3.2
Stable tag:        1.10.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Displays the current YouTube live video from a specified channel.

== Description ==

Displays the current YouTube live video from a specified channel via the shortcode `[youtube_live]`.

= Setup Notes =

Your YouTube livestream must be set to “Public” or it will not work. This is a security feature of YouTube’s API and unfortunately there’s no way to work around it.

In addition, your live stream must be set to allow embedding on third-party sites. If that feature is unavailable, you may need to [enable monetization](https://www.youtube.com/account_monetization) for your account. See YouTube documentation for more information or help with allowing embedding.

= How It Works =

If no live video is available, you can display a specified video or a “channel player” showing all your recent videos.

You can also enable auto-refresh to automatically check for a live video every 30 seconds (warning: will increase server load, so use with caution).

By default, the server will check YouTube’s API and then cache that response for 15 minutes before checking the API again (you may change this value in the admin settings). If auto-refresh is enabled, clients will check against your server every 30 seconds and likely will hit that cache as well, so it can potentially take up to 16 minutes before a client will get a live video.

The length of both caches can be changed using the `wp_youtube_live_transient_timeout` filter (see below for more information).

If no live video is available when a page is loaded, several fallback options are available:

- “Show a custom HTML message” allows you to specify a custom message to show
- “Show scheduled live videos” will show a player and countdown until your next live video
- “Show last completed live video” will show your most recently-completed live video
- “Show recent videos from my channel” will show a playlist of recent videos from your channel
- “Show a specified playlist” will show a specified playlist
- “Show a specified video” will show a specified video
- “Show nothing at all” will show nothing at all

When a video ends, users’ browsers will check your server again to see if a live video is available. If so, it will load that; if not, it will fall back as set in your options.

= Shortcode Options =

- `width`: player width in pixels; defaults to what you set on the settings page
- `height`: player height in pixels; defaults to what you set on the settings page
- `autoplay`: whether or not to start playing immediately on load; defaults to false
- `auto_refresh`: (either `true` or `false`) overrides the auto-refresh setting on the settings page
- `fallback_behavior`: choose from the following: `upcoming`, `completed`, `channel`, `playlist`, `video`, `message`, `no_message`
    - `upcoming`: the next upcoming scheduled video on the specified channel
    - `playlist`: a specified playlist (shortcode must also include the `fallback_playlist` attribute)
    - `video`: a specified video (shortcode must also include the `fallback_video` attribute)
    - `message`: a specified message
    - `no_message`: nothing at all
- `fallback_playlist`: a playlist URL to show when there are no live videos
- `fallback_video`: a video URL to show when there are no live videos
- `fallback_message`: a message to show when there are no live videos
- `js_only`: (either `true` or `false`) workaround for some caching issues; if a caching plugin (W3 Total Cache, WP Super Cache, etc.) or proxy (CloudFlare, etc.) caches the HTML while a video is live, visitors may continue to see an old live video even if it has ended. If set `js_only` is set to `true`, the server never displays the player code in the initial request and instead sends it in response to uncached ajax requests. This may also result in the video player being slightly delayed on page load due to the extra request, depending on the clients’ bandwidth and latency.

Example shortcode: `[youtube_live width="720" height="360" autoplay="true"]`

= Filters =

The filter `wp_youtube_live_no_stream_available` will customize the message viewers see if there is no live stream currently playing, and takes effect **after** the `fallback_message` shortcode attribute is parsed (if `fallback_message="no_message"` is set in a shortcode, it will override the filter). For example, add this to your theme’s `functions.php` file:

`
add_filter( 'wp_youtube_live_no_stream_available', 'my_ytl_custom_message' );
function my_ytl_custom_message( $message ) {
    $message = '<p>Please check back later or subscribe to <a target="_blank" href="https://youtube.com/channel/UCH…">our YouTube channel</a>.</p>
    <p><button type="button" class="button" id="check-again">Check again</button><span class="spinner" style="display:none;"></span></p>';
    return $message;
}
`

The filter `wp_youtube_live_transient_timeout` is available to customize the cache timeout length in seconds. For example, add this to your theme’s `functions.php` file to set the cache length to 15 seconds instead of the default 900:

`
add_filter( 'wp_youtube_live_transient_timeout', 'my_ytl_custom_timeout' );
function my_ytl_custom_timeout( $timeout ) {
    return 15;
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

YouTube setup instructions: see [this article](https://andrewrminion.com/2020/03/setting-up-youtube-live/).

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

Generally, it can take up to 15 minutes for the streaming page with the shortcode to recognize that you have a live stream, for several reasons:

1. YouTube’s API caches information about your videos for a short time (seems to be 2 minutes max).
2. To help you from exceeding the free API quota, this plugin caches YouTube’s API response for 15 minutes (configurable using the transient timeout setting), instead of checking the API every time an update is requested by a client. See Quota Units below for more information.
3. If you are using a caching plugin (WP Super Cache, W3 Total Cache, etc.), the generated page content is cached on your server, including whatever shortcode content is available when the cache is created. However, this plugin provides a workaround by sending an Ajax request from the user’s browser when the page is loaded, and then every 30 seconds thereafter until a live video is available (also configurable using the `wp_youtube_live_transient_timeout` filter).

In short, there’s a tradeoff between showing the live video immediately and minimizing API quota and server resource usage, and I’ve tried to strike a reasonable balance, while allowing you the ability to tweak the cache timeouts yourself to fit your needs.

= Quota Units =

- The YouTube Data API used by this plugin enforces a daily quota limit. Each request to find live videos costs 100 quota units, and the current daily cap (as of April 14, 2020) is 10,000 daily quota units.
- Every time this plugin checks for live videos, it costs 100 quota units. The API responses are cached for 15 minutes (by default) on your server to help stay within the daily quota limit.
- End users’ browsers will request an update from your site every 30 seconds.
- The admin settings panel has a field to configure how often to check the YouTube Data API for live videos. A value of 900 seconds (15 minutes) should stay pretty close to the default daily quota. If you have low traffic during “off hours” (when you’re not likely to be broadcasting a live event), you may be able to experiment and set this lower, since the quota won’t be consumed as much during the off hours.
- Fallback behavior:
    - “Show a custom HTML message” costs no additional quota units
    - “Show scheduled live videos” fallback behavior costs an additional 100 quota units per API call plus 3 quota units for each scheduled video you have (until the next-scheduled video starts [plus a 15-minute “grace period” to give some leeway for your actual start time], or for 5 minutes if there are no videos scheduled)
    - “Show last completed live video” fallback behavior costs an additional 100 quota units per API call
    - “Show recent videos from my channel” fallback behavior costs 1 quota unit for the call + 2 quota units for each video listed
    - “Show a specified playlist” fallback behavior costs 1 quota unit for the call + 2 quota units for each video in the playlist
    - “Show a specified video” costs no additional quota units
    - “Show nothing at all” costs no additional quota units

Estimated quota usage:

- If the transient timeout is set to 900 seconds and the page containing the shortcode is open in a browser 24/7, it should cost 9,600 quota units per day, regardless of how many visitors (due to the plugin’s caching mechanism).
- If fallback behavior is set to “scheduled live videos” or “last completed live video,” it should cost an additional 100 quota units when the next-scheduled video begins (or every 5 minutes if no videos are scheduled).
- If fallback behavior is set to “specified playlist,” it should cost an additional 1 quota unit per page load plus 2 quota units per video in the playlist.
- If fallback behavior is set to “specified playlist” or “specified video,” it should cost an additional 3 quota unit per page load.

These are estimates; your usage may vary. To see your actual quota usage in real time, visit the [API Usage page](https://console.developers.google.com/apis/api/youtube/usage).

The YouTube quota limit has been tightened down in recent years: as of April 14, 2020, it allows a max of 10 thousand quota units per day. However, you can [request a quota increase here](https://support.google.com/youtube/contact/yt_api_form).

If your API project has been active for more than 2 years or so, you may have a drastically higher quota limit and can set the transient lower.

I am looking at other ways to accomplish the necessary behavior closer to realtime while remaining within the quota limits.


= Terms of Service and Privacy Policy =

This plugin stores your channel ID and API token in your WordPress options table, but does not store or collect any other information.

Because this plugin helps you use the YouTube service, you should refer to these documents as well:

- [YouTube Terms of Service](https://www.youtube.com/t/terms)
- [Google Privacy Policy](https://policies.google.com/privacy)

== Screenshots ==

1. Settings screen

== Changelog ==

= 1.10.0 =
- Fix `show_related` parameter
- Fix debug output
- Add capability checking to see debug output

= 1.9.0 =
- Add YouTube Terms of Service and Google Privacy Policy

= 1.8.8 =
- Prevent undefined index error

= 1.8.7 =
- Fix deployment

= 1.8.6 =
- Fix undefined index error when no upcoming videos are available

= 1.8.5 =
- Prevent any errors from showing up when debug mode is disabled

= 1.8.4 =
- Fix “undefined index” issue in debug logs
- Fix persistent update notice

= 1.8.3 =
- Escape admin-visible debug output

= 1.8.2 =
- Escape oembed output

= 1.8.1 =
- Update code documentation
- Update plugin branding images

= 1.8.0 =
- Fix reported security issues
- Update plugin branding images

= 1.7.22 =
- Fix reflected cross-site scripting vulnerability

= 1.7.21 =
- Fix error on activation

= 1.7.20 =
- Fix error when no upcoming videos are cached

= 1.7.19 =
- Add referer to API requests.

= 1.7.18 =
- Test with WordPress 5.7
- Improve cache flush tool
- Improve deployment process

= 1.7.17 =
- Fix issue with invalid videos response.

= 1.7.16 =
- Add utility to flush cache.

= 1.7.15 =
- Add Terms of Service and Privacy Policy.

= 1.7.14 =
- Update FAQs and add admin notices.

= 1.7.13 =
- Update FAQs.

= 1.7.12 =
- Add transient timeout field.
- Update quota usage documentation due to recent changes.

= 1.7.11 =
- Add link to setup instructions.

= 1.7.10 =
- Fix a bug that could casue a player to show up even if no video is live.

= 1.7.9 =
- Fix a bug causing duplicate players when the shortcode is inside a `<p>` element.

= 1.7.8 =
- Fix some bugs with shortcode parameters
- Fix a bug where scheduled videos would cause an API error when checking for current live videos
- Add more documentation about available shortcode parameters
- Add a note about empty fallback video field

= 1.7.7 =
- Add `js_only` shortcode parameter to work around some caching issues

= 1.7.6 =
- Fix a typo in the admin
- Update the screenshot of the admin showing all the currently-available settings

= 1.7.5 =
- Fix a typo related to “show related videos”
- Add missing support for autoplay and “show related videos” to playlist and video fallback options
- Add note in admin about Google Chrome’s autoplay policy change

= 1.7.4 =
- Fix issues with shortcode parameters being ignored
- Fix issues with errors being displayed when in fact there were none
- Fix issues with “Show recent videos from my channel” fallback behavior
- Fix typos and clarify some fallback behavior

= 1.7.3 =
- This update sponsored by [International Podcast Day](https://internationalpodcastday.com/)
- Fix issues with upcoming video caching

= 1.7.2 =
- This update sponsored by [International Podcast Day](https://internationalpodcastday.com/)
- Automatically load fallback behavior when a video ends
- If fallback behavior is “Show upcoming videos,” cache a list of upcoming videos for 24 hours to save API quota unit cost
- Use YouTube’s API instead of a `<iframe` embed

= 1.7.1 =
- This update sponsored by [International Podcast Day](https://internationalpodcastday.com/)
- Fix a few minor bugs introduced in v1.7.0

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
