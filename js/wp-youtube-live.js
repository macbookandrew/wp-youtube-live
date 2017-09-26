(function($){
    $(document).ready(function(){
        // set up request
        var data = Object.assign(wpYouTubeLive, {
            'action': 'load_youtube_live',
            'isAjax': true,
        }),
            event = document.createEvent('Event');
        event.initEvent('wpYouTubeLiveStarted', true, true);

        // run an initial check to clear caches
        sendRequest(data);

        $('body').on('click', 'button#check-again', function(event) {
            event.preventDefault();
            data.requestType = 'refresh';
            checkAgain(data);
        });

        // auto-refresh
        if (wpYouTubeLive.auto_refresh == 'true') {
            var checkAgainTimer = setInterval(function() {
                data.requestType = 'refresh';
                checkAgain(data);
            }, wpYouTubeLive.refreshInterval * 1000);
        }

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
                if (data.error) {
                    $('.wp-youtube-live-error').append(data.error).show();
                } else if (data.live) {
                    $('.wp-youtube-live').replaceWith(data.content).addClass('live');
                    $('.wp-youtube-live-error').hide();
                    window.dispatchEvent(event);
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
                console.log('aborting check since video is live');
                clearInterval(checkAgainTimer);
            } else {
                console.log('sending request...');
                sendRequest(data);
            }
        }
    });
})(jQuery);
