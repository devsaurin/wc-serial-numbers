<?php
defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_List_Table extends \WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of items
	 * @var string
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Sold number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $sold_count;

	/**
	 * Refunded number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $refunded_count;

	/**
	 * Expired number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $expired_count;

	/**
	 * Expired number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $failed_count;

	/**
	 * Cancelled number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $cancelled_count;

	/**
	 * available number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $available_count;

	/**
	 * Inactive number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $inactive_count;

	/**
	 * Base URL
	 * @var string
	 */
	public $base_url;


	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Serial', 'wc-serial-number' ),
			'plural'   => __( 'Serials', 'wc-serial-number' ),
			'ajax'     => false,
		) );
		$this->base_url = admin_url( 'admin.php?page=wc-serial-numbers' );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->get_results();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch ( $status ) {
			case 'available':
				$total_items = $this->available_count;
				break;
			case 'sold':
				$total_items = $this->sold_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'cancelled':
				$total_items = $this->cancelled_count;
				break;
			case 'expired':
				$total_items = $this->expired_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @return array $views All the views sellable
	 * @since 1.0.0
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$refunded_count  = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$failed_count    = '&nbsp;<span class="count">(' . $this->failed_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$inactive_count  = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';

		$views = array(
			'all'       => sprintf( '<a href="%s" title="%s" %s>%s</a>', remove_query_arg( 'status', $this->base_url ), __( 'All serial numbers', 'wc-serial-numbers' ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'available', $this->base_url ), __( 'Available for sell', 'wc-serial-numbers' ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'sold'      => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'sold', $this->base_url ), __( 'Sold & active serial numbers', 'wc-serial-numbers' ), $current === 'sold' ? ' class="current"' : '', __( 'Sold', 'wc-serial-numbers' ) . $sold_count ),
			'refunded'  => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'refunded', $this->base_url ), __( 'Refunded serial numbers', 'wc-serial-numbers' ), $current === 'refunded' ? ' class="current"' : '', __( 'Refunded', 'wc-serial-numbers' ) . $refunded_count ),
			'cancelled' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'cancelled', $this->base_url ), __( 'Cancelled serial numbers', 'wc-serial-numbers' ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'wc-serial-numbers' ) . $cancelled_count ),
			'expired'   => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'expired', $this->base_url ), __( 'Expired serial numbers', 'wc-serial-numbers' ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count ),
			'failed'    => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'failed', $this->base_url ), __( 'Expired serial numbers', 'wc-serial-numbers' ), $current === 'failed' ? ' class="current"' : '', __( 'Failed', 'wc-serial-numbers' ) . $failed_count ),
			'inactive'  => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'inactive', $this->base_url ), __( 'Inactive serial numbers', 'wc-serial-numbers' ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wc-serial-numbers' ) . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate'   => __( 'Activate', 'wc-serial-numbers' ),
			'deactivate' => __( 'Deactivate', 'wc-serial-numbers' ),
			'delete'     => __( 'Delete', 'wc-serial-numbers' ),
			'export'     => __( 'Export', 'wc-serial-numbers' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'key'         => __( 'Key', 'wc-serial-numbers' ),
			'product'     => __( 'Product', 'wc-serial-numbers' ),
			'order'       => __( 'Order', 'wc-serial-numbers' ),
			'customer'    => __( 'Customer', 'wc-serial-numbers' ),
			'expire_date' => __( 'Expire Date', 'wc-serial-numbers' ),
			'order_date'  => __( 'Order Date', 'wc-serial-numbers' ),
			'status'      => __( 'Status', 'wc-serial-numbers' ),
		);

		if ( wc_serial_numbers()->is_software_support_enabled() ) {
			$columns['activation'] = __( 'Activation', 'wc-serial-numbers' );
			$columns['validity']   = __( 'Validity', 'wc-serial-numbers' );
		}

		return apply_filters( 'wc_serial_numbers_serials_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'key'         => array( 'serial_key', false ),
			'product'     => array( 'product_id', false ),
			'order'       => array( 'order_id', false ),
			'activation'  => array( 'activation_limit', false ),
			'expire_date' => array( 'expire_date', false ),
			'validity'    => array( 'validity', false ),
			'status'      => array( 'status', false ),
			'order_date'  => array( 'order_date', false ),
		);

		return apply_filters( 'wc_serial_numbers_serials_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string Name of the primary column.
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function get_primary_column_name() {
		return 'key';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->id}' value='{$item->id}' />";
	}

	/**
	 * Get serial Key
	 *
	 * since 1.0.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_key( $item ) {
		$row_actions         = array();
		$base_url            = add_query_arg( array( 'id' => $item->id ), admin_url( 'admin.php?page=wc-serial-numbers' ) );
		$edit_url            = wp_nonce_url( add_query_arg( [ 'action' => 'edit' ], $base_url ), 'serial_number_nonce' );
		$delete_url          = wp_nonce_url( add_query_arg( [ 'action' => 'delete' ], $base_url ), 'serial_number_nonce' );
		$row_actions['id']   = sprintf( __( 'ID: %d', 'wp-serial-numbers' ), $item->id );
		$row_actions['show'] = sprintf( '<a data-serial-id="%d" data-nonce="%s" class="wcsn-show-serial-key"   href="#">%s</a>', $item->id, wp_create_nonce( 'wc_serial_numbers_decrypt_key' ), __( 'Show', 'wc-serial-numbers' ) );
		$row_actions['edit'] = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wp-serial-numbers' ) );

		$row_actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wp-serial-numbers' ) );

		$row_actions = apply_filters( 'wc_serial_numbers_serials_table_row_actions', $row_actions, $item );

		$spinner            = sprintf( '<img class="wcsn-spinner" style="display: none;" src="%s"/>', admin_url( 'images/loading.gif' ) );
		$class              = 'encrypted';
		$serial_key         = '';
		$hide_serial_number = ( 'on' == wc_serial_numbers()->get_settings( 'hide_serial_number', '', 'wcsn_general_settings' ) );

		if ( ! $hide_serial_number ) {
			$class      = 'decrypted';
			$serial_key = WC_Serial_Numbers_Encryption::decrypt( $item->serial_key );
			unset( $row_actions['show'] );
		}

		return sprintf( '<code class="wcsn-serial-key %1$s">%2$s</code> %3$s%4$s', $class, $serial_key, $spinner, $this->row_actions( $row_actions ) );
	}


	/**
	 * Get product name
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.1.6
	 */
	function column_product( $item ) {
		$product     = wc_get_product( $item->product_id );
		$post_parent = wp_get_post_parent_id( $item->product_id );
		$post_id     = $post_parent ? $post_parent : $item->product_id;

		return empty( $item->product_id ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );
	}

	/**
	 * Get order.
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.1.6
	 */
	function column_order( $item ) {
		$line = ! empty( $item->order_id ) ? '#' . $item->order_id : '&mdash;';

		return ! empty( $item->product_id ) ? '<a href="' . get_edit_post_link( $item->order_id ) . '" target="_blank">' . $line . '</a>' : $line;
	}

	/**
	 * Get customer name.
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.1.6
	 */
	function column_customer( $item ) {
		if ( empty( $item->order_id ) ) {
			return '&mdash;';
		}
		$order    = wc_get_order( $item->order_id );
		$customer = get_userdata( $order->get_customer_id() );

		return empty( $customer->ID ) ? '&mdash;' : sprintf(
			'<a href="%s">%s (#%d - %s)</a>',
			get_edit_user_link( $customer->ID ),
			$customer->display_name,
			$customer->ID,
			$customer->user_email
		);
	}

	/**
	 * Get activation.
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.1.6
	 */
	function column_activation( $item ) {
		global $wpdb;
		$limit = ! empty( $item->activation_limit ) ? $item->activation_limit : __( '∞', 'wc-serial-numbers' );
		$count = intval( $item->activation_count );

		$link = add_query_arg( [
			'key_id' => $item->id,
			'page'   => 'wc-serial-numbers-activations'
		], admin_url( 'admin.php' ) );

		$activated = sprintf( '<a href="%s">%s</a>', $link, $count );

		return sprintf( '<b>%s</b> / <b>%s</b>', $activated, $limit );
	}

	/**
	 * Get validity.
	 *
	 * @param $item
	 *
	 * @return string|void
	 * @since 1.1.6
	 */
	function column_validity( $item ) {
		return ! empty( $item->validity ) ? sprintf( _n( '<b>%s</b> Day <br><small>After purchase</small>', '<b>%s</b> Days <br><small>After purchase</small>', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Never expire', 'wc-serial-numbers' );
	}

	/**
	 * Get status
	 *
	 * @param $item
	 *
	 * @return mixed|string
	 * @since 1.1.6
	 */
	function column_status( $item ) {
		return sprintf( "<span class='wcsn-key-status %s'>%s</span>", sanitize_html_class( $item->status ), wc_serial_numbers_get_item_status( $item, 'view' ) );
	}

	/**
	 * Expire date.
	 *
	 * @param $item
	 *
	 * @return false|string
	 * @since 1.1.6
	 */
	function column_expire_date( $item ) {
		return ! empty( $item->expire_date ) && ( '0000-00-00 00:00:00' != $item->expire_date ) ? date( get_option( 'date_format' ), strtotime( $item->expire_date ) ) : '&mdash;';
	}

	/**
	 * Order date.
	 *
	 * @param $item
	 *
	 * @return false|string
	 * @since 1.1.6
	 */
	function column_order_date( $item ) {
		return ! empty( $item->order_date ) && ( '0000-00-00 00:00:00' != $item->order_date ) ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';
	}


	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string|void
	 */
	function column_default( $item, $column_name ) {
		$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';

		return apply_filters( 'wc_serial_numbers_serials_table_column_content', $column, $item, $column_name );
	}


	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return object $get_results Array of all the data for the discount codes
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page   = $this->get_items_per_page( 'serials_per_page', $this->per_page );
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$id         = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'    => $orderby,
			'order'      => $order,
			'status'     => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'include'    => $id,
			'search'     => $search
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count     = wc_serial_numbers_get_items( $args, true );
		$this->available_count = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'available' ) ), true );
		$this->sold_count      = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'sold' ) ), true );
		$this->failed_count    = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'failed' ) ), true );
		$this->refunded_count  = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'refunded' ) ), true );
		$this->cancelled_count = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'cancelled' ) ), true );
		$this->failed_count    = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'failed' ) ), true );
		$this->expired_count   = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'expired' ) ), true );
		$this->inactive_count  = wc_serial_numbers_get_items( array_merge( $args, array( 'status' => 'inactive' ) ), true );

		$results = wc_serial_numbers_get_items( $args );

		return $results;
	}

}
