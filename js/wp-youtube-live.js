(function($){
    $(document).ready(function(){
        // set up request
        var data = Object.assign(wpYouTubeLive, {
            'action': 'load_youtube_live',
            'isAjax': true,
        });

        $('body').on('click', 'button#check-again', function(event) {
            event.preventDefault();
            checkAgain(data);
        });


        // run every 30 seconds until we have a livestream
        var checkAgainTimer = setInterval(function() {
            checkAgain(data);
        }, 30000);

        /**
         * Check for live-stream
         * @param {object} data info to pass to WP
         */
        function sendRequest(data) {
            $('.wp-youtube-live .spinner').show();
            $.ajax({
                method: "POST",
                url: wpYouTubeLive.ajaxUrl,
                data: data
            })
            .done(function(response) {
                var data = JSON.parse(response);
                if (data.live) {
                    $('.wp-youtube-live').replaceWith(data.content).addClass('live');
                }
            })
            .always(function() {
                $('.wp-youtube-live .spinner').hide();
            })
        }

        /**
         * Check if a live stream has been loaded
         * @param {object} data parameters for callback function
         */
        function checkAgain(data) {
            console.log('checking again...');
            if ($('.wp-youtube-live').hasClass('live')) {
                clearInterval(checkAgainTimer);
            } else {
                sendRequest(data);
            }
        }
    });
})(jQuery);
