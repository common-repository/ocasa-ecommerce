<?php
/**
 * Class OcasaWoo Webhook
 *
 * @package  Conexa\OcasaWoo\Webhook;
 */

namespace Conexa\OcasaWoo\Orders;

use Conexa\Woo\Helper\Helper;

defined( 'ABSPATH' ) || exit();

/**
 * WebHook's base Class
 */
class Webhooks {

	const OK    = 'HTTP/1.1 200 OK';
	const ERROR = 'HTTP/1.1 500 ERROR';

	/**
	 * Receives the webhook and check if it's valid to proceed
	 *
	 * @param string $data Webhook json Data for testing purpouses.
	 * @param string $data_header Webhook json Data for testing purpouses.
	 *
	 * @return bool
	 */
	public static function listener( string $data = null, string $data_header = null ) {

		// Takes raw data from the request.
		if ( is_null( $data ) || empty( $data ) ) {
			$json = file_get_contents( 'php://input' );
		} else {
			$json = $data;
		}

		Helper::log_info( 'Webhook recibido' );
		Helper::log(
			__FUNCTION__ .
				__( '- Webhook recibido de OcasaWoo:', 'ocasawootextdomain' ) .
				$json
		);

		$process = self::process_webhook( $json );

		if ( is_null( $json ) || empty( $json ) ) {
			// Real Webhook.
			if ( $process ) {
				header( self::OK );
			} else {
				header( self::ERROR );
				wp_die(
					__( 'WooCommerce OcasaWoo Webhook no válido.', 'ocasawootextdomain' ),
					'OcasaWoo Webhook',
					array( 'response' => 500 )
				);
			}
		} else {
			// For testing purpouse.
			return $process;
		}
	}


	/**
	 * Process Webhook
	 *
	 * @param json $json Webhook data for.
	 *
	 * @return bool
	 */
	public static function process_webhook( $json ) {

		// Converts it into a PHP object.
		$data = json_decode( $json, true );

		if ( empty( $data ) || ! self::validate_input( $data ) ) {
			return false;
		}
		return self::handle_webhook( $data );
	}


	/**
	 * Validates the incoming webhook
	 *
	 * @param array $data Webhook data to be validated.
	 */
	private static function validate_input( array $data = array() ) {

	}

	/**
	 * Handles and processes the webhook
	 *
	 * @param array $data webhook data to be processed.
	 */
	private static function handle_webhook( array $data = array() ) {

	}
}
