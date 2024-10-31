<?php
/**
 * Class OcasaWoo Panel
 *
 * @package  Conexa\OcasaWoo\Panel;
 */

namespace Conexa\OcasaWoo\Panel;

defined( 'ABSPATH' ) || exit;

use Conexa\Woo\Helper\Helper;
use Conexa\OcasaWoo\SDK\OcasaWooSdk;


/**
 * Main Onboarding Class
 */
class Main {

	/**
	 * Register Onboarding Page
	 */
	public static function create_menu_option() {
		add_submenu_page(
			'woocommerce',
			__( 'Ocasa', 'ocasawootextdomain' ),
			__( 'Ocasa', 'ocasawootextdomain' ),
			'manage_woocommerce',
			'wc-ocasawoo-panel',
			array( __CLASS__, 'page_content' )
		);

		return true;
	}

	/**
	 * Get content
	 */
	public static function page_content() {


		if ( ! is_admin() && ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			die( __( 'what are you doing here?', 'ocasawootextdomain' ) );
		}
				
		$userId = get_option( 'ocasawoo_user' );

		if ( empty( $userId ) || is_null( $userId ) ) {
			$userId = get_site_url();
			$userId = preg_replace( '/[^A-Za-z0-9 ]/', '', $userId );

			update_option( 'ocasawoo_user', $userId );
		}

		$sdk = new OcasaWooSdk();
		$res = $sdk->register_or_login( $userId );

		if ( $res != false) {
			$data           = array();
			$data['userId'] = $userId;
			$data['token']  = $res['token'];
            $data['url']    = ( $res['userOnboard'] ) ? 'https://ocasa-woocommerce.conexa.ai/woocommerce/panel/orders?userId=' . $data['token'] : 'https://ocasa-woocommerce.conexa.ai/woocommerce/onboarding?userId=' . $data['token'];
			//$data['url']    = ( $res['userOnboard'] ) ? 'https://ocasa-woocommerce-stage.conexa.ai/woocommerce/panel/orders?userId=' . $data['token'] : 'https://ocasa-woocommerce-stage.conexa.ai/woocommerce/onboarding?userId=' . $data['token'];
			helper::get_template_part( 'panel', 'content', $data );
			return true;
		}

	}
}
