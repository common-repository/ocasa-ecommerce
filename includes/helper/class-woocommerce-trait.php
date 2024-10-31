<?php
/**
 * Class Woo Helper
 *
 * @package  Conexa\Woo\Helper;
 */

namespace Conexa\Woo\Helper;

/**
 * Woocommerce Trait
 */
trait WooCommerceTrait {

	/**
	 * Gets product dimensions and details
	 *
	 * @param int $product_id  comment about this variable.
	 * @return false|array
	 */
	public static function get_product_dimensions( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;}

		$dimension_unit = 'cm';
		$weight_unit    = 'kg';

		$height = ( $product->get_height() ? wc_get_dimension( $product->get_height(), $dimension_unit ) : '0' );
		$width  = ( $product->get_width() ? wc_get_dimension( $product->get_width(), $dimension_unit ) : '0' );
		$length = ( $product->get_length() ? wc_get_dimension( $product->get_length(), $dimension_unit ) : '0' );

		return array(
			'height'          => ( $product->get_height() ? wc_get_dimension( $product->get_height(), $dimension_unit ) : '0' ),
			'width'           => ( $product->get_width() ? wc_get_dimension( $product->get_width(), $dimension_unit ) : '0' ),
			'length'          => ( $product->get_length() ? wc_get_dimension( $product->get_length(), $dimension_unit ) : '0' ),
			'weight'          => ( $product->has_weight() ? wc_get_weight( $product->get_weight(), $weight_unit ) : '0' ),
			'price'           => $product->get_price(),
			'description'     => $product->get_name(),
			'id'              => $product_id,
			'sku'             => $product->get_sku(),
			'wc-product-size' => $height * $width * $length,
		);
	}

	/**
	 * Gets all items from a cart
	 *
	 * @param WC_Cart $cart  comment about this variable.
	 * @return false|array
	 */
	public static function get_items_from_cart( $cart ) {
		$products = array();
		$items    = $cart->get_cart();
		foreach ( $items as $item ) {
			$product_id = $item['data']->get_id();
			$product    = wc_get_product( $product_id );
			if ( ! ( $product->is_downloadable( 'yes' ) || $product->is_virtual( 'yes' ) ) ) {
				$new_product = self::get_product_dimensions( $product_id );
				if ( ! $new_product ) {
					self::log_error( __( 'Helper -> Error obteniendo productos del carrito, producto con malas dimensiones - ID: ', 'ocasawootextdomain' ) . $product_id );
					return false;
				}
				for ( $i = 0;$i < $item['quantity'];$i++ ) {
					$products[] = $new_product;
				}
			}
		}
		return $products;
	}

	/**
	 * Gets items from an order
	 *
	 * @param WC_Order $order  comment about this variable.
	 * @return false|array
	 */
	public static function get_items_from_order( $order ) {
		$products = array();
		$items    = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item->get_variation_id();
			if ( ! $product_id ) {
				$product_id = $item->get_product_id();}
			$product = wc_get_product( $product_id );
			if ( ! ( $product->is_downloadable( 'yes' ) || $product->is_virtual( 'yes' ) ) ) {
				$new_product = self::get_product_dimensions( $product_id );
				if ( ! $new_product ) {
					self::log_error( __( 'Helper -> Error obteniendo productos de la orden, producto con malas dimensiones - ID: ', 'ocasawootextdomain' ) . $product_id );
					return false;
				}
				for ( $i = 0;$i < $item->get_quantity();$i++ ) {
					$products[] = $new_product;
				}
			}
		}
		return $products;
	}

	/**
	 * Groups an array of items
	 *
	 * @param array $items  comment about this variable.
	 * @return array
	 */
	public static function group_items( array $items ) {
		$grouped_items = array();
		foreach ( $items as $item ) {
			if ( isset( $grouped_items[ $item['id'] ] ) ) {
				$grouped_items[ $item['id'] ]['quantity']++;
			} else {
				$grouped_items[ $item['id'] ]             = $item;
				$grouped_items[ $item['id'] ]['quantity'] = 1;
			}
		}
		return $grouped_items;
	}
}
