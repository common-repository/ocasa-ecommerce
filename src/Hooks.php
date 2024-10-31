<?php
/**
 * Hooks Files
 *
 * @package  Conexa\OcasaWoo\Hooks
 */

defined( 'ABSPATH' ) || exit;

// --- Settings
add_filter(
	'plugin_action_links_' . plugin_basename( \OcasaWoo::MAIN_FILE ),
	array(
		'OcasaWoo',
		'create_settings_link',
	)
);

// --- Add OcasaWoo Shipment Method
add_filter( 'woocommerce_shipping_methods', array( 'OcasaWoo', 'add_shipping_method' ) );

// --- Add OcasaWoo Panel
add_action( 'admin_menu', array( '\Conexa\OcasaWoo\Panel\Main', 'create_menu_option' ) );

// --- Add OcasaWoo Post Order
add_action( 'woocommerce_order_status_changed', array( '\Conexa\OcasaWoo\Orders\ProcessNewOrder', 'handle_order_status' ), 10, 4 );


// --- Order Actions
add_action( 'woocommerce_order_actions', array( '\Conexa\OcasaWoo\Orders\Actions', 'add_order_action' ) );
add_action( 'woocommerce_order_action_wc_ocasa_send_to_panel', array( '\Conexa\OcasaWoo\Orders\Actions', 'handle_send_to_panel' ) );


// --- Webhook
add_action( 'woocommerce_api_wc-ocasawoo-order-status', array( '\Conexa\OcasaWoo\Orders\Webhooks', 'listener' ) );
