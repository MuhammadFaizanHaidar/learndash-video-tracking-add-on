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
	}
}