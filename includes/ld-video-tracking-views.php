<?php
/**
 * Generates The User Grade Listing for Admin
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class LD_Video_Tracking_List_Table_Class extends WP_List_Table {
	//define dataset for WP_List_Table => data

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Student', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //singular name of the listed records
			'plural'   => __( 'Students', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //plural name of the listed records
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
			} else {
					if( $order == "asc" && $orderby == "id" ) {
						$args = array(
							'orderby'      => 'ID',
							'order'        => 'ASC',
						); 
				} elseif ( $order == "desc" && $orderby == "id"  ) {
						$args = array(
							'orderby'      => 'ID',
							'order'        => 'DESC',
						);
					
				} elseif ( $order == "desc" && $orderby == "title"  ) {
						$args = array(
							'orderby'      => 'name',
							'order'        => 'DESC',
						);
				} elseif ( $order == "asc" && $orderby == "title"  ) {
					$args = array(
						'orderby'      => 'name',
						'order'        => 'ASC',
					);
				} else {
					$args = array(
						'orderby'      => 'ID',
						'order'        => 'DESC',
					);
				}
			
			}
			
			$users = get_users( $args );
			
			if( count( $users ) > 0 ) {
				foreach ( $users as $index => $user) {
					$author_info = get_userdata( $user->ID );
					$post_id     =  get_the_ID();
					$key         = $post_id."_".$user->ID;
					$user_video_data = get_user_meta( $user->ID, $key, true );
					if ( $user_video_data ) {
						//delete_user_meta( $user->ID, $key, $user_video_data );
						$users_array[] = array(
							"id"          => $user->ID,
							"title"       => '<b><a href="' .get_author_posts_url( $user->ID ). '"> '. $author_info->display_name .'</a></b>' ,
							"video_title" => $user_video_data['video_title'],
							"video_length"=> gmdate( "i:s", $user_video_data['video_duration'] )."min:sec",
							"w_progress"  => gmdate( "i:s", $user_video_data['progress_in_sec'] )."min:sec",
							"w_percentage"=> $user_video_data['progress_in_percentage']."%",
						);
					}
					# code...
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
		$total_items  = count($datas);

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
			"cb"           => "<input type='checkbox'/>",
			"id"           => "ID",
			"title"        => "User Name",
			"video_title"  => "Video Title",
			"video_length" => "Video Duration",
			"w_progress"   => "Progress",
			"w_percentage" => "Percentage",		
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array("");
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
	
			case 'title':

			case 'video_title':

			case 'video_length':
					
			case 'w_progress':

			case 'w_percentage':
			return $item[ $column_name ];
			
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
function ld_video_tracking_list_table_layout() {
	$myRequestTable = new LD_Video_Tracking_List_Table_Class();
	global $pagenow;
	?>
	<div class="wrap"><h2>Video Progression Data</h2>
	<form method="get">
	<input type="hidden" name="page" value="<?php echo $pagenow ?>" />
	<?php if( isset( $myRequestTable ) ) : ?>
		<?php $myRequestTable->prepare_items();  ?>
		<?php $myRequestTable->search_box( __( 'Search Students By ID' ), 'students' ); //Needs To be called after $myRequestTable->prepare_items() ?>
		<?php $myRequestTable->display(); ?>
	<?php endif; ?>
	</form> <?php

}

ld_video_tracking_list_table_layout();