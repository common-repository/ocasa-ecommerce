<?php
/**
 * Class OcasaWoo Shipping Class
 *
 * @package  Conexa\OcasaWoo\ShippingMethod;
 */

namespace Conexa\OcasaWoo\ShippingMethod;

use Conexa\Woo\Helper\Helper;
use Conexa\OcasaWoo\Api\OcasaWooApi;
use Conexa\OcasaWoo\SDK\OcasaWooSdk;
use WC_Shipping_Method;

defined( 'ABSPATH' ) || class_exists( '\WC_Shipping_Method' ) || exit;

if ( ! class_exists( '\Conexa\OcasaWoo\ShippingMethod\WC_OcasaWoo' ) ) {

	/**
	 * OcasaWoo WC_OcasaWoo
	 */
	class WC_OcasaWoo extends \WC_Shipping_Method {
		/**
		 * Default constructor
		 *
		 * @param int $instance_id Shipping Method Instance from Order.
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'ocasawoo';
			$this->instance_id        = absint( $instance_id );
			$this->title              = $this->get_option( 'title' );
			$this->method_title       = __( 'Ocasa', 'ocasawootextdomain' );
			$this->method_description = __( 'Método de envío de Ocasa para WooCommerce.', 'ocasawootextdomain' );
			$this->supports           = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);
			$this->init();
		}

		/**
		 * Init user set variables.
		 *
		 * @return void
		 */
		public function init() {
			$this->instance_form_fields = include 'settings.php';
			$this->title                = $this->get_option( 'title' );

			// Load the settings API.
			$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings.
			$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

			// Save settings in admin if you have any defined.
			add_action(
				'woocommerce_update_options_shipping_' . $this->id,
				array(
					$this,
					'process_admin_options',
				)
			);
		}

		/**
		 * Calculate the shipping costs.
		 *
		 * @param array $package Package of items from cart.
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			$rateDefaults = array(
				'label'    => $this->get_option( 'title' ), // Label for the rate.
				'cost'     => '', // Amount for shipping or an array of costs (for per item shipping).
				'taxes'    => '', // Pass an array of taxes, or pass nothing to have it calculated for you, or pass 'false' to calculate no tax for this method.
				'calc_tax' => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs passed via 'cost'.
				'package'  => $package,
				'term'     => '',
			);

			Helper::log( 'Quotation' );

			$items = Helper::get_items_from_cart( WC()->cart );
			if ( false === $items ) {
				Helper::log( 'No items' );

				return;
			}

			$zipTo = $package['destination']['postcode'];
			Helper::log( $package['destination'] );
			if ( empty( $zipTo ) ) {
				Helper::log( 'No Zipcode' );
				return;
			}

			$zipFrom = get_option( 'woocommerce_store_postcode' );
			$sdk     = new OcasaWooSdk();
			$res     = $sdk->quote_list( $items, $zipFrom, $zipTo );

			if ( $res ) {
				foreach ( $res as $quote ) {

					$promiseString = __( ' - Envío a domicilio.', 'ocasawootextdomain' );

					$rate          = $rateDefaults;
					$rate['id']    = $this->get_rate_id() . '_' . $quote['serviceCode'];
					$rate['label'] = $quote['serviceName'] . $promiseString;
					$rate['cost']  = floatval( $quote['rate'] );

					$rate['meta_data'] = array(
						'serviceCode'  => $quote['serviceCode'],
						'serviceName'  => $quote['serviceName'],
						'deliveryTime' => $quote['deliveryTime'],
					);

					$this->add_rate( $rate );
				}
				return true;
			} else {
				return false;
			}
		}

	}
}
