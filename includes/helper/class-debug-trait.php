<?php
/**
 * Class Woo Helper
 *
 * @package  Conexa\Woo\Helper;
 */

namespace Conexa\Woo\Helper;

/**
 * Database Trait
 */
trait DebugTrait {

	/**
	 * DebugTrait
	 *
	 * @param string $log comment about this variable.
	 */
	public static function log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			self::log_debug( print_r( $log, true ) );
		} else {
			self::log_debug( $log );
		}
	}

}
