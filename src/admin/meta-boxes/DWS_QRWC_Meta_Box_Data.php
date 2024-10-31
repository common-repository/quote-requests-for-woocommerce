<?php

defined( 'ABSPATH' ) || exit;

/**
 * Tweaks the default WC order data meta box for quotes support.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_QRWC_Meta_Box_Data extends WC_Meta_Box_Order_Data {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public static function output( $post ) {
		global $theorder;

		if ( ! is_object( $theorder ) ) {
			$theorder = dws_qrwc_get_quote( $post->ID );
		}

		$quote = $theorder;

		self::init_address_fields();

		$order_type_object = get_post_type_object( $post->post_type );
		wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );

		include dirname( __FILE__ ) . '/views/html-quote-data.php';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public static function save( $order_id, ?WP_Post $post = null ) {
		if ( 'dws_shop_quote' === $post->post_type ) {
			$_POST['_payment_method'] = '';
			parent::save( $order_id );
		}
	}

	// endregion
}
