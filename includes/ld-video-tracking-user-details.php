<?php
/**
 * Generates The User Grade Listing for Admin
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class LD_Detailed_Video_Tracking_List_Table_Class extends WP_List_Table {
    //define dataset for WP_List_Table => data
    /** Class constructor */
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Detailed Student Data', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //singular name of the listed records
            'plural'   => __( 'Detailed Students Datas', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ] );
    }
    
    /**
     * Function to filter data based on order , order_by & searched items
     *
     * @param string $orderby
     * @param string $order
     * @param string $search_term
     * @return array $users_array()
     */
    public function list_table_data_fun( $orderby='', $order='' , $search_term='' ) {
        $users_array = array();
        $args        = array();
        $users       ="";
        if( !empty( $search_term ) ) {
            $searchcol = array(
                'ID',
                'user_email',
                'user_login',
                'user_nicename',
                'user_url',
                'display_name'
            );
            
            $args  = array(
                'fields'         => 'all_with_meta', 
                'orderby'        => $orderby , 
                'order'          => $order , 
                'search'         => $_REQUEST["s"] ,
                'search_columns' => $searchcol
                );
            $users   = get_users( $args );
            $users   = $users->ID;
        } else {
                if( $order == "asc" && $orderby == "id" ) {
                    $args = array(
                        'orderby'=> 'ID',
                        'order'  => 'ASC',
                        'fields' =>	'ID',
                    ); 
            } elseif ( $order == "desc" && $orderby == "id"  ) {
                    $args = array(
                        'orderby'=> 'ID',
                        'order'  => 'DESC',
                        'fields' =>	'ID',
                    );
                
            } elseif ( $order == "desc" && $orderby == "title"  ) {
                    $args = array(
                        'orderby'   => 'name',
                        'order'     => 'DESC',
                        'fields'	=>	'ID',
                    );
            } elseif ( $order == "asc" && $orderby == "title"  ) {
                $args = array(
                    'orderby'   => 'name',
                    'order'     => 'ASC',
                    'fields'	=>	'ID',
                );
            } else {
                $args = array(
                    'orderby'   => 'ID',
                    'order'     => 'DESC',
                    'fields'	=>	'ID',
                );
            }
        }
        $user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : "";
        $courses     = learndash_user_get_enrolled_courses( $user_id );
        $author_info = get_userdata( $user_id );
        foreach( $courses as $course_id ) {
            $course_lesson_ids  = learndash_course_get_steps_by_type( $course_id, 'sfwd-lessons' );
            $course_topic_ids   = learndash_course_get_steps_by_type( $course_id, 'sfwd-topic' );
            $course_progression = array_merge( $course_lesson_ids, $course_topic_ids );
            $course_duration    = 0;
            $course_progress    = 0;
            foreach( $course_progression as $post_id ) {
                $key             = "ld-course-video-duration".$post_id;
                $video_data      = get_post_meta( $course_id, $key, true );
                if( $video_data ) {
                    $course_duration = $course_duration + $video_data["video_duration"];
                }
                $key             = $post_id."_".$user_id;
                $key             = "ld-video-tracking".$key;
                $video_data      = get_post_meta( $course_id, $key, true );
                if( $video_data ) {
                    $course_progress = $course_progress + $video_data['progress_in_sec'];
                }
            }

            if ( $course_duration > 0 &&  $course_progress > 0 ) {
                //delete_user_meta( $user->ID, $key, $user_video_data );
                $percent_progress = ( $course_progress/$course_duration ) * 100;
                $users_array[] = array(
                    "id"              => $user_id,
                    "title"           => '<b><a href="' .get_author_posts_url( $user_id ). '"> '. $author_info->display_name .'</a></b>' ,
                    "c_id"            => $course_id,
                    "course_title"    => get_the_title( $course_id ),
                    "course_duration" => gmdate( "h:i:s", $course_duration )."hr:min:sec",
                    "w_progress"      => gmdate( "h:i:s", $course_progress )."hr:min:sec",
                    "w_percentage"    => ( int )$percent_progress."%",
                );
            }
                
        }

        return $users_array;
    }
    //prepare_items
    public function prepare_items() {
        $orderby = isset( $_GET['orderby'] ) ? trim( $_GET['orderby'] ): "";
        $order   = isset( $_GET['order'] ) ? trim( $_GET['order'] ) : "";
        $search_term  = isset( $_POST['s'] ) ? trim( $_POST['s'] ) : "";
        $search_term  = isset( $_GET['s'] ) ? trim( $_GET['s'] ) : "";
        if( $search_term == "" ) {
            $search_term  = isset( $_GET['s'] ) ? trim( $_GET['s'] ) : "";
        }
    
        $datas        = $this->list_table_data_fun( $orderby, $order, $search_term );
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = count( $datas );
        $this->set_pagination_args( array( "total_items"=> $total_items,
            "per_page" => $per_page ) );
        $this->items = array_slice( $datas, ( ( $current_page - 1 )* $per_page ), $per_page );
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }
        //get_columns
    public function get_columns() {
        $columns = array(
            "cb"              => "<input type='checkbox'/>",
            "id"              => "ID",
            "title"           => "User Name",
            "c_id"            => "Course ID",
            "course_title"    => "Course Title",
            "course_duration" => "Course Duration",
            "w_progress"      => "Progress",
            "w_percentage"    => "Percentage",  
            "action"          => "Action"    
        );
        return $columns;
    }
    public function get_hidden_columns() {
        return array("");
    }
    public function column_title( $item ) {
		$action = array(
				"edit" => sprintf('<a href="?page=%s&action2=%s&course_id=%s&user_id=%s">View Lessons Details</a>', $_GET['page'], 'ld-vid-track-lessons',$item['c_id'],  $item['id'] ) );
		return sprintf('%1$s %2$s', $item['title'],$this->row_actions( $action ) );

    }
    
    public function get_sortable_columns() {
            return array (
            "title" => array( "title", true ),
            "id"    => array( "id", true ),
        );
    }   
    
    /**
     * Generate the table navigation above or below the table
     *
     * @since 3.1.0
     * @access protected
     *
     * @param string $which
     */
    protected function display_tablenav( $which ) {
        // REMOVED NONCE -- INTERFERING WITH SAVING POSTS ON METABOXES
        // Add better detection if this class is used on meta box or not.
        /*
        if ( 'top' == $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        }
        */
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions( $which ); ?>
            </div>
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>
            <br class="clear"/>
        </div>
    <?php
    }
    //column_default
    public function column_default( $item, $column_name ){
        switch ( $column_name ) {
            case 'id':
            case 'c_id':
    
            case 'title':
            case 'course_title':
            case 'course_duration':
                    
            case 'w_percentage':
                
            case 'w_progress':
            return $item[ $column_name ];
            case 'action':	
				return '<a href="?page='.$_GET['page'].'&action2=ld-vid-track-lessons&course_id='.$item['c_id'].'&user_id='.$item['id'].'">View Lessons Details</a>';
            
            default:
                return "no value";
                
        }
    }
}
/**
 * Shows the List table
 *
 * @return void
 */
function ld_detailed_video_tracking_list_table_layout() {
    $myRequestTable = new LD_Detailed_Video_Tracking_List_Table_Class();
    global $pagenow;
    ?>
    <div class="wrap"><h2>User Video Tracking Details</h2>
    <form method="get">
    <input type="hidden" name="page" value="<?php echo $pagenow ?>" />
    <?php if( isset( $myRequestTable ) ) : ?>
        <?php $myRequestTable->prepare_items();  ?>
        <?php $myRequestTable->display(); ?>
    <?php endif; ?>
    </form> <?php
}
ld_detailed_video_tracking_list_table_layout();