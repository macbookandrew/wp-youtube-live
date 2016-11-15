(function($) {
    $(document).ready(function(){

        var player,
            urlString = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video' + '&channelId=' + youtubeLiveSettings['channelId'] + '&key=' + youtubeLiveSettings['apiKey'];
        console.info('YouTube search URL: '+urlString);

        // search YouTube channel
        $.ajax({
            type: "GET",
            url: urlString,
            dataType: 'jsonp',
            success: function(data) {
                var latestVideoId = data.items[0].videoId;

                // loop through results looking for a live video
                $.each(data.items, function(key, value) {
                    if (value.id.kind == 'youtube#video') {
                        // if this is live, load player
                        if (value.snippet.liveBroadcastContent == 'live') {
                            window.youtubeActiveVideo = true;
                            onYouTubeIframeAPIReady(value.id.videoId);
                        }
                    }
                });
            },
            error: function(data) {
                // if something went wrong, point them to the YouTube channel URL
                $('#youtube-live-player').html('<p>Sorry, something went wrong trying to display the current live broadcast. Please try <a target="_blank" href="https://youtube.com/channel/'+youtubeLiveSettings['channelId']+'">watching on YouTube.com</a>.</p>');
            },
            complete: function(data) {
                // if no live video fond
                $('div#youtube-live-player').html('<p>Sorry, there&rsquo;s no live stream at the moment. Please check back later or take a look at <a target="_blank" href="https://youtube.com/channel/'+youtubeLiveSettings['channelId']+'">all our videos</a>.</p>');
            }
        });

        // instantiate the player
        function onYouTubeIframeAPIReady(videoId) {
            player = new YT.Player('youtube-live-player', {
                height: youtubeLiveSettings['height'],
                width: youtubeLiveSettings['width'],
                autoplay: youtubeLiveSettings['autoplay'],
                modestbranding: youtubeLiveSettings['modestbranding'],
                playsinline: youtubeLiveSettings['playsinline'],
                videoId: videoId,
                events: {
                    'onReady': onPlayerReady
                }
            });
        }

        // start playing the video
        function onPlayerReady(event) {
            event.target.playVideo();
        }
    });
})(jQuery);
