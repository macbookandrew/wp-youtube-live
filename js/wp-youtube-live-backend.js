/**
 * Set up object
 */
var wpYTdata = {
    isAjax: true
};

(function($){
    $(document).ready(function(){
        var fallbackSelector = $('select[name="youtube_live_settings[fallback_behavior]"]');
        updateFallbackOptions(fallbackSelector);

        fallbackSelector.on('change', function() {
            updateFallbackOptions($(this));
        });

        $('body').on('click', 'button#update_youtube_upcoming_cache', function(event) {
            event.preventDefault();
            wpYTdata.action = $(this).data('action');
            wpYTdata.nonce = $(this).data('nonce');
            wpYTsendRequest(wpYTdata);
        });
    });

    /**
     * Show/hide additional info
     * @param {object} fallbackSelector jQuery selector object
     */
    function updateFallbackOptions(fallbackSelector) {
        var selectedFallback = fallbackSelector.val(),
            fallbackAll = $('.fallback'),
            fallbackMessage = $('.fallback.message'),
            fallbackUpcoming = $('.fallback.upcoming'),
            fallbackPlaylist = $('.fallback.playlist'),
            fallbackVideo = $('.fallback.video');

        if (selectedFallback == 'message') {
            fallbackAll.slideUp();
            fallbackMessage.slideDown();
        } else if (selectedFallback == 'upcoming') {
            fallbackAll.slideUp();
            fallbackUpcoming.slideDown();
        } else if (selectedFallback == 'playlist') {
            fallbackAll.slideUp();
            fallbackPlaylist.slideDown();
        } else if (selectedFallback == 'video') {
            fallbackAll.slideUp();
            fallbackVideo.slideDown();
        } else {
            fallbackAll.slideUp();
        }
    }

    /**
     * Send ajax request
     * @param {object} wpYTdata data sent to server
     */
    function wpYTsendRequest(wpYTdata) {
        $('.wp-youtube-live-upcoming-cache').html('<p>Flushing cache&hellip;</p>');
        $('.spinner').css({'visibility': 'visible'});
        $.ajax({
            method: "POST",
            url: ajaxurl,
            data: wpYTdata
        })
        .done(function(response) {
            var requestData = JSON.parse(response);
            $('.wp-youtube-live-upcoming-cache').html(requestData);
        })
        .always(function() {
            $('.spinner').css({'visibility': 'hidden'});
        })
    }

})(jQuery);
