(function($){
    $(document).ready(function(){
        // set up request
        var data = Object.assign(wpYouTubeLive, {
            'action': 'load_youtube_live',
            'isAjax': true,
        });

        $('body').on('click', 'button#check-again', function() {
            event.preventDefault();
            sendRequest(data);
        });

        // make request
        function sendRequest(data) {
            $.post(
                wpYouTubeLive.ajaxUrl,
                data,
                function(response) {
                    $('.wp-youtube-live').replaceWith(response);
                }
            );
        }
    });
})(jQuery);
