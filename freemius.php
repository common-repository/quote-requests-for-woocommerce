<?php

defined( 'ABSPATH' ) || exit;
/**
 * Returns the Freemius instance of the current plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Freemius
 */
function dws_qrwc_fs() : Freemius
{
    global  $dws_qrwc_fs ;
    
    if ( !isset( $dws_qrwc_fs ) ) {
        // Activate multisite network integration.
        if ( !defined( 'WP_FS__PRODUCT_8856_MULTISITE' ) ) {
            define( 'WP_FS__PRODUCT_8856_MULTISITE', true );
        }
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
        /* @noinspection PhpUnhandledExceptionInspection */
        $dws_qrwc_fs = fs_dynamic_init( array(
            'id'             => '8856',
            'slug'           => 'quote-requests-for-woocommerce',
            'type'           => 'plugin',
            'public_key'     => 'pk_83d1d005bed8db09703e58dad1c8f',
            'is_premium'     => false,
            'premium_suffix' => 'Premium',
            'has_addons'     => true,
            'has_paid_plans' => true,
            'menu'           => array(
            'first-path' => 'plugins.php',
        ),
            'is_live'        => true,
        ) );
    }
    
    return $dws_qrwc_fs;
}

/**
 * Initializes the Freemius global instance and sets a few defaults.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  Freemius
 */
function dws_qrwc_fs_init() : Freemius
{
    $freemius = dws_qrwc_fs();
    do_action( 'dws_qrwc_fs_initialized' );
    $freemius->add_filter( 'after_skip_url', 'dws_qrwc_fs_settings_url' );
    $freemius->add_filter( 'after_connect_url', 'dws_qrwc_fs_settings_url' );
    $freemius->add_filter( 'after_pending_connect_url', 'dws_qrwc_fs_settings_url' );
    return $freemius;
}

/**
 * Returns the URL to the settings page.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_fs_settings_url() : string
{
    return ( dws_qrwc_instance()->is_active() ? admin_url( 'admin.php?page=wc-settings&tab=dws-quotes' ) : admin_url( 'plugins.php' ) );
}
