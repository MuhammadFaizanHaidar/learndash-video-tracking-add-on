<?php
/**
 * Get CURL
 *
 * @param [type] $url
 * @return void
 */
function curl_get( $url ) {
    $curl = curl_init( $url );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
    $return = curl_exec( $curl );
    curl_close( $curl );

    return $return;
}

/**
 * Get Utube Video id from url
 *
 * @param [type] $url
 * @return mix id
 */
function youtube_id_from_url( $url ) {
    $pattern = '%^# Match any youtube URL
    (?:https?://)? # Optional scheme. Either http or https
    (?:www\.)? # Optional www subdomain
    (?: # Group host alternatives
    youtu\.be/ # Either youtu.be,
    | youtube\.com # or youtube.com
    (?: # Group path alternatives
    /embed/ # Either /embed/
    | /v/ # or /v/
    | /watch\?v= # or /watch\?v=
    ) # End path alternatives.
    ) # End host alternatives.
    ([\w-]{10,12}) # Allow 10-12 for 11 char youtube id.$%x';
    $result = preg_match($pattern, $url, $matches);

    if ( false !== $result ) {

        return $matches[1];
    }

    return false;
}

/**
 * Get Video Length
 *
 * @param string $videoid
 * @return video_length
 */
function get_video_length( $videoid='' ) {
    define('YT_API_URL', 'http://gdata.youtube.com/feeds/api/videos?q=');
    $video_id = $videoid;
    //Using cURL php extension to make the request to youtube API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, YT_API_URL . $video_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //$feed holds a rss feed xml returned by youtube API
    $feed = curl_exec($ch);
    curl_close($ch);
    
    //Using SimpleXML to parse youtube's feed
    $xml = simplexml_load_string($feed);
    $entry = $xml->entry[0];
    $media = $entry->children('media', true);
    $group = $media->group;
    $vid_duration = $group['duration'];
    $duration_formatted = str_pad( floor( $vid_duration / 60 ), 2, 
    '0', STR_PAD_LEFT) . ':' . str_pad( $vid_duration % 60, 2, '0', STR_PAD_LEFT );

    return $vid_duration;
}
    
/**
 * Get Video Details
 *
 * @param [type] $url
 * @return Video_Length
 */
function get_video_details( $url ) {
    $video_url = parse_url( $url );
    if ( $video_url['host'] == 'www.youtube.com' || $video_url['host'] == 'youtube.com' ) {
        $videoid = youtube_id_from_url($url);
        $video_length = get_video_length($videoid);

        return $video_length;
    } else if ( $video_url['host'] == 'www.youtu.be' || $video_url['host'] == 'youtu.be' ) {
        $videoid      = youtube_id_from_url( $url );
        $video_length = get_video_length( $videoid );

        return $video_length;
    } else if ( $video_url['host'] == 'www.vimeo.com' || $video_url['host'] == 'vimeo.com' ) {
        $oembed_endpoint = 'http://vimeo.com/api/oembed';
        $json_url  = $oembed_endpoint.'.json?url='.rawurlencode( $url ).'&width=640';
        $video_arr = curl_get( $json_url ) ;
        $video_arr = json_decode( $video_arr, TRUE );
        $vid_duration = $video_arr['duration'];
        return $vid_duration;

    }
}