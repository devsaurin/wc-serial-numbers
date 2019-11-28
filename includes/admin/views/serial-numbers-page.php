<?php
defined( 'ABSPATH' ) || exit();
?>
<div class="wrap ea-wrapper">
	<?php
	if ( isset( $_GET['wcsn-action'] ) && $_GET['wcsn-action'] == 'add_serial_number' ) {
		wcsn_get_views( 'edit-revenue.php' );
	} elseif ( isset( $_GET['wcsn-action'] ) && $_GET['wcsn-action'] == 'edit_serial_number' ) {
		wcsn_get_views( 'edit-revenue.php' );
	} else {
		require_once WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/tables/class-serial-numbers-list-table.php';
		$list_table = new WC_Serial_Numbers_List_Table();
		$list_table->prepare_items();
		$base_url = admin_url( 'admin.php?page=wc-serial-numbers' );
		?>

		<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>
		<a href="<?php echo esc_url( add_query_arg( array( 'wcsn-action' => 'add_serial_number' ), $base_url ) ); ?>"
		   class="page-title-action">
			<?php _e( 'Add New', 'wp-ever-accounting' ); ?>
		</a>
		<?php do_action( 'edit_serial_number_page_top' ); ?>
		<form method="get" action="<?php echo esc_url( $base_url ); ?>">
			<div class="ea-list-table">
				<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'serial-number' ); ?>
				<input type="hidden" name="page" value="wc-serial-numbers"/>
				<?php $list_table->views() ?>
				<?php $list_table->display() ?>
			</div>
		</form>
		<?php
		do_action( 'edit_serial_number_page_bottom' );
	}
	?>
</div>
