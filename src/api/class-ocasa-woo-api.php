<?php
/**
 * Class OcasaWoo Api Class
 *
 * @package  Conexa\OcasaWoo\Api;
 */

namespace Conexa\OcasaWoo\Api;

use Conexa\Woo\Helper\Helper;
defined( 'ABSPATH' ) || exit();
/**
 * Clip API Class
 */
class OcasaWooApi extends \Conexa\Woo\ApiWoo\ApiConnector implements \Conexa\Woo\ApiWoo\ApiInterface {

	const API_BASE_URL = 'https://ocasa-woocommerce-api.conexa.ai';
	//const API_BASE_URL = 'https://ocasa-woocommerce-api-stage.conexa.ai';

	/**
	 *  Get Base Url
	 *
	 * @return String
	 */
	public function get_base_url() {
		return self::API_BASE_URL;
	}

}
