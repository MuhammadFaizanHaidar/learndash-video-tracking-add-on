<?php
/**
 * LDVideo Tracking  Options
 *
 * Displays the LDVideo Tracking  Options.
 *
 * @author   WooTitans
 * @category Admin
 * @package  LDVideo Tracking Options /Plugin Options
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Class WOO_Titans_BQ_Options
 */
class WOO_Titans_LT_VID_Options {
    public $page_tab;
    /**
     * Hook in tabs.
     */
    public function __construct () {

        $this->page_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        add_action( 'admin_menu', [ $this, 'woo_titans_bq_menu' ] );
        add_action( 'admin_notices', [ $this, 'woo_titans_bq_admin_notices'] );
        add_filter ( 'admin_footer_text', [ $this, 'woo_titans_bq_remove_footer_admin' ] );
       // add_action( 'admin_post_woo_titans_bq_admin_settings', [ $this, 'woo_titans_bq_admin_settings_save' ] );
      
    }

   

    /**
     * Add plugin's menu
     */
    public function woo_titans_bq_menu() {
        add_submenu_page(
            'learndash-lms',
            __( 'LD Video Tracking', LD_VIDEO_TRACKING_TEXT_DOMAIN ),
            __( 'LD Video Tracking', LD_VIDEO_TRACKING_TEXT_DOMAIN ),
            'manage_options',
            'woo-titans-ld-vid-tracking-options',
            [ $this, 'woo_titans_ld_vid_tracking_options' ]
        );
    }

    /**
     * Fields Generator
     *
     * @param string $label
     * @param $name
     * @param $field_type
     * @param string $field_value
     * @param string $hint
     * @param string $before_text
     * @param string $after_text
     */
    public function create_fields( $label = '', $name, $field_type, $field_value = '', $checked = '', $hint = '', $before_text = '', $after_text = '' ) {

        if( empty( $field_type ) || is_null( $field_type ) ) return;
        if( empty( $name ) || is_null( $name ) ) return;

        if( 'checkbox' === $field_type ) {

            if( !empty( $label ) ) {
                echo '<td>';
                echo '<label for="'. $name .'" class="label">'. $label . '</label>';
                echo '</td>';
            } else {
                echo '';
            }

            echo '<td>';
            echo $before_text . ' <input type="' . $field_type . '" '. $checked .'  class="checkbox" id="'. $name .'" name="' . $name . '" /> ' .$after_text;
            if( !empty( $hint ) ) {
                echo '<span class="hint">'. $hint .'</span>';
            }
            echo '</td>';
        } elseif( 'text' === $field_type || 'number' === $field_type ) {
            echo '<td>';
            if( !empty( $label ) ) {
                echo '<label for="'. $name .'" class="label">'. $label . '</label>';
            } else {
                echo '&nbsp;';
            }
            echo '</td>';
            echo '<td>';
            $description_text = ( empty( $field_value ) ? 'Quiz Content' : $field_value );
            echo $before_text . ' <input type="' . $field_type . '" id="'. $name .'" value="' . $description_text . '" name="' . $name . '" /> ' .$after_text;
            if( !empty( $hint ) ) {
                echo '<span class="hint">'. $hint .'</span>';
            }
            echo '</td>';
        } elseif( 'textarea' === $field_type ) {
            if( !empty( $label ) ) {
                echo '<label for="'. $name .'" class="label-textarea">'. $label . '</label>';
            }
            echo $before_text . ' <textarea id="'. $name .'" cols="100" rows="7" name="' . $name . '" />'.$field_value.'</textarea> ' .$after_text;
            if( !empty( $hint ) ) {
                echo '<span class="hint">'. $hint .'</span>';
            }
        } elseif( 'radio' === $field_type ) {
            echo $before_text . ' <input type="' . $field_type . '" '. $checked .' class="'. $name .'" value="' . $field_value . '" name="' . $name . '" /> ' .$after_text;
            if( !empty( $hint ) ) {
                echo '<span class="hint">'. $hint .'</span>';
            }
        }
    }

    /**
     * Setting page data
     */
    public function woo_titans_ld_vid_tracking_options() {
        ?>
        <div id="wrap" class="boswoo-settings-wrapper">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php echo __( 'View Users', LD_VIDEO_TRACKING_TEXT_DOMAIN ); ?></h1>

            <div class="nav-tab-wrapper">
                <?php
                $woo_bg_settings_sections = $this->woo_titans_bq_get_setting_sections();
                foreach( $woo_bg_settings_sections as $key => $woo_bg_settings_section ) {
                    ?>
                    <a href="?page=woo-titans-ld-vid-tracking-options&tab=<?php echo $key; ?>"
                       class="nav-tab <?php echo $this->page_tab == $key ? 'nav-tab-active' : ''; ?>">
                        <i class="fa <?php echo $woo_bg_settings_section['icon']; ?>" aria-hidden="true"></i>
                        <?php _e( $woo_bg_settings_section['title'], LD_VIDEO_TRACKING_TEXT_DOMAIN ); ?>
                    </a>
                    <?php
                }
                ?>
            </div>

            <?php
            foreach( $woo_bg_settings_sections as $key => $woo_bg_settings_section ) {
                if( $this->page_tab == $key ) {
                    include( 'templates/' . $key . '.php' );
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * BadgeOS WooCommerce Settings Sections
     *
     * @return mixed|void
     */
    public function woo_titans_bq_get_setting_sections() {

        $woo_titans_bq_settings_sections = array(
            'general' => array(
                'title' => __( 'General Options', LD_VIDEO_TRACKING_TEXT_DOMAIN ),
                'icon' => 'fa-hashtag',
            ),
        );

        return apply_filters( 'woo_titans_ld_vid_tracking_settings_sections', $woo_titans_bq_settings_sections );
    }

    /**
     * Save Plugin's Settings
     */
    public function save_woo_titans_bq_settings() {

    }

    /**
     * Add footer branding
     *
     * @param $footer_text
     * @return mixed
     */
    function woo_titans_bq_remove_footer_admin ( $footer_text ) {
        if( isset( $_GET['page'] ) && ( $_GET['page'] == 'woo-titans-ld-vid-tracking-options' ) ) {
            _e( 'Fueled by <a href="http://www.wordpress.org" target="_blank">WordPress</a> | developed and designed by 
            <a href="https://profiles.wordpress.org/muhammadfaizanhaidar/" 
            target="_blank">Muhammad Faizan Haidar</a></p>', 
            LD_VIDEO_TRACKING_TEXT_DOMAIN 
            );
        } else {
            return $footer_text;
        }
    }
}

$GLOBALS['WOO_Titans_LT_VID_Options'] = new WOO_Titans_LT_VID_Options();
