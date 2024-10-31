<?php
/**
 * Class Woo Helper
 *
 * @package  Conexa\Woo\Helper;
 */

namespace Conexa\Woo\Helper;

trait AssetsTrait {

	/**
	 * Gets Assets Folder URL
	 *
	 * @return string
	 */
	public static function get_assets_folder_url() {
		return plugin_dir_url( \OcasaWoo::MAIN_FILE ) . 'assets';
	}

}
