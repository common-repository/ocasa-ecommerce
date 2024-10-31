<?php
/**
 * Class OcasaWoo Sdk
 *
 * @package  Conexa\OcasaWoo\Sdk;
 */

namespace Conexa\OcasaWoo\Sdk;

use Conexa\Woo\Helper\Helper;
use Conexa\OcasaWoo\Api\OcasaWooApi;
use Conexa\OcasaWoo\ShippingMethod\WC_OcasaWoo;

/**
 * OcasaWoo SDK Main Class
 */
class OcasaWooSdk {

	/**
	 * Constructor Method
	 */
	public function __construct() {
		$this->api         = new OcasaWooApi();
		$this->token       = get_option( 'ocasawoo_token' );
		$this->userOnboard = false;
	}

	/**
	 * Adds register_or_login
	 *
	 * @param int $userId comment about this variable.
	 * @return Array
	 */
	public function register_or_login( $userId ) {
		$endpoint = '/api/v1/gateway/oauth';

		$body              = array();
		$body['userId']    = $userId;
		$body['storeName'] = get_bloginfo( 'name' );

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		try {
			$response = $this->api->post(
				$endpoint,
				$body,
				$headers
			);
		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return false;
		}
		$response = $this->handle_response( $response, __FUNCTION__ );
		if($response){
			update_option( 'ocasawoo_token', $response['token'] );
			return array(
				'token'       => $response['token'],
				'userOnboard' => $response['userOnboard'],
			);
		}
		return false;
	}

	/**
	 * Adds quote_list
	 *
	 * @param array $items comment about this variable.
	 * @param int   $zipFrom comment about this variable.
	 * @param int   $zipTo comment about this variable.
	 * @return Array
	 */
	public function quote_list( $items, $zipFrom, $zipTo ) {
		$endpoint      = '/api/v1/gateway/quotation?userId=' . $this->token;
		$grouped_items = Helper::group_items( $items );
		$total_weight  = 0;
		$total_height  = 0;
		$total_width   = 0;
		$total_length  = 0;
		$total_volume  = 0;

		foreach ( $grouped_items as $item ) {
			$total_weight = $total_weight + floatval( $item['weight'] ) * floatval( $item['quantity'] );
			$total_height = $total_height + floatval( $item['height'] ) * floatval( $item['quantity'] );
			$total_width  = $total_width + floatval( $item['width'] ) * floatval( $item['quantity'] );
			$total_length = $total_length + floatval( $item['length'] ) * floatval( $item['quantity'] );
			$total_volume = $total_volume + ( $total_length * $total_height * $total_width );
		}
		if ( $total_weight > 25 ) {
			return;
		}
		if (( $total_height > 170 ) || ( $total_width > 170) || ( $total_length > 170)) {
			return;
		}
		$body                          = array();
		$body['postalCodeDestination'] = $zipTo;
		$body['weight']                = $total_weight;
		$body['volume']                = $total_volume;

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		try {
			$response = $this->api->post(
				$endpoint,
				$body,
				$headers
			);
			$response = $this->handle_response( $response, __FUNCTION__ );
			return $response['data'];

		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return false;
		}

	}

	/**
	 * Adds post_order
	 *
	 * @param \WC_Order $order comment about this variable.
	 * @return Array
	 */
	public function post_order( \WC_Order $order ) {
		$endpoint = '/api/v1/gateway/order?userId=' . $this->token;
		$items    = Helper::get_items_from_order( $order );

		Helper::log( $order );

		$order->get_shipping_methods();
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return;
		}

		$shipping_method = array_shift( $shipping_methods );
		if ( 'ocasawoo' === $shipping_method->get_method_id() ) {
			$shipping_method_pickit = new WC_OcasaWoo( $shipping_method->get_instance_id() );
			$serviceCode            = $shipping_method->get_meta( 'serviceCode' );
			$serviceName            = $shipping_method->get_meta( 'serviceName' );
			$deliveryTime           = $shipping_method->get_meta( 'deliveryTime' );
			$rate                   = $shipping_method->get_meta( 'rate' );
		} else {
			return;
		}

		$shipping_states  = WC()->countries->get_states( $order->get_shipping_country() );
		$shipping_state   = ! empty( $shipping_states[ $order->get_shipping_state() ] ) ? $shipping_states[ $order->get_shipping_state() ] : '';
		$shipping_country = ! empty( WC()->countries->countries[ $order->get_shipping_country() ] ) ? WC()->countries->countries[ $order->get_shipping_country() ] : '';
		$customer_note    = ! empty( $order->get_customer_note() ) ? ' - ' . $order->get_customer_note() : '';

		$body            = array();
		$body['orderId'] = $order->get_ID();

		$grouped_items = Helper::group_items( $items );
		foreach ( $grouped_items as $item ) {
			$body['items'][] = array(
				'quantity' => floatval( $item['quantity'] ),
				'name'     => $item['description'],
				'price'    => floatval( $item['price'] ),
				'height'   => floatval( $item['height'] ),
				'weight'   => floatval( $item['weight'] ),
				'width'    => floatval( $item['width'] ),
				'depth'    => floatval( $item['length'] ),
			);
		}

		$body['orderNumber'] = $order->get_ID();
		$body['typeService'] = $serviceName;
		$body['serviceCode'] = $serviceCode;

		$body['recipient'] = array(
			'firstName' => $order->get_billing_first_name(),
			'lastName'  => $order->get_billing_last_name(),
			'cuil'      => '',
		);

		$body['shippingAddress'] = array(
			'street'     => $order->get_shipping_address_1(),
			'number'     => '',
			'floor'      => '',
			'postalCode' => $order->get_shipping_postcode(),
			'locality'   => '',
			'city'       => $order->get_shipping_city(),
			'province'   => $shipping_state,
			'country'    => $shipping_country,
		);

		$body['totalSpent']    = $order->get_total();
		$body['subTotal']      = $order->get_subtotal();
		$body['shipingCost']   = $order->get_shipping_total();
		$body['paymentMethod'] = $order->get_payment_method_title();
		$body['notes']         = $order->get_shipping_address_2() . $customer_note;

		$body['webhookURL'] = get_site_url( null, '/wc-api/wc-ocasawoo-order-status' );

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		try {
			$response = $this->api->post(
				$endpoint,
				$body,
				$headers
			);
			$response = $this->handle_response( $response, __FUNCTION__ );
			return true;

		} catch ( \Exception $e ) {
			Helper::log_error( __FUNCTION__ . ': ' . $e->getMessage() );
			return false;
		}

	}

	/**
	 * Adds handle_response
	 *
	 * @param string $response comment about this variable.
	 * @param string $function_name comment about this variable.
	 * @return Array
	 */
	protected function handle_response( $response, string $function_name ) {
		if ( ! isset( $response['success'] ) ) {
			return false;
		}

		if ( false === $response['success'] ) {
			return false;
		}

		if ( 'post_order' === $function_name ) {
			return $response['success'];
		}
		return $response;
	}

}
