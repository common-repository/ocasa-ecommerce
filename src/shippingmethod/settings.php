<?php
/**
 * Class OcasaWoo Shipping Class
 *
 * @package  Conexa\OcasaWoo\ShippingMethod;
 */

namespace Conexa\OcasaWoo\ShippingMethod;

/**
 * Settings for OcasaWoo rate shipping.
 */

defined( 'ABSPATH' ) || exit;

$settings = array(
	'title' => array(
		'title'       => __( 'Nombre Método Envío', 'ocasawootextdomain' ),
		'type'        => 'text',
		'description' => __( 'Nombre con el que aparecerá el tipo de envío en tu tienda.', 'ocasawootextdomain' ),
		'default'     => __( 'Ocasa', 'ocasawootextdomain' ),
		'desc_tip'    => true,
	),

);

return $settings;
