(function($){
    $(document).ready(function(){
        $('input[name="youtube_live_settings[show_channel_if_dead]"]').on('change', function(){
            var fallbackField = $('input[name="youtube_live_settings[fallback_video]"]');

            if ($(this).val() == 'true') {
                fallbackField.attr('disabled', true);
            } else {
                fallbackField.attr('disabled', false);
            }
        });
    });
})(jQuery);
