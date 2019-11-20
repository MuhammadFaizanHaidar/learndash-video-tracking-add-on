<?php
defined( 'ABSPATH' ) || exit;

/**
 * Manage MetaBox on the lesson,course & topics Edit Page
 */
class LD_Video_Tracking_Metabox {

	/**
	 * Constructor
	 */
	public function __construct() { 

        $this->hooks();
    }
    
    private function hooks() {

        add_action( 'add_meta_boxes', [ $this, 'register_ld_video_tracking_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'ld_video_tracking_add_query_string'], 100, 3 );
        add_action( 'post_updated', [ $this, 'ld_video_tracking_add_query_string' ], 10, 3 ); 
        add_action( 'save_post', [ $this, 'ld_video_tracking_save_video_url'], 100, 3 );
        add_action( 'post_updated', [ $this, 'ld_video_tracking_save_video_url' ], 10, 3 ); 
    }

    /**
     * Register the meta box for the gateway
     * @param void
     * @return void
     */
    public function register_ld_video_tracking_meta_boxes() {
        add_meta_box( 
            'ld-video-tracking-students-metaboxes-id', 
            'Students Video Tracking Information', 
            array( $this, 'show_students_video_data_listing_meta_box' ), 
            'sfwd-lessons', 
            'advanced', 
            'high'
        );

        add_meta_box( 
            'ld-video-tracking-students-metaboxes-id', 
            'Students Video Tracking Information', 
            array( $this, 'show_students_video_data_listing_meta_box' ), 
            'sfwd-topic', 
            'advanced', 
            'high'
        );

        add_meta_box( 
            'ld-video-tracking-students-metaboxes-id', 
            'Students Video Tracking Information', 
            array( $this, 'show_course_students_video_data_listing_meta_box' ), 
            'sfwd-courses', 
            'advanced', 
            'high'
        );
    }

    /**
     * Saves the video url of lessons & topics to the parent courses
     *
     * @param [type] $post_id
     * @param [type] $post
     * @param [type] $update
     * @return void
     */
    public function ld_video_tracking_save_video_url( $post_id, $post, $update ) {
        if ( $post->post_type == 'sfwd-lessons' || $post->post_type == 'sfwd-toppic' ) {
            $post_meta = "";
            $p_course  = "";
            $video_url = "";
            $video_enabled = "";
            if( $post->post_type == 'sfwd-lessons' ) {
				$post_meta = get_post_meta( $post_id, "_sfwd-lessons", true );
                $p_course  = $post_meta['sfwd-lessons_course'];
                $video_url = $post_meta['sfwd-lessons_lesson_video_url'];
                $video_enabled = $post_meta['sfwd-lessons_lesson_video_enabled'];
			} elseif( $post->post_type == 'sfwd-topic' ) {
				$post_meta = get_post_meta( $post_id, "_sfwd-topic", true );
                $p_course  = $post_meta['sfwd-topic_course'];
                $video_url = $post_meta['sfwd-topic_lesson_video_url'];
                $video_enabled = $post_meta['sfwd-topic_lesson_video_enabled'];
            }
            if ( !empty( $p_course ) ) {
                $video_data = [];
                $video_data["post_id"]   = $post_id;
                $video_data["video_url"] = $video_url;
                if ( $video_url != "" ) {
                    $video_data["video_duration"] =  get_video_details( $video_url );
                } else {
                    $video_data["video_duration"] =  ""; 
                }
                update_post_meta( $p_course, "ld-course-video-duration".$post_id, $video_data );
            }
        }
    }

    /**
     * Adds query string to the course,topic & lesson edit url
     *
     * @param [type] $post_id
     * @param [type] $post
     * @param [type] $update
     * @return void
     */
    public function ld_video_tracking_add_query_string( $post_id, $post, $update ) {
		$post_type    = get_post_type($post);
		$search_term  = isset( $_POST['s'] ) ? trim( $_POST['s'] ) : "";
		if ( $search_term == "" ) {

			$search_term  = isset( $_GET['s'] ) ? trim( $_GET['s'] ) : "";
		}
		if ( ( $post_type == 'sfwd-lessons' ||  $post_type == 'sfwd-topic' || $post_type == 'sfwd-courses' )  && $search_term != "" ) {

			wp_safe_redirect( add_query_arg( 's', $search_term, $_POST['_wp_http_referer'] ) );
			exit;
        }
        return;
	}

    public function show_students_video_data_listing_meta_box () {
       

        do_action( 'ld_video_tracking_before_tracking_data_listing' );

        if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-views.php' ) ) {
            require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-views.php' );
        }

        do_action( 'ld_video_tracking_after_tracking_data_listing' ); 
    }

    public function show_course_students_video_data_listing_meta_box () {
       

        do_action( 'ld_course_video_tracking_before_tracking_data_listing' );

        if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-course-views.php' ) ) {
            require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-course-views.php' );
        }

        do_action( 'ld_course_video_tracking_after_tracking_data_listing' ); 
    }
}

return new LD_Video_Tracking_Metabox();