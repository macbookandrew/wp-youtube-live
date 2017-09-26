(function($){
    $(document).ready(function(){
        var fallbackSelector = $('select[name="youtube_live_settings[fallback_behavior]"]');
        updateFallbackOptions(fallbackSelector);

        fallbackSelector.on('change', function() {
            updateFallbackOptions($(this));
        });
    });

    function updateFallbackOptions(fallbackSelector) {
        var selectedFallback = fallbackSelector.val(),
            fallbackAll = $('p.fallback'),
            fallbackMessage = $('p.fallback.message'),
            fallbackUpcoming = $('p.fallback.upcoming'),
            fallbackPlaylist = $('p.fallback.playlist'),
            fallbackVideo = $('p.fallback.video');

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
})(jQuery);
