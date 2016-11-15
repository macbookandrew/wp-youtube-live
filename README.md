# WP YouTube Live #
**Contributors:** [macbookandrew](https://profiles.wordpress.org/macbookandrew)  
**Donate link:**       https://cash.me/$AndrewRMinionDesign  
**Tags:**              youtube, live, video, embed  
**Requires at least:** 3.6  
**Tested up to:**      4.6.1  
**Stable tag:**        1.0.0  
**License:**           GPLv2 or later  
**License URI:**       http://www.gnu.org/licenses/gpl-2.0.html  

Displays the current YouTube live video from a specified channel.

## Description ##

Displays the current YouTube live video from a specified channel via the shortcode `[youtube_live]`.

### Shortcode Options ###

- width: player width in pixels; defaults to 640
- height: player height in pixels; defaults to 360
- autoplay: whether or not to start playing immediately on load; defaults to false
- playsinline: whether or not the video plays full-screen on iOS devices (false) or inline in the page (true)

Example shortcode: `[youtube_live width="720" height="360" autoplay="true"]`

Development of this plugin is done on [GitHub](https://github.com/macbookandrew/wp-youtube-live/). Pull requests are always welcome.

## Installation ##

1. Upload this folder to the `/wp-content/plugins/` directory or install from the Plugins menu in WordPress
1. Activate the plugin through the Plugins menu in WordPress
1. Add your Google API key and YouTube Channel ID in the settings page (Settings > YouTube Live)
1. Add the shortcode `[youtube_live]` into any post/page to show the live player

## Changelog ##

### 1.0.0 ###
Initial release
