<?php
/**
 * Class OcasaWoo Orders
 *
 * @package  Conexa\OcasaWoo\Orders;
 */

namespace Conexa\OcasaWoo\Orders;

defined( 'ABSPATH' ) || exit;


use Conexa\Woo\Helper\Helper;
use Conexa\OcasaWoo\Api\OcasaWooApi;
use Conexa\OcasaWoo\SDK\OcasaWooSdk;
use Conexa\OcasaWoo\ShippingMethod\WC_OcasaWoo;


/**
 * Main Plugin Process Class
 */
class ProcessNewOrder {

	/**
	 * Comment about handle_order_status.
	 *
	 * @param int       $order_id comment about this variable.
	 * @param string    $status_from comment about this variable.
	 * @param string    $status_to comment about this variable.
	 * @param \WC_Order $order comment about this variable.
	 */
	public static function handle_order_status( int $order_id, string $status_from, string $status_to, \WC_Order $order ) {
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

		if ( 'processing' !== $order->get_status() ) {
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


}
