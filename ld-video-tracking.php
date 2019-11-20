<?php
/**
 * Plugin Name: LearnDash Video Tracking Add-on
 * Plugin URI:  https://profiles.wordpress.org/muhammadfaizanhaidar/
 * Description: This addon will provide video tracking for LearnDash courses, lessons & topics
 * Version:     1.0
 * Author:      Muhammad Faizan Haidar
 * Author URI:  https://profiles.wordpress.org/muhammadfaizanhaidar/
 * Text Domain: ld-video-tracking
 * License: 	GNU AGPL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_activation_hook( __FILE__, [ 'LD_Video_Tracking', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'LD_Video_Tracking', 'deactivation' ] );

/**
 * Class LD_Video_Tracking
 */
class LD_Video_Tracking {

	const VERSION = '1.0';

	/**
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @return LD_Video_Tracking
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof LD_Video_Tracking ) ) {
			self::$instance = new self;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Activation function hook
	 *
	 * @return void
	 */
	public function activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {

			return;
		}

		update_option( 'ld_video_tracking_version', self::VERSION );
		$default_values = get_option( 'ld_video_tracking_version' );
		if ( empty( $default_values ) ) {
			$form_data = [];
			update_option( 'ld_video_tracking_version', $form_data );
		}
	}

	/**
	 * Deactivation function hook
	 *
	 * @return void
	 */
	public function deactivation() {
		delete_option( 'ld_video_tracking_version' );

		return false;
	}

	/**
	 * Upgrade function hook
	 *
	 * @return void
	 */
	public function upgrade() {
		if ( get_option( 'ld_video_tracking_version' ) != self::VERSION ) {
		}
	}

	/**
	 * Setup Constants
	 */
	private function setup_constants() {

		/**
		 * Plugin Text Domain
		 */
		define( 'LD_VIDEO_TRACKING_TEXT_DOMAIN', 'ld-video-tracking' );

		/**
		 * Plugin Directory
		 */
		define( 'LD_VIDEO_TRACKING_DIR', plugin_dir_path( __FILE__ ) );
		define( 'LD_VIDEO_TRACKING_DIR_FILE', LD_VIDEO_TRACKING_DIR . basename( __FILE__ ) );
		define( 'LD_VIDEO_TRACKING_INCLUDES_DIR', trailingslashit( LD_VIDEO_TRACKING_DIR . 'includes' ) );
		define( 'LD_VIDEO_TRACKING_TEMPLATES_DIR', trailingslashit( LD_VIDEO_TRACKING_DIR . 'templates' ) );
		define( 'LD_VIDEO_TRACKING_BASE_DIR', plugin_basename( __FILE__ ) );

		/**
		 * Plugin URLS
		 */
		define( 'LD_VIDEO_TRACKING_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
		define( 'LD_VIDEO_TRACKING_ASSETS_URL', trailingslashit( LD_VIDEO_TRACKING_URL . 'assets' ) );
	}

	/**
	 * Pugin Include Required Files
	 */
	private function includes() {

		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-metabox.php' ) ) {
            require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-metabox.php' );
		}
		
		if( file_exists( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-functions.php' ) ) {
            require_once ( LD_VIDEO_TRACKING_INCLUDES_DIR . 'ld-video-tracking-functions.php' );
        }
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts',    [ $this, 'frontend_enqueue_scripts' ], 11 );
		add_action( 'plugins_loaded',        [ $this, 'upgrade' ] );
		add_filter( 'learndash_lesson_video_data',[ $this, 'ld_lesson_video_filter' ], 99, 2 );
		add_action( 'wp_ajax_video_tracking_ajax_action',        array( $this, 'ld_video_tracking_ajax_action' ), 10 );
        add_action( 'wp_ajax_nopriv_video_tracking_ajax_action', array( $this, 'ld_video_tracking_ajax_action' ), 10);
	}

	/**
	 * Viddeo Tracking Ajax Action
	 *
	 * @return string
	 */
	public function ld_video_tracking_ajax_action() {
		$ended       = false;
		$post_id     = sanitize_text_field( $_POST['pid'] );
		$user_id     = sanitize_text_field( $_POST['uid'] );
		$post_type   = sanitize_text_field( $_POST['p_type'] );
		$parent_course = sanitize_text_field( $_POST['parent_course'] );
		$meta_key      = $key = $post_id."_".$user_id;
		
		$video_title = sanitize_text_field( $_POST['video_title'] );
		$v_duration  = sanitize_text_field( $_POST['v_length'] );
		$v_per       = sanitize_text_field( $_POST['v_percent'] );
		$v_prog      = sanitize_text_field( $_POST['v_progress'] );
		$video_ended = sanitize_text_field( $_POST['video_ended'] );
		$video_data  = [
			"post_type"       => $post_type,
			"post_id"	      => intval( $post_id ),
			"user_id"		  => intval( $user_id ),
			"video_title"     => $video_title,
			"video_ended"     => $video_ended,
			"video_duration"  => $v_duration,
			"progress_in_sec" => $v_prog,
			"progress_in_percentage" => $v_per*100,
		];
		if ( isset( $_POST['video_ended'] ) ) {
			$ended   = sanitize_text_field( $_POST['video_ended'] );
			if ( $ended == true ) {
				$user_video_data = get_user_meta( $user_id, $key, true );
				if ( $user_video_data['video_ended'] == false ) {
					//delete_user_meta( $user_id, $key, $user_video_data );
					$meta_updated = update_user_meta( $user_id, $meta_key, $video_data );
					//delete_post_meta( $parent_course, "ld-video-duration".$post_id );
					update_post_meta( intval( $parent_course ), "ld-video-tracking".$key, $video_data );
					if ( $meta_updated ) {
						echo "meta updated ";
						exit;
					} else {
						echo "meta not updated";
						exit;
					}
				} else {
					echo "already ended";
					exit;
				}
			}
		}

		$user_video_data = get_user_meta( $user_id, $key, true );
		$meta_updated    = false;
		if ( ( empty( $user_video_data ) || $user_video_data['video_ended'] == false ) && ( $user_video_data['progress_in_sec'] != $v_prog || empty( $user_video_data ) ) ) {
			//delete_user_meta( $user_id, $key, $user_video_data );
			$meta_updated = update_user_meta( $user_id, $meta_key, $video_data );
			//delete_post_meta( $parent_course, "ld-video-duration".$post_id );
			update_post_meta( intval( $parent_course ), "ld-video-tracking".$key, $video_data );
			if ( $meta_updated ) {
				if ( $user_video_data['video_ended'] == false ) {
					$show = "yes";
				}

				echo "post_type:".$post_type.""."p_id:".$post_id.""."u_id:".$user_id.""."v_title:".$video_title."video_ended:".$show;
				echo "";
				echo "video duration:".$v_duration.""."video percentage:".$v_per.""."video_prog:".$v_prog;
				exit;
			} else {

				echo "Not updated";
				exit;
			}
		} else {

			echo "already tracked";
			exit;
		}
		exit;
	}


	/**
	 * Overrides LearnDash Lesson Video Filter
	 *
	 * @param [type] $video_data
	 * @param [type] $settings
	 * @return $video_data
	 */
	public function ld_lesson_video_filter( $video_data, $settings ) {
		global $post;
		if ( !is_singular( $post->post_type ) ) {
			return;
		}

		if ( !is_user_logged_in() ) {
			return;
		}

		if( $post->post_type != "sfwd-lessons" && $post->post_type != "sfwd-topic" ) {
			return;
		}

		if ( $video_data['videos_found_provider'] !== false ) {

			wp_enqueue_script( 
				'ld-video-tracking-script', 
				LD_VIDEO_TRACKING_ASSETS_URL . 'js/ld-video-tracking-script.js', 
				array( 'jquery' ), 
				self::VERSION,
				true 
			);

			//error_log('local: video_data<pre>'. print_r($this->video_data, true) .'</pre>');
			$post_id                 = get_the_ID();
			$video_data["post_id"]   = $post_id;  
			$video_data["user_id"]   = get_current_user_id();
			$video_data["post_type"] = $post->post_type;
			$video_data["ajax_url"]  = admin_url( 'admin-ajax.php' );
			$p_course                = -1;
			$post_meta               = "";
			if( $post->post_type == "sfwd-lessons" ) {
				$post_meta = get_post_meta( $post_id, "_sfwd-lessons", true );
				$p_course  = $post_meta['sfwd-lessons_course'];
			} elseif( $post->post_type == "sfwd-topic" ) {
				$post_meta = get_post_meta( $post_id, "_sfwd-topic", true );
				$p_course  = $post_meta['sfwd-topic_course'];
			}

			$video_data["parent_course"]  = $p_course;
			wp_localize_script( 'ld-video-tracking-script', 
				'ld_video_tracking_video_data', 
				$video_data 
			);
			if ( $video_data['videos_found_provider'] == 'youtube' ) {
				wp_enqueue_script( 'youtube_iframe_api', 
					'https://www.youtube.com/iframe_api', 
					array( 'ld-video-tracking-script' ), 
					'1.0', 
					true 
				);
			} else if ( $video_data['videos_found_provider'] == 'vimeo' ) {
				wp_enqueue_script( 'vimeo_iframe_api', 
					'https://player.vimeo.com/api/player.js',
					array( 'ld-video-tracking-script' ), 
					null, 
					true 
				);
			}
		}
		return $video_data;
	}

	/**
	 * Translate the "Plugin activated." string
	 *
	 * @param [type] $translated_text
	 * @param [type] $untranslated_text
	 * @param [type] $domain
	 *
	 * @return void
	 */
	public function activation_message( $translated_text, $untranslated_text, $domain ) {
		$old = array(
			"Plugin <strong>activated</strong>.",
			"Selected plugins <strong>activated</strong>." 
		);
	
		$new = "The Core is stable and the Plugin is <strong>deactivated</strong>";
		
		if ( ! class_exists( 'SFWD_LMS' ) && in_array( __( $untranslated_text, LD_VIDEO_TRACKING_TEXT_DOMAIN ), __( $old, LD_VIDEO_TRACKING_TEXT_DOMAIN ), true ) ) {
			$translated_text = __( $new, LD_VIDEO_TRACKING_TEXT_DOMAIN );
			remove_filter( current_filter(), __FUNCTION__, 99 );
		}
		
		return $translated_text;
	}

	/**
	 * Enqueue scripts on admin
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		$screen          = get_current_screen();
		/**
		 * plugin's admin script
		 */
		wp_enqueue_script( 
			'ld-video-tracking-admin-script', 
			LD_VIDEO_TRACKING_ASSETS_URL . 'js/ld-video-tracking-admin-script.js', 
			[ 'jquery' ], 
			self::VERSION, 
			true 
		);

		
		/**
		 * plugin's admin style
		 */
		wp_enqueue_style(
			'ld-video-tracking-admin-style', 
			LD_VIDEO_TRACKING_ASSETS_URL . 'css/ld-video-tracking-admin-style.css', 
			self::VERSION, 
			null 
		);
		
	}

	/**
	 * Enqueue scripts on frontend
	 */
	public function frontend_enqueue_scripts() {
		/**
		 * plugin's frontend script
		 */

		global $post;
		
		if( $post->post_type == "sfwd-lessons" || $post->post_type != "sfwd-topic" ) {
			
			wp_enqueue_script(
				'ld-video-tracking-front-script', 
				LD_VIDEO_TRACKING_ASSETS_URL . 'js/ld-video-tracking-front-script.js', 
				[ 'jquery' ], 
				self::VERSION, 
				true 
			);
			wp_enqueue_style( 
				'ld-video-tracking-front-style', 
				LD_VIDEO_TRACKING_ASSETS_URL . 'css/ld-video-tracking-front-style.css', 
				self::VERSION, 
				null 
			);

			wp_localize_script( 
				'ld-video-tracking-front-script', 
				'ld_video_tracking_ajax_url',
				[
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				]
			);
		}
	}

}

/**
 * Display admin notifications if dependency not found.
 */
function ld_video_tracking_ready() {
	if ( ! is_admin() ) {

		return;
	}

	if ( ! class_exists( 'SFWD_LMS' ) ) {
		$class   = 'notice is-dismissible error';
		$message = __( 'LearnDash Video Tracking add-on requires <a href="https://www.learndash.com" target="_BLANK">LearnDash</a> plugin to be activated.', 'ld-video-tracking' );
		printf( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	return true;
}

/**
 * @return bool
 */
function LD_Video_Tracking() {
	if ( ! class_exists( 'SFWD_LMS' ) ) {
		add_action( 'admin_notices', 'ld_video_tracking_ready' );

		return false;
	}

	$GLOBALS['LD_Video_Tracking'] = LD_Video_Tracking::instance();
}

add_action( 'plugins_loaded', 'LD_Video_Tracking' );
