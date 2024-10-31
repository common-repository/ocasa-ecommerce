<?php
/**
 * Class OcasaWoo Orders
 *
 * @package  Conexa\OcasaWoo\Orders;
 */

namespace  Conexa\OcasaWoo\Orders;

defined( 'ABSPATH' ) || exit;

use Conexa\Woo\Helper\Helper;
use Conexa\OcasaWoo\SDK\OcasaWooSdk;
use Conexa\OcasaWoo\ShippingMethod\WC_OcasaWoo;
use \WC_Order;


/**
 * Main Plugin Process Class
 */
class Actions {
	/**
	 * Comment about handle_order_status.
	 *
	 * @param array $order comment about this variable.
	 */
	public static function handle_send_to_panel( $order ) {
		if ( empty( $order ) ) {
			return;
		}

		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return;
		}

		$shipping_method = array_shift( $shipping_methods );
		if ( 'ocasawoo' !== $shipping_method->get_method_id() ) {
			return;
		}

		Helper::log( 'Create Order' );
		$sdk = new OcasaWooSdk();
		if ( $sdk->post_order( $order ) ) {
			$order->add_order_note( esc_html( 'El pedido fue enviado al panel de Ocasa.', 'ocasawootextdomain' ) );
			return true;
		} else {
			$order->add_order_note( esc_html( 'No fue posible enviar el pedido al panel de Ocasa. Consulte el Log.', 'ocasawootextdomain' ) );
			return false;
		}
	}

	/**
	 * Adds add_order_action
	 *
	 * @param array $actions comment about this variable.
	 * @return Array
	 */
	public static function add_order_action( $actions ) {
		global $theorder;

		$shipping_methods = $theorder->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return $actions;
		}
		$shipping_method = array_shift( $shipping_methods );

		if ( 'ocasawoo' === $shipping_method->get_method_id() ) {
			$actions['wc_ocasa_send_to_panel'] = __( 'Ocasa - Enviar a Panel', 'ocasawootextdomain' );
		}
		return $actions;

	}

}
