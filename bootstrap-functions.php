<?php
/**
 * Defines plugin-specific getters and functions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests
 *
 * @noinspection PhpMissingReturnTypeInspection
 */

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\FileSystem\Files;
use DWS_QRWC_Deps\Psr\Log\LogLevel;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the whitelabel name of the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function dws_qrwc_name() {
	return defined( 'DWS_QRWC_NAME' )
		? DWS_QRWC_NAME : 'Quote Requests for WooCommerce';
}

/**
 * Returns the version of the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_qrwc_version() {
	return defined( 'DWS_QRWC_VERSION' )
		? DWS_QRWC_VERSION : null;
}

/**
 * Returns the path to the plugin's main file.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_qrwc_path() {
	return defined( 'DWS_QRWC_PATH' )
		? DWS_QRWC_PATH : null;
}

/**
 * Returns the path to the plugin's temp directory.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string      $subfolder      Optional subfolder inside the base folder.
 *
 * @return  string|null
 */
function dws_qrwc_temp_dir_path( string $subfolder ) {
	if ( ! defined( 'DWS_QRWC_TEMP_DIR_PATH' ) ) {
		return null;
	}

	$path = Files::generate_full_path( DWS_QRWC_TEMP_DIR_PATH, $subfolder );
	if ( ! is_dir( $path ) ) {
		$result = mkdir( $path, 0777, true );
		if ( true !== $result ) {
			dws_qrwc_instance()->log_event_and_finalize( "Failed to create temporary path $path", array(), LogLevel::ALERT );
			return null;
		} else { // create .htaccess file and empty index.php to protect in case an open web folder is used
			file_put_contents( trailingslashit( $path ) . '.htaccess', 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			touch( trailingslashit( $path ) . 'index.php' );
		}
	}

	return $path;
}

/**
 * Returns the minimum PHP version required to run the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_qrwc_min_php() {
	return defined( 'DWS_QRWC_MIN_PHP' )
		? DWS_QRWC_MIN_PHP : null;
}

/**
 * Returns the minimum WP version required to run the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string|null
 */
function dws_qrwc_min_wp() {
	return defined( 'DWS_QRWC_MIN_WP' )
		? DWS_QRWC_MIN_WP : null;
}
