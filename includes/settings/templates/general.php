<?php
/**
* General Options
*/

if ( ! defined( 'ABSPATH' ) ) exit;

ld_list_table();
/**
 * Fires based on the actions by the list table
 *
 * @return void
 */
function ld_list_table() {
	$action = isset( $_GET['action'] ) ? trim( $_GET['action'] ) : "";
<<<<<<< HEAD
	$action2 = isset( $_GET['action2'] ) ? trim( $_GET['action2'] ) : "";

	if( $action == "ld-vid-track-edit" ) {
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-user-details.php' ) ) {
			require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-user-details.php' );
		}
	} elseif( $action2 == "ld-vid-track-lessons"  ) {
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-course-lessons.php' ) ) {
			require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-course-lessons.php' );
		}
	} else {
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-users-views.php' ) ) {
			require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-users-views.php' );
		}
=======

	if( $action == "ld-vid-track-edit" ) {
		$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : "";
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-user-details.php' ) ) {
			require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-user-details.php' );
		}
	} else {
		ob_start();
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-users-views.php' ) ) {
			require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-users-views.php' );
		}
	

		$template = ob_get_contents();

		ob_end_clean();
		echo $template;
>>>>>>> 280e9b4b7f89f3a1888aaebf9776c08b14f85ba7
	}
}