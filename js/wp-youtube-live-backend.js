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
            fallbackVideo = $('p.fallback.video');

        if (selectedFallback == 'message') {
            fallbackAll.slideUp();
            fallbackMessage.slideDown();
        } else if (selectedFallback == 'video') {
            fallbackAll.slideUp();
            fallbackVideo.slideDown();
        } else {
            fallbackAll.slideUp();
        }
    }
})(jQuery);
