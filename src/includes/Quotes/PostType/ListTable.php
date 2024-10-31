<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Quotes\PostType;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Helpers\AssetsHelpersTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Assets;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\AssetsHelpersAwareInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles modifications related to the edit posts views.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class ListTable extends AbstractPluginFunctionality implements AssetsHelpersAwareInterface {
	// region TRAITS

	use AssetsHelpersTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED FUNCTIONS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'current_screen', $this, 'setup_screen' );
		$hooks_service->add_action( 'check_ajax_referer', $this, 'setup_screen' );
		$hooks_service->add_action( 'admin_print_styles-edit.php', $this, 'enqueue_style' );

		$hooks_service->add_filter( 'woocommerce_admin_order_preview_get_order_details', $this, 'filter_admin_quote_preview_details', 10, 2 );
	}

	// endregion

	// region HOOKS

	/**
	 * Loads a different list table handler for quotes.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup_screen() {
		global $wc_list_table;

		$screen_id = false;

		if ( \function_exists( 'get_current_screen' ) ) {
			$screen    = \get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		// phpcs:disable
		if ( ! empty( $_REQUEST['screen'] ) ) {
			$screen_id = \wc_clean( \wp_unslash( $_REQUEST['screen'] ) );
		}
		// phpcs:enable

		if ( 'edit-dws_shop_quote' === $screen_id ) {
			/* @noinspection PhpIncludeInspection */
			include_once \WC()->plugin_path() . '/includes/admin/list-tables/class-wc-admin-list-table-orders.php';
			$wc_list_table = new \DWS_Admin_List_Table_Quotes();
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		\remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		\remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	/**
	 * Enqueues the stylesheet on the quotes list table page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function enqueue_style() {
		if ( 'dws_shop_quote' === Strings::maybe_cast_input( INPUT_GET, 'post_type' ) ) {
			$plugin        = $this->get_plugin();
			$minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/css/admin/quotes-list-table.css', 'DWS_QRWC_SCRIPT_DEBUG' );
			\wp_enqueue_style(
				$this->get_asset_handle(),
				$minified_path,
				array( 'woocommerce_admin_styles' ),
				Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() )
			);

			$minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/js/admin/quotes-list-table.js', 'DWS_QRWC_SCRIPT_DEBUG' );
			\wp_enqueue_script(
				$this->get_asset_handle(),
				$minified_path,
				array( 'jquery' ),
				Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() ),
				true
			);
		}
	}

	/**
	 * Sets the proper quote status name when previewing a quote in the admin area.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $order_details      The order details.
	 * @param   \WC_Order   $order              The order object.
	 *
	 * @return  array
	 */
	public function filter_admin_quote_preview_details( array $order_details, \WC_Order $order ): array {
		if ( true === dws_qrwc_is_quote( $order ) ) {
			$order_details['status_name'] = dws_qrwc_get_quote_status_name( $order->get_status() );
		}

		return $order_details;
	}

	// endregion
}
