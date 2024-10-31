<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the raw database value of a product's setting.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $field_id       The ID of the settings field to retrieve.
 * @param   int             $product_id     The ID of the product to retrieve the setting from.
 * @param   string|null     $group          The group to retrieve the value from.
 *
 * @return  mixed|null
 */
function dws_qrwc_get_raw_product_setting( string $field_id, int $product_id, ?string $group = null ) {
	$group = is_null( $group ) ? 'product-settings' : Strings::maybe_suffix( $group, '-product-settings' );
	return dws_qrwc_component( $group )->get_field_value( $field_id, $product_id );
}

/**
 * Returns the validated database value of a product's setting.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $field_id       The ID of the settings field to retrieve.
 * @param   int             $product_id     The ID of the product to retrieve the setting from.
 * @param   string|null     $group          The group to retrieve the value from.
 *
 * @return  mixed|null
 */
function dws_qrwc_get_validated_product_setting( string $field_id, int $product_id, ?string $group = null ) {
	$group = is_null( $group ) ? 'product-settings' : Strings::maybe_suffix( $group, '-product-settings' );
	return dws_qrwc_component( $group )->get_validated_field_value( $field_id, $product_id );
}

/**
 * Validates a given value against the validation routine of a given product settings field.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   mixed           $value          The value to validate.
 * @param   string          $field_id       The ID of the settings field the value belongs to.
 * @param   string|null     $group          The group to validate the value with.
 *
 * @return  mixed
 */
function dws_qrwc_validate_product_setting( $value, string $field_id, ?string $group = null ) {
	$group = is_null( $group ) ? 'product-settings' : Strings::maybe_suffix( $group, '-product-settings' );
	return dws_qrwc_component( $group )->validate_field_value( $value, $field_id );
}

/**
 * For a given global settings field ID, returns the product-level field ID that overwrites it, if applicable.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @param   string          $global_field_id    The global field ID.
 * @param   string|null     $global_group       The ID of the global group that the field belongs to.
 *
 * @return  string|null
 */
function dws_qrwc_map_global_settings_field_to_product_settings_field( string $global_field_id, ?string $global_group = null ): ?string {
	$mapping = null;

	$global_field = is_null( $global_group ) ? $global_field_id : "$global_group/$global_field_id";
	switch ( $global_field ) {
		case 'requests/valid-customers':
			$mapping = \str_replace( 'requests', 'general', $global_field );
			break;
		case 'request-lists/add-to-list-text':
			$mapping = \str_replace( 'request-lists', 'ui', $global_field );
			break;
	}

	if ( dws_qrwc_fs()->can_use_premium_code__premium_only() ) {
		switch ( $global_field ) {
			case 'requests/valid-customers-callback':
			case 'requests/valid-customers-user-roles':
				$mapping = \str_replace( 'requests', 'general', $global_field );
				break;
			case 'requests/hide-valid-products-from-invalid-customers':
				$mapping = 'advanced/hide-from-invalid-customers-if-valid';
				break;
			case 'requests/disable-valid-products-purchasing':
				$mapping = 'advanced/disable-purchasing-if-valid';
				break;
			case 'requests/hide-valid-products-price':
				$mapping = 'advanced/hide-price-if-valid';
				break;
			case 'requests/allow-out-of-stock-valid-products':
				$mapping = 'advanced/allow-if-out-of-stock';
				break;
		}
	}

	return \apply_filters( dws_qrwc_get_hook_tag( 'map_global_settings_field_to_product_settings_field' ), $mapping, $global_field );
}

/**
 * Returns a given setting's value for a given product.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string      $global_field_id    The ID of the global settings field.
 * @param   string      $global_group       The global group that the setting belongs to.
 * @param   int         $product_id         The ID of the product to check for overriding values.
 * @param   bool|null   $overridden         Reference parameter that is set to whether the setting was locally overridden or not.
 *
 * @return  mixed|null
 */
function dws_qrwc_get_validated_setting_maybe_merged_with_product_setting( string $global_field_id, string $global_group, int $product_id, ?bool &$overridden = null ) {
	$global_setting  = dws_qrwc_get_validated_setting( $global_field_id, $global_group );
	$product_setting = 'global';

	if ( true === dws_qrwc_are_requests_enabled() && true === dws_qrwc_is_supported_request_product( $product_id ) ) {
		$product_field_id = dws_qrwc_map_global_settings_field_to_product_settings_field( $global_field_id, $global_group );
		if ( ! is_null( $product_field_id ) ) {
			$product_setting = dws_qrwc_get_validated_product_setting( $product_field_id, $product_id );
		}
	}

	$overridden = ( 'global' !== $product_setting );
	return $overridden
		? dws_qrwc_validate_setting( $product_setting, $global_field_id, $global_group )
		: $global_setting;
}
