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
 * Get Admin ids
 *
 * @return array
 */
function ld_vid_tracking_admin_user_ids() {
    //Grab wp DB
    global $wpdb;
    //Get all users in the DB
    $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");

    //Blank array
    $adminArray = array();
    //Loop through all users
    foreach ( $wp_user_search as $userid ) {
        //Current user ID we are looping through
        $curID = $userid->ID;
        //Grab the user info of current ID
        $curuser = get_userdata( $curID );
        //Current user level
        $user_level = $curuser->user_level;
        //Only look for admins
        if( $user_level >= 8 ) {//levels 8, 9 and 10 are admin
            //Push user ID into array
            $adminArray[] = $curID;
        }
    }

    return $adminArray;
}

/**
 * Get registered users for a given course
 *
 * @param [type] $course_id
 * @param [type] $args
 * @param boolean $ex_admin
 * @return array
 */
function ld_vid_tracking_get_users_for_course( $course_id, $args = array(), $ex_admin = true ) {
    $course_user_ids    = array();
    if ( empty( $course_id ) ) return $course_user_ids;
    $query_args         = "";
    $query_args         = wp_parse_args( $query_args, $args );
    
	if ( $ex_admin == true ) {
		$query_args['role__not_in'] = array('administrator');
	}
	
	$course_price_type = learndash_get_course_meta_setting( $course_id, 'course_price_type' );
    if ( $course_price_type == 'open' ) {
		
        $user_query = new WP_User_Query( $query_args );
        
		return $user_query;
	} else { 
        $course_access_list = learndash_get_course_meta_setting( $course_id, 'course_access_list');
        $course_user_ids    = array_merge( $course_user_ids, $course_access_list );

        $course_access_users = learndash_get_course_users_access_from_meta( $course_id );
        $course_user_ids     = array_merge( $course_user_ids, $course_access_users );
        
        $course_groups_users = get_course_groups_users_access( $course_id );
        $course_user_ids     = array_merge( $course_user_ids, $course_groups_users );
        //print_r($course_user_ids );
        if ( !empty( $course_user_ids ) )
            $course_user_ids = array_unique( $course_user_ids );

        $course_expired_access_users = learndash_get_course_expired_access_from_meta( $course_id );
        if ( !empty( $course_expired_access_users ) )
            $course_user_ids = array_diff( $course_access_list, $course_expired_access_users );

        
        if ( $ex_admin == false ) {
            $admin_ids       = [];
            $admin_ids       = ld_vid_tracking_admin_user_ids();
            $course_user_ids = array_merge( $course_user_ids, $admin_ids );
        }
        if ( !empty( $course_user_ids ) ) {
            $query_args['include'] = $course_user_ids;
            
            $user_query = new WP_User_Query( $query_args );
            
            $course_user_ids = $user_query->get_results();
            
            return $user_query;
        }
        
    }

    return $course_user_ids;
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

