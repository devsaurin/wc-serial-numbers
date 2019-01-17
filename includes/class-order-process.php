<?php

namespace Pluginever\WCSerialNumbers;


class Order_Process {


	function __construct() {

		// Check if Customer can checkout even there is no serial number
		$is_allowed = wsn_get_settings( 'wsn_allow_checkout', '', 'wsn_general_settings' );

		if ( $is_allowed != 'on' ) {
			add_action( 'woocommerce_check_cart_items', [ $this, 'validate_cart_content' ] );
		}

		add_action( 'woocommerce_checkout_order_processed', [ $this, 'order_process' ] );

		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'order_serial_number_details' ] );

	}


	/**
	 * Reserve or generate a serial number for the product during place order process.
	 *
	 * @param $order
	 * @param $data
	 */

	function order_process( $order_id ) {

		$order = wc_get_order( $order_id );

		$items = $order->get_items();

		$serial_numbers_ids = [];

		foreach ( $items as $item_id => $item_data ) {

			$product    = $item_data->get_product();
			$product_id = $product->get_id();
			$quantity   = $item_data->get_quantity();

			$enable_serial_number = get_post_meta( $product_id, 'enable_serial_number', true );

			if ( $enable_serial_number == 'enable' ) {

				$numbers = wsn_get_available_numbers( $product_id );
				error_log(print_r($numbers, true ));

				$number = $numbers[ array_rand( $numbers ) ]; //serial_number_to_be_used
				error_log(print_r($number, true ));
				$used = get_post_meta( $number, 'used', true );

				update_post_meta( $number, 'order', $order->get_id() );

				update_post_meta( $number, 'used', ( $used + $quantity ) );

				$serial_numbers_ids[ $product_id ] = $number;

				do_action( 'wsn_update_notification_on_order_delete', $product_id );

			}

		}

		//Update Order meta data
		update_post_meta( $order_id, 'serial_numbers', $serial_numbers_ids );
	}

	/**
	 * Show th Serial number details in the order details page
	 *
	 * @param $order
	 */

	function order_serial_number_details( $order ) {

		$serial_numbers = get_post_meta( $order->get_id(), 'serial_numbers', true );

		if ( empty( $serial_numbers ) or ! wsn_check_status_to_send( $order ) ) {
			return;
		}

		include WPWSN_TEMPLATES_DIR . '/order-details-serial-number.php';

	}


	function validate_cart_content() {

		$car_products = WC()->cart->get_cart_contents();

		foreach ( $car_products as $id => $cart_product ) {

			$product    = $cart_product['data'];
			$product_id = $cart_product['product_id'];
			$quantity   = $cart_product['quantity'];

			$is_enabled = get_post_meta( $product_id, 'enable_serial_number', true ); //Check if the serial number enabled for this product.

			if ( $is_enabled == 'enable' ) {

				$total_number = 0;

				$numbers = wsn_get_available_numbers( $product_id );

				foreach ($numbers as $number){

					$deliver_times = get_post_meta($number, 'deliver_times', true);
					$used          = get_post_meta($number, 'used', true);

					$total_number += ($deliver_times -$used);

				}

				if ( $total_number < $quantity ) {
					wc_add_notice( __( 'Sorry, There is not enough <strong>Serial Number</strong> available for', 'wc-serial-numbers' ) . ' <strong>' . $product->get_title() . '</strong>, <br>' . __( 'Please remove this item or lower the quantity, For now we have', 'wc-serial-numbers' ) . ' ' . $count_numbers . ' ' . __( 'Serial Number(s)', 'wc-serial-numbers' ) . ' ' . __( 'for this product.', 'wc-serial-numbers' ) . '' . '<br>', 'error' );
				}
			}

		}
	}
}
