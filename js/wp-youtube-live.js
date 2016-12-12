(function($){
    $(document).ready(function(){
        // set up request
        var data = Object.assign(wpYouTubeLive, {
            'action': 'load_youtube_live',
            'isAjax': true,
        });

        $('body').on('click', 'button#check-again', function() {
            event.preventDefault();
            $('.wp-youtube-live .spinner').show();
            sendRequest(data);
        });

        // make request
        function sendRequest(data) {
            $.ajax({
                method: "POST",
                url: wpYouTubeLive.ajaxUrl,
                data: data
            })
            .done(function(response) {
                $('.wp-youtube-live').replaceWith(response);
            })
            .always(function() {
                $('.wp-youtube-live .spinner').hide();
            })
        }
    });
})(jQuery);
