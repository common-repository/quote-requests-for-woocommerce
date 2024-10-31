<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;

defined( 'ABSPATH' ) || exit;

/**
 * Function for outputting a date-time input field inspired by the one included in WC Subscriptions.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int|null    $timestamp  A timestamp for a certain date in the site's timezome. If left empty, or 0, it will
 *                                  be set to today's date.
 * @param   array       $args       A set of name => value pairs to customise the input fields.
 *
 * @return  string
 */
function dws_qrwc_date_input( ?int $timestamp = null, array $args = array() ): string {
	$args = wp_parse_args(
		$args,
		array(
			'name_attr'    => '',
			'include_time' => true,
		)
	);

	$date       = empty( $timestamp ) ? '' : date_i18n( 'Y-m-d', $timestamp );
	$date_input = '<input type="text" class="date-picker dws-qrwc-date-picker" value="' . esc_attr( $date ) . '" placeholder="' . esc_attr__( 'YYYY-MM-DD', 'quote-requests-for-woocommerce' ) . '" name="' . esc_attr( $args['name_attr'] ) . '" id="' . esc_attr( $args['name_attr'] ) . '" maxlength="10" pattern="([0-9]{4})-(0[1-9]|1[012])-(##|0[1-9#]|1[0-9]|2[0-9]|3[01])"/>';

	if ( true === $args['include_time'] ) {
		$hours      = empty( $timestamp ) ? '' : date_i18n( 'H', $timestamp );
		$hour_input = '<input type="text" class="hour" placeholder="' . esc_attr__( 'HH', 'quote-requests-for-woocommerce' ) . '" name="' . esc_attr( $args['name_attr'] ) . '_hour" id="' . esc_attr( $args['name_attr'] ) . '_hour" value="' . esc_attr( $hours ) . '" maxlength="2" size="2" pattern="([01]?[0-9]{1}|2[0-3]{1})" />';

		$minutes      = empty( $timestamp ) ? '' : date_i18n( 'i', $timestamp );
		$minute_input = '<input type="text" class="minute" placeholder="' . esc_attr__( 'MM', 'quote-requests-for-woocommerce' ) . '" name="' . esc_attr( $args['name_attr'] ) . '_minute" id="' . esc_attr( $args['name_attr'] ) . '_minute" value="' . esc_attr( $minutes ) . '" maxlength="2" size="2" pattern="[0-5]{1}[0-9]{1}" />';

		$date_input = sprintf( '%s@%s:%s', $date_input, $hour_input, $minute_input );
	}

	$date_input = "<div class='dws-qrwc-date-input'>$date_input</div>";

	return apply_filters(
		dws_qrwc_get_hook_tag( 'date_input' ),
		$date_input,
		$timestamp,
		$args
	);
}

/**
 * Converts a given date string assumed to be in site's timezone to a UTC timestamp. Inspired by WC Subscriptions.
 *
 * We cannot use strtotime() for this because other code might call date_default_timezone_set() to change the timezone
 * and thus strtotime() will attempt to convert to UTC by adding or deducing the GTM/UTC offset for that timezone.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $date_string    A date string formatted in MySQL or similar that will map correctly when
 *                                  instantiating an instance of DateTime().
 *
 * @return  int|null    UNIX timestamp or null if the date string is malformed.
 */
function dws_qrwc_date_to_time( string $date_string ): ?int {
	if ( empty( $date_string ) ) {
		return null;
	}

	try {
		$date_time = new WC_DateTime( $date_string, new DateTimeZone( 'UTC' ) );
		return Integers::validate( $date_time->getTimestamp() );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Returns the WP theme object instance cached in a static variable to avoid calling the same function multiple times per request.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  WP_Theme|null
 */
function dws_qrwc_get_active_theme(): ?WP_Theme {
	static $theme = null;

	if ( is_null( $theme ) ) {
		$theme = wp_get_theme();
	}

	return $theme->exists() ? $theme : null;
}
