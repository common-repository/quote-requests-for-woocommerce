<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\FileSystem\Files;

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper around WooCommerce's 'date_input_html_pattern' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_wc_date_input_html_pattern(): string {
	$wc_standard = apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' );
	return apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'date_input_html_pattern' ), $wc_standard );
}

/**
 * Wrapper around WooCommerce's 'enable_order_notes_field' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool
 */
function dws_qrwc_wc_enable_order_notes_field(): bool {
	$wc_standard = apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) );
	return Booleans::maybe_cast(
		apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'enable_order_notes_field' ), $wc_standard ),
		false
	);
}

/**
 * Wrapper around WooCommerce's 'continue_shopping_redirect' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_wc_continue_shopping_redirect(): string {
	$shop_url         = wc_get_page_permalink( 'shop' );
	$default_redirect = wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), $shop_url ) : $shop_url;
	$wc_standard      = apply_filters( 'woocommerce_continue_shopping_redirect', $default_redirect );

	return Strings::maybe_cast(
		apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'continue_shopping_redirect' ), $wc_standard, $default_redirect, $shop_url ),
		$default_redirect
	);
}

/**
 * Wrapper around WooCommerce's 'return_to_shop_redirect' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_wc_return_to_shop_redirect(): string {
	$shop_url    = wc_get_page_permalink( 'shop' );
	$wc_standard = apply_filters( 'woocommerce_return_to_shop_redirect', $shop_url );

	return Strings::maybe_cast(
		apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'return_to_shop_redirect' ), $wc_standard, $shop_url ),
		$shop_url
	);
}

/**
 * Wrapper around WooCommerce's 'return_to_shop_text' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_wc_return_to_shop_text(): string {
	$default_text = __( 'Return to shop', 'woocommerce' ); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
	$wc_standard  = apply_filters( 'woocommerce_return_to_shop_text', $default_text );

	return Strings::maybe_cast(
		apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'return_to_shop_text' ), $wc_standard, $default_text ),
		$default_text
	);
}

/**
 * Wrapper around WooCommerce's 'purchase_order_item_types' filter.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  array
 */
function dws_qrwc_wc_purchase_order_item_types(): array {
	$wc_standard = apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' );
	return apply_filters( dws_qrwc_get_hook_tag( 'woocommerce', 'purchase_order_item_types' ), Arrays::validate( $wc_standard, array( $wc_standard ) ) );
}

/**
 * Wrapper around WooCommerce's 'wc_get_template' function.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $template_name          Template name.
 * @param   array   $args                   Template arguments.
 * @param   string  $templates_subfolder    Optional default path subfolder.
 */
function dws_qrwc_wc_get_template( string $template_name, array $args = array(), string $templates_subfolder = '' ) {
	wc_get_template(
		$template_name,
		$args,
		'woocommerce/quote-requests/',
		trailingslashit( dws_qrwc_instance()::get_plugin_templates_path( true ) . $templates_subfolder )
	);
}

/**
 * Buffered wrapper around WooCommerce's 'wc_get_template' function.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $template_name          Template name.
 * @param   array   $args                   Template arguments.
 * @param   string  $templates_subfolder    Optional default path subfolder.
 *
 * @return  string
 */
function dws_qrwc_wc_get_template_html( string $template_name, array $args = array(), string $templates_subfolder = '' ): string {
	ob_start();
	dws_qrwc_wc_get_template( $template_name, $args, $templates_subfolder );
	return ob_get_clean();
}
