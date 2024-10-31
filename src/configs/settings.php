<?php

use function  DWS_QRWC_Deps\DI\factory ;
defined( 'ABSPATH' ) || exit;
$global_settings = array(
    'defaults' => array(
    'requests'                         => array(
    'enabled'                   => 'no',
    'valid-customers'           => 'logged-in',
    'valid-products'            => 'categories',
    'valid-products-categories' => array(),
    'valid-products-tags'       => array(),
    'disable-shipping-fields'   => 'yes',
),
    'request-messages'                 => array(
    'price-subject-to-change' => factory( function () {
    return '<i>' . __( 'subject to change until quote is created', 'quote-requests-for-woocommerce' ) . '</i><br/><br/>';
} ),
),
    'request-lists'                    => array(
    'add-to-list-text' => factory( function () {
    return __( 'Request quote', 'quote-requests-for-woocommerce' );
} ),
),
    'request-list-messages'            => array(
    'cannot-add-product-to-shopping-cart' => factory( function () {
    return __( '{product_name} cannot be added to your quote request list because your cart contains shopping items. Please clear your cart first.', 'quote-requests-for-woocommerce' );
} ),
    'cannot-add-product-to-request-cart'  => factory( function () {
    return __( '{product_name} cannot be added to your cart because it contains quote request items. Please clear your cart first.', 'quote-requests-for-woocommerce' );
} ),
    'removed-invalid-product-from-list'   => factory( function () {
    return __( '{product_name} has been removed from your quote request list because it is no longer eligible for quote requests. Please contact us if you need assistance.', 'quote-requests-for-woocommerce' );
} ),
),
    'plugin'                           => array(
    'remove-data-uninstall' => 'no',
),
    'linked-orders-for-wc-integration' => array(
    'allow-quotes-linking'           => 'no',
    'allow-quotes-as-order-children' => 'no',
),
),
    'options'  => array(
    'boolean'  => array(
    'yes' => factory( function () {
    return _x( 'Yes', 'settings', 'quote-requests-for-woocommerce' );
} ),
    'no'  => factory( function () {
    return _x( 'No', 'settings', 'quote-requests-for-woocommerce' );
} ),
),
    'requests' => array(
    'valid-customers'           => array(
    'all'        => factory( function () {
    return __( 'All customers', 'quote-requests-for-woocommerce' );
} ),
    'logged-out' => factory( function () {
    return __( 'Guest customers only', 'quote-requests-for-woocommerce' );
} ),
    'logged-in'  => factory( function () {
    return __( 'Logged-in customers only', 'quote-requests-for-woocommerce' );
} ),
),
    'valid-products'            => array(
    'all'        => factory( function () {
    return __( 'All supported products', 'quote-requests-for-woocommerce' );
} ),
    'categories' => factory( function () {
    return __( 'Supported products that have certain categories assigned', 'quote-requests-for-woocommerce' );
} ),
    'tags'       => factory( function () {
    return __( 'Supported products that have certain tags assigned', 'quote-requests-for-woocommerce' );
} ),
),
    'valid-products-categories' => factory( function () {
    $product_categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ) );
    return \array_combine( \array_column( $product_categories, 'term_id' ), \array_column( $product_categories, 'name' ) );
} ),
    'valid-products-tags'       => factory( function () {
    $product_tags = \get_terms( array(
        'taxonomy'   => 'product_tag',
        'hide_empty' => false,
    ) );
    return \array_combine( \array_column( $product_tags, 'term_id' ), \array_column( $product_tags, 'name' ) );
} ),
),
),
);
return $global_settings;