<?php

use function  DWS_QRWC_Deps\DI\factory ;
defined( 'ABSPATH' ) || exit;
$global_settings = (include 'settings.php');
$boolean_choices = array_merge( array(
    'global' => factory( function () {
    return __( 'Follow global settings', 'quote-requests-for-woocommerce' );
} ),
), $global_settings['options']['boolean'] );
$product_settings = array(
    'defaults' => array(
    'general' => array(
    'is-valid-product' => 'global',
    'valid-customers'  => 'global',
),
    'ui'      => array(
    'add-to-list-text' => '',
),
),
    'options'  => array(
    'general' => array(
    'is-valid-product' => array(
    'global' => factory( function () {
    return __( 'Follow global settings', 'quote-requests-for-woocommerce' );
} ),
    'yes'    => factory( function () {
    return __( 'Always allow requests for this product', 'quote-requests-for-woocommerce' );
} ),
    'no'     => factory( function () {
    return __( 'Never allow requests for this product', 'quote-requests-for-woocommerce' );
} ),
),
    'valid-customers'  => array_merge( array(
    'global' => factory( function () {
    return __( 'Follow global settings', 'quote-requests-for-woocommerce' );
} ),
), $global_settings['options']['requests']['valid-customers'] ),
),
),
);
return $product_settings;