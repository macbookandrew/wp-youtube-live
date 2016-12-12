<?php

class EmbedYoutubeLiveStreaming {
    public $channelId;
    public $API_Key;

    public $jsonResponse; // pure server response
    public $objectResponse; // response decoded as object
    public $arrayRespone; // response decoded as array

    public $isLive; // true if there is a live streaming at the channel

    public $queryData; // query values as an array
    public $getAddress; // address to request GET
    public $getQuery; // data to request, encoded

    public $queryString; // Address + Data to request

    public $part;
    public $eventType;
    public $type;

    public $default_embed_width;
    public $default_embed_height;
    public $default_ratio;

    public $embed_code; // contain the embed code
    public $embed_autoplay;
    public $embed_width;
    public $embed_height;

    public $live_video_id;
    public $live_video_title;
    public $live_video_description;

    public $live_video_publishedAt;

    public $live_video_thumb_default;
    public $live_video_thumb_medium;
    public $live_video_thumb_high;

    public $channel_title;

    public function __construct($ChannelID, $API_Key, $autoQuery = true) {
        $this->channelId = $ChannelID;
        $this->API_Key = $API_Key;

        $this->part = "id,snippet";
        $this->eventType = "live";
        $this->type = "video";

        $this->getAddress = "https://www.googleapis.com/youtube/v3/search?";

        $this->default_embed_width = "560";
        $this->default_embed_height = "315";
        $this->default_ratio = $this->default_embed_width / $this->default_embed_height;

        $this->embed_width = $this->default_embed_width;
        $this->embed_height = $this->default_embed_height;

        $this->embed_autoplay = true;

        if ($autoQuery == true) { $this->queryIt(); }
    }

    public function queryIt() {
        // check transient before performing query
        $wp_youtube_live_transient = get_transient( 'wp-youtube-live-api-response' );

        // if no or expired transient, set up query
        if ( false === $wp_youtube_live_transient ) {
            $this->queryData = array(
                "part" => $this->part,
                "channelId" => $this->channelId,
                "eventType" => $this->eventType,
                "type" => $this->type,
                "key" => $this->API_Key,
            );
            $this->getQuery = http_build_query($this->queryData); // transform array of data in url query
            $this->queryString = $this->getAddress . $this->getQuery;

            // request from API via curl
            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_URL, $this->queryString );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $curl, CURLOPT_CAINFO, plugin_dir_path( __FILE__ ) . 'cacert.pem' );
            curl_setopt( $curl, CURLOPT_CAPATH, plugin_dir_path( __FILE__ ) );
            $this->jsonResponse = curl_exec( $curl );
            curl_close( $curl );

            // save to 30-second transient to reduce API calls
            set_transient( 'wp-youtube-live-api-response', $this->jsonResponse, apply_filters( 'wp_youtube_live_transient_timeout', '30' ) );
        } else {
            $this->jsonResponse = $wp_youtube_live_transient;
        }

        $this->objectResponse = json_decode($this->jsonResponse); // decode as object
        $this->arrayResponse = json_decode($this->jsonResponse, TRUE); // decode as array

        $this->isLive();
        if ($this->isLive) {
            $this->live_video_id = $this->objectResponse->items[0]->id->videoId;
            $this->live_video_title = $this->objectResponse->items[0]->snippet->title;
            $this->live_video_description = $this->objectResponse->items[0]->snippet->description;

            $this->live_video_published_at = $this->objectResponse->items[0]->snippet->publishedAt;
            $this->live_video_thumb_default = $this->objectResponse->items[0]->snippet->thumbnails->default->url;
            $this->live_video_thumb_medium = $this->objectResponse->items[0]->snippet->thumbnails->medium->url;
            $this->live_video_thumb_high = $this->objectResponse->items[0]->snippet->thumbnails->high->url;

            $this->channel_title = $this->objectResponse->items[0]->snippet->channelTitle;
            $this->embedCode();
        }
    }

    public function isLive($getOrNot = false) {
        if ($getOrNot==true) {
            $this->queryIt();
        }

        $live_items = count($this->objectResponse->items);

        if ($live_items>0) {
            $this->isLive = true;
            return true;
        } else {
            $this->isLive = false;
            return false;
        }
    }

    public function setEmbedSizeByWidth($width, $refill_code = true) {
        $ratio = $this->default_embed_width / $this->default_embed_height;
        $this->embed_width = $width;
        $this->embed_height = $width / $ratio;

        if ( $refill_code == true ) { $this->embedCode(); }
    }

    public function setEmbedSizeByHeight($height, $refill_code = true) {
                $ratio = $this->default_embed_width / $this->default_embed_height;
                $this->embed_height = $height;
                $this->embed_width = $height * $ratio;

        if ( $refill_code == true ) { $this->embedCode(); }
    }

    public function embedCode() {
        $autoplay = $this->embed_autoplay ? "?autoplay=1" : "";

        $this->embed_code = <<<EOT
<iframe
    width="{$this->embed_width}"
    height="{$this->embed_height}"
    src="//www.youtube.com/embed/{$this->live_video_id}{$autoplay}"
    frameborder="0"
    allowfullscreen>
</iframe>
EOT;

        return $this->embed_code;
    }
}
