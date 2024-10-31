<?php
/**
 * Plugin Name: Ocasa para WooCommerce
 * Description: Método de Envío de WooCommerce para Ocasa.
 * Version: 1.0.3
 * Requires PHP: 7.0
 * Author: Conexa
 * Author URI: https://conexa.ai
 * Text Domain: ocasawootextdomain
 * Domain Path: /i18n/languages
 * WC requires at least: 5.4.1
 * WC tested up to: 5.4
 *
 * @package Ecomerciar\Ocasa\Ocasa
 */

defined( 'ABSPATH' ) || exit;

use Conexa\Woo\Helper\Helper;

add_action( 'plugins_loaded', array( 'OcasaWoo', 'init' ) );
add_action( 'activated_plugin', array( 'OcasaWoo', 'activation' ) );
add_action( 'deactivated_plugin', array( 'OcasaWoo', 'deactivation' ) );

if ( ! class_exists( 'OcasaWoo' ) ) {
	/**
	 * Plugin's base Class
	 */
	class OcasaWoo {

		const VERSION     = '1.0.3';
		const PLUGIN_NAME = 'ocasawoo';
		const MAIN_FILE   = __FILE__;
		const MAIN_DIR    = __DIR__;

		/**
		 * Inits our plugin
		 */
		public static function init() {
			if ( ! self::check_system() ) {
				return false;
			}

			spl_autoload_register(
				function ( $class ) {
					// Plugin base Namespace.

					if ( strpos( $class, 'Woo' ) === false ||
						( strpos( $class, 'Ecomerciar' ) === false && strpos( $class, 'Conexa' ) === false ) ) {
						return;
					}
					$boiler    = 'includes';
					$class     = str_replace( '\\', '/', $class );
					$parts     = explode( '/', $class );
					$classname = array_pop( $parts );

					$filename = $classname;
					$filename = str_replace( 'WooCommerce', 'Woocommerce', $filename );
					$filename = str_replace( 'WC_', 'Wc', $filename );
					$filename = str_replace( 'WC', 'Wc', $filename );
					$filename = preg_replace( '/([A-Z])/', '-$1', $filename );
					$filename = 'class' . $filename;
					$filename = strtolower( $filename );
					$folder   = strtolower( array_pop( $parts ) );

					if ( 'class-ocasa-woo' === $filename ) {
						return;
					}
					if ( ( 'helper' !== $folder ) && ( 'apiwoo' !== $folder ) ) {
						$boiler = 'src';
					}

					require_once plugin_dir_path( __FILE__ ) . $boiler . '/' . $folder . '/' . $filename . '.php';
				}
			);

			include_once __DIR__ . '/src/Hooks.php';
			self::load_textdomain();
			Helper::init();
			return true;
		}

		/**
		 * Checks system requirements
		 *
		 * @return bool
		 */
		public static function check_system() {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$system = self::check_components();

			if ( $system['flag'] ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				echo '<div class="notice notice-error is-dismissible">'
				. '<p>' .
					sprintf(
						/* translators: %s: System Flag */
						esc_html__(
							'<strong>%1$s</strong> Requiere al menos %2$s versión %3$s o superior.',
							'ocasa'
						),
						esc_html( self::PLUGIN_NAME ),
						esc_html( $system['flag'] ),
						esc_html( $system['version'] )
					) .
					'</p>'
				. '</div>';
				return false;
			}

			if ( ! class_exists( 'WooCommerce' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				echo '<div class="notice notice-error is-dismissible">'
				. '<p>' .
					sprintf(
						/* translators: %s: System Flag */
						esc_html__(
							'WooCommerce debe estar activo antes de usar <strong>%s</strong>',
							'ocasa'
						),
						esc_html( self::PLUGIN_NAME )
					) .
					'</p>'
				. '</div>';
				return false;
			}
			return true;
		}

		/**
		 * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
		 *
		 * @return array
		 */
		private static function check_components() {
			global $wp_version;
			$flag    = false;
			$version = false;

			if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
				$flag    = 'PHP';
				$version = '7.0';
			} elseif ( version_compare( $wp_version, '5.4', '<' ) ) {
				$flag    = 'WordPress';
				$version = '5.4';
			} elseif (
			! defined( 'WC_VERSION' ) ||
			version_compare( WC_VERSION, '3.8.0', '<' )
			) {
				$flag    = 'WooCommerce';
				$version = '3.8.0';
			}

			return array(
				'flag'    => $flag,
				'version' => $version,
			);
		}

		/**
		 * Loads the plugin text domain
		 *
		 * @return void
		 */
		public static function load_textdomain() {
			load_plugin_textdomain( 'ocasawootextdomain', false, basename( dirname( __FILE__ ) ) . '/i18n/languages' );
		}


		/**
		 * Create a link to the settings page
		 *
		 * @param array $links Links for plugin.
		 * @return array
		 */
		public static function create_settings_link( array $links ) {
			$link =
				'<a href="' .
				esc_url(
					get_admin_url(
						null,
						'admin.php?page=wc-ocasawoo-panel'
					)
				) .
				'">' .
				__( 'Ajustes', 'ocasawootextdomain' ) .
				'</a>';
			array_unshift( $links, $link );
			return $links;
		}

		/**
		 * Activation Plugin Actions
		 *
		 * @param string $plugin Plugin Name.
		 * @return bool
		 */
		public static function activation( $plugin ) {
			if ( plugin_basename( self::MAIN_FILE ) === $plugin ) {
				wp_safe_redirect(
					admin_url(
						esc_url(
							'admin.php?page=wc-ocasawoo-panel'
						)
					)
				);
				exit();
			}
			return false;
		}

		/**
		 * Deactivation Plugin Actions
		 *
		 * @param int $plugin comment about this variable.
		 * @return void
		 */
		public static function deactivation( $plugin ) {
			delete_option( 'ocasawoo_user' );
			delete_option( 'ocasawoo_token' );
		}

		/**
		 * Adds our shipping method to WooCommerce
		 *
		 * @param array $shipping_methods comment about this variable.
		 * @return array
		 */
		public static function add_shipping_method( $shipping_methods ) {
			$shipping_methods['ocasawoo'] = '\Conexa\OcasaWoo\ShippingMethod\WC_OcasaWoo';
			return $shipping_methods;
		}

	}

	// --- HPOS WooCommerce Compatibility
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );


	/**
	 * Custom function to declare compatibility with cart_checkout_blocks feature 
	*/
	function ocasa_declare_cart_checkout_blocks_compatibility() {
		// Check if the required class exists
		if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
			// Declare compatibility for 'cart_checkout_blocks'
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
		}
	}
	// Hook the custom function to the 'before_woocommerce_init' action
	add_action('before_woocommerce_init', 'ocasa_declare_cart_checkout_blocks_compatibility');
}
