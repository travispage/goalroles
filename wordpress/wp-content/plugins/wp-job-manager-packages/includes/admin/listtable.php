<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class WPJM_Pack_Admin_ListTable
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_ListTable extends WP_List_Table {

	/**
	 * @var \WP_Job_Manager_Packages
	 */
	public $core;

	/**
	 * WPJM_Pack_Admin_ListTable constructor.
	 *
	 * @param array|string $core
	 */
	public function __construct( $core ){

		$this->core = $core;

		parent::__construct( array(
			'singular' => 'package',
			'plural'   => 'packages',
			'ajax'     => false
		) );
	}

	/**
	 * Output Limits Column Data
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_limits( $item ){

		if( $item->package_type === 'job_listing' ){

			$job_types = $this->core->job->packages->get_package_types();
			$output    = array();

			if( ! empty( $job_types ) ){

				foreach( (array) $job_types as $job_type => $jtc ) {

					$allow = "allow_{$job_type}";

					if( ! $item->$allow ){
						continue;
					}

					$limit = "{$job_type}_limit";
					$used  = "{$job_type}_used";
					$posts = "{$job_type}_posts";

					$posted = __( 'Unlimited', 'wp-job-manager-packages' );

					if( isset( $item->$limit ) && $item->$limit ){
						$posts_array = maybe_unserialize( $item->$posts );

						$posted_out_of = sprintf( __( '%s Used', 'wp-job-manager-packages' ), absint( $item->$used ) . ' / ' . absint( $item->$limit ) );
						$posted = $item->$used ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=job_listing&show_ids=' . implode( ',', array_keys( $posts_array ) ) ) ) . '">' . $posted_out_of . '</a>' : $posted_out_of;
					}

					$output[] = "{$jtc['label']}: {$posted}";
				}

			}

		}

		if( $item->package_type === 'resume' ){

			$resume_types = $this->core->resume->packages->get_package_types();
			$output    = array();

			if( ! empty( $resume_types ) ){

				foreach( (array) $resume_types as $resume_type => $rtc ) {

					$allow = "allow_{$resume_type}";

					if( ! $item->$allow ){
						continue;
					}

					$limit = "{$resume_type}_limit";
					$used  = "{$resume_type}_used";
					$posts = "{$resume_type}_posts";

					$posted = __( 'Unlimited', 'wp-job-manager-packages' );

					if( isset( $item->$limit ) && $item->$limit ){
						$posts_array = maybe_unserialize( $item->$posts );

						$posted_out_of = sprintf( __( '%s Used', 'wp-job-manager-packages' ), absint( $item->$used ) . ' / ' . absint( $item->$limit ) );

						$posted = $item->$used ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=resume&show_ids=' . implode( ',', array_keys( $posts_array ) ) ) ) . '">' . $posted_out_of . '</a>' : $posted_out_of;
					}

					$output[] = "{$rtc['label']}: {$posted}";
				}

			}

		}

		return implode( '<br/>', $output );
	}

	/**
	 * Default Column Value Output Handling
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string|void
	 */
	public function column_default( $item, $column_name ) {
		global $wpdb;

		switch( $column_name ) {
			case 'product_id' :
				$product = wc_get_product( $item->product_id );

				return $product ? '<a href="' . admin_url( 'post.php?post=' . absint( $product->get_id() ) . '&action=edit' ) . '">' . esc_html( $product->get_title() ) . '</a>' : __( 'n/a', 'wp-job-manager-packages' );
			case 'user_id' :
				$user = get_user_by( 'id', $item->user_id );

				if ( $item->user_id && $user ) {
					return '<a href="' . admin_url( 'user-edit.php?user_id=' . absint( $item->user_id ) ) . '">' . esc_attr( $user->display_name ) . '</a><br/><span class="description">' . esc_html( $user->user_email ) . '</span>';
				} else {
					return __( 'n/a', 'wp-job-manager-packages' );
				}
			case 'status' :
				return $item->status;
			case 'order_id' :
				return $item->order_id > 0 ? '<a href="' . admin_url( 'post.php?post=' . absint( $item->order_id ) . '&action=edit' ) . '">#' . absint( $item->order_id  ) . ' &rarr;</a>' : __( 'n/a', 'wp-job-manager-packages' );
			case 'limit' :
				return '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . ( 'resume' === $item->package_type ? 'resume' : 'job_listing' ) . '&package=' . absint( $item->id ) ) ) . '">' . ( $item->view_limit ? sprintf( __( '%s Used', 'wp-job-manager-packages' ), absint( $item->package_count ) . ' / ' . absint( $item->view_limit ) ) : __( 'Unlimited', 'wp-job-manager-packages' ) ) . '</a>';
			case 'package_type' :
				$product = wc_get_product( $item->product_id );
				if( empty( $product ) || ! is_object( $product ) ){
					return __( 'Unknown', 'wp-job-manager-packages' );
				}
				$sub_label = $product->is_subscription() ? __( 'Subscription', 'wp-job-manager-packages' ) . ' ' : '';
				$post_label = job_manager_get_post_type_label( $item->package_type );
				return "{$post_label} {$sub_label}" . __( 'Package', 'wp-job-manager-packages' );
			case 'job_actions' :
				return '<div class="actions">
					<a class="button button-icon icon-edit" href="' . esc_url( add_query_arg( array( 'action' => 'edit', 'package_id' => $item->id ), admin_url( 'users.php?page=job_manager_packages' ) ) ) . '">' . __( 'Edit', 'wp-job-manager-packages' ) . '</a>
					<a class="button button-icon icon-delete" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'package_id' => $item->id ), admin_url( 'users.php?page=job_manager_packages' ) ), 'delete', 'delete_nonce' ) . '">' . __( 'Delete', 'wp-job-manager-packages' ) . '</a></div>
				</div>';
		}
	}

	/**
	 * Return Columns to Show
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns(){

		$columns = array(
			'user_id'      => __( 'User', 'wp-job-manager-packages' ),
			'package_type' => __( 'Type', 'wp-job-manager-packages' ),
			'status'       => __( 'Status', 'wp-job-manager-packages' ),
			'product_id'   => __( 'Product', 'wp-job-manager-packages' ),
			'limits'       => __( 'Limits', 'wp-job-manager-packages' ),
			'order_id'     => __( 'Order ID', 'wp-job-manager-packages' ),
			'job_actions'  => __( 'Actions', 'wp-job-manager-packages' )
		);
		return $columns;
	}

	/**
	 * Sortable Columns
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'order_id'     => array( 'order_id', false ),
			'user_id'      => array( 'user_id', true ),
			'product_id'   => array( 'product_id', false ),
			'package_type' => array( 'package_type', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param string $which
	 */
	public function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			return;
		}
		parent::display_tablenav( $which );
	}

	/**
	 * Prepare Items for Output
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function prepare_items() {
		global $wpdb;

		$current_page          = $this->get_pagenum();
		$per_page              = 50;
		$orderby               = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'user_id';
		$order                 = empty( $_REQUEST['order'] ) || $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';
		$order_id              = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';
		$user_id               = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : '';
		$product_id            = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : '';
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$where                 = array( 'WHERE 1=1' );

		if ( $order_id ) {
			$where[] = 'AND order_id=' . $order_id;
		}
		if ( $user_id ) {
			$where[] = 'AND user_id=' . $user_id;
		}
		if ( $product_id ) {
			$where[] = 'AND product_id=' . $product_id;
		}

		$db_table    = WPJM_Pack_User::$db_table;
		$where       = implode( ' ', $where );
		$max         = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}{$db_table} $where;" );
		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$db_table} $where ORDER BY `{$orderby}` {$order} LIMIT %d, %d", ( $current_page - 1 ) * $per_page, $per_page ) );

		$this->set_pagination_args( array(
			'total_items' => $max,
			'per_page'    => $per_page,
			'total_pages' => ceil( $max / $per_page )
		) );
	}
}