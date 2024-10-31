<?php
/**
 * The Quote Requests for WooCommerce bootstrap file.
 *
 * @since               1.0.0
 * @version             1.0.0
 * @package             DeepWebSolutions\WC-Plugins\QuoteRequests
 * @author              Deep Web Solutions
 * @copyright           2022 Deep Web Solutions
 * @license             GPL-3.0-or-later
 *
 * @noinspection        ALL
   *
 * @wordpress-plugin
 * Plugin Name:             Quote Requests for WooCommerce
 * Plugin URI:              https://www.deep-web-solutions.com/plugins/quote-requests-for-woocommerce/
 * Description:             A WooCommerce extension for allowing customers to submit quote requests to get customized prices before placing their orders.
 * Version:                 1.0.4
 * Requires at least:       5.6
 * Requires PHP:            7.4
 * Author:                  Deep Web Solutions
 * Author URI:              https://www.deep-web-solutions.com
 * License:                 GPL-3.0+
 * License URI:             http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:             quote-requests-for-woocommerce
 * Domain Path:             /src/languages
 * WC requires at least:    5.0
 * WC tested up to:         6.4
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'dws_qrwc_fs' ) ) {
	dws_qrwc_fs()->set_basename( false, __FILE__ );
	return;
}

// Start by autoloading dependencies and defining a few functions for running the bootstrapper.
is_file( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';

// Load plugin-specific bootstrapping functions.
require_once __DIR__ . '/bootstrap-functions.php';

// Check that the DWS WP Framework is loaded.
if ( ! function_exists( '\DWS_QRWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_get_bootstrapper_init_status' ) ) {
	add_action(
		'admin_notices',
		function() {
			$message      = wp_sprintf( /* translators: %s: Plugin name. */ __( 'It seems like <strong>%s</strong> is corrupted. Please reinstall!', 'quote-requests-for-woocommerce' ), dws_qrwc_name() );
			$html_message = wp_sprintf( '<div class="error notice dws-plugin-corrupted-error">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}
	);
	return;
}

// Define plugin constants.
define( 'DWS_QRWC_NAME', DWS_QRWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_name() . ': Quote Requests for WooCommerce' );
define( 'DWS_QRWC_VERSION', '1.0.4' );
define( 'DWS_QRWC_PATH', __FILE__ );
define( 'DWS_QRWC_TEMP_DIR_PATH', DWS_QRWC_Deps\DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_TEMP_DIR_PATH . 'quote-requests-for-woocommerce/' );

// Define minimum environment requirements.
define( 'DWS_QRWC_MIN_PHP', '7.4' );
define( 'DWS_QRWC_MIN_WP', '5.6' );

// Start plugin initialization if system requirements check out.
if ( DWS_QRWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_check_php_wp_requirements_met( dws_qrwc_min_php(), dws_qrwc_min_wp() ) ) {
	if ( ! function_exists( 'dws_qrwc_fs' ) ) {
		include __DIR__ . '/freemius.php';
		dws_qrwc_fs_init();
	}

	include __DIR__ . '/functions.php';
	add_action( 'plugins_loaded', 'dws_qrwc_instance_initialize' );
	register_activation_hook( __FILE__, 'dws_qrwc_plugin_activate' );
} else {
	DWS_QRWC_Deps\DeepWebSolutions\Framework\dws_wp_framework_output_requirements_error( dws_qrwc_name(), dws_qrwc_version(), dws_qrwc_min_php(), dws_qrwc_min_wp() );
}
