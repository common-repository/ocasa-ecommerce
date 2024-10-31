<?php
/**
 * Class Woo Helper
 *
 * @package  Conexa\Woo\Helper;
 */

namespace Conexa\Woo\Helper;

/**
 * Helper Main Class
 */
class Helper {
	use TemplateTrait;
	use DebugTrait;
	use LoggerTrait;
	use WooCommerceTrait;
	use AssetsTrait;
}
