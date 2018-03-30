/**
 * Set variables and event listener
 */
var wpYTdata = Object.assign(wpYouTubeLiveSettings, {
    'action': 'load_youtube_live',
    'isAjax': true,
}),
    wpYTevent = document.createEvent('Event');
wpYTevent.initEvent('wpYouTubeLiveStarted', true, true);

/**
 * Handle auto-refresh
 */
if (wpYouTubeLiveSettings.auto_refresh == 'true') {
    var checkAgainTimer = setInterval(function() {
        wpYTdata.requestType = 'refresh';
        wpYTcheckAgain(wpYTdata);
    }, wpYouTubeLiveSettings.refreshInterval * 1000);
}

/**
 * Check for live-stream
 * @param {object} data info to pass to WP
 */
function wpYTsendRequest(wpYTdata) {
    console.log('sending request...');
    jQuery('.wp-youtube-live .spinner').show();
    jQuery.ajax({
        method: "POST",
        url: wpYouTubeLiveSettings.ajaxUrl,
        data: wpYTdata
    })
    .done(function(response) {
        var requestData = JSON.parse(response);
        if (requestData.error) {
            jQuery('.wp-youtube-live-error').append(requestData.error).show();
        } else if (requestData.live === true && typeof requestData.content !== 'undefined') {
            jQuery('.wp-youtube-live').replaceWith(requestData.content).addClass('live');
            jQuery('.wp-youtube-live-error').hide();
            window.dispatchEvent(wpYTevent);
            onYouTubeIframeAPIReady();
        }
    })
    .always(function() {
        jQuery('.wp-youtube-live .spinner').hide();
    })
}

/**
 * Check if a live stream has been loaded
 * @param {object} data parameters for callback function
 */
function wpYTcheckAgain(wpYTdata) {
    console.log('checking again...');
    if (jQuery('.wp-youtube-live').hasClass('live')) {
        console.log('aborting check since video is live');
        clearInterval(checkAgainTimer);
    } else {
        wpYTsendRequest(wpYTdata);
    }
}

/**
 * Handle autorefresh
 */
jQuery(document).ready(function(){
    // run an initial check to clear caches
    wpYTsendRequest(wpYTdata);

    jQuery('body').on('click', 'button#check-again', function(event) {
        event.preventDefault();
        wpYTdata.requestType = 'refresh';
        wpYTcheckAgain(wpYTdata);
    });
});

/**
 * Play video when it is ready
 * @param {object} event YouTube player event
 */
function wpYTonPlayerReady(event) {
    if (wpYTdata.autoplay == 'true') {
        event.target.playVideo();
    }
}

/**
 * Get fallback behavior from server when video ends
 * @param {object} event YouTube player event
 */
function wpYTonPlayerStateChange(event) {
    console.log('YouTube player: ' + event.data);
    if (event.data == 0) {
        jQuery('.wp-youtube-live').removeClass('live').addClass('completed');
        wpYTdata.completedVideoID = wpYTPlayer.getVideoData().video_id;
        wpYTcheckAgain(window.wpYTdata);
    }
}
