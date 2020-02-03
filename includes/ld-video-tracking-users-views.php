<?php
/**
 * Generates The User Grade Listing for Admin
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Lt_List_Table_Class extends WP_List_Table {
	//define dataset for WP_List_Table => data

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'LT Student', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //singular name of the listed records
			'plural'   => __( 'LT Students', LD_VIDEO_TRACKING_TEXT_DOMAIN ), //plural name of the listed records
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
			$searchcol= array(
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
				'search'         =>$_REQUEST["s"] ,
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
                    $author_info   = get_userdata( $user->ID );
                    $users_array[] = array(
                        "id"         => $user->ID,
                        "title"      => '<b><a href="' .get_author_posts_url( $user->ID ). '"> '. $author_info->display_name .'</a></b>' ,
                    );
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
	
		$datas        = $this->list_table_data_fun( $orderby, $order, $search_term );


		$per_page     = 100;
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
			"cb"         => "<input type='checkbox'/>",
			"id"         => "ID",
			"title"      => "User Name",
			"action"     => "Action"		
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

	//column_default
	public function column_default( $item, $column_name ){
		switch ( $column_name ) {
			case 'id':

				
			case 'title':
				
			return $item[ $column_name ];
			case 'action':	
				return '<a href="?page='.$_GET['page'].'&action=ld-vid-track-edit&user_id='.$item['id'].'">View Details</a>';
			
			default:
				return "no value";
				
		}

	}

	public function column_title( $item ) {
		$action = array(
				"edit" => sprintf('<a href="?page=%s&action=%s&user_id=%s">View Details</a>',$_GET['page'],'ld-vid-track-edit',$item['id']));
		return sprintf('%1$s %2$s', $item['title'],$this->row_actions( $action ) );

	}
}

/**
 * Shows the List table
 *
 * @return void
 */
function lt_vid_list_table_layout() {
	$myRequestTable = new Lt_List_Table_Class();
	?>
	<div class="wrap"><h2>Users</h2>
	<form method="post">
	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
	<?php if( isset( $myRequestTable ) ) : ?>
		<?php $myRequestTable->prepare_items();  ?>
		<?php $myRequestTable->search_box( __( 'Search Users' ), 'students' ); //Needs To be called after $myRequestTable->prepare_items() ?>
		<?php $myRequestTable->display(); ?>
	<?php endif; ?>
	</form> <?php

}

lt_vid_list_table_layout();