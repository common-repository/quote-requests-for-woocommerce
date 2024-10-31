<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Quotes\PostType;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Helpers\AssetsHelpersTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Assets;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles actions related to the meta boxes displayed on the edit quote screens.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class MetaBoxes extends AbstractPluginFunctionality {
	// region TRAITS

	use AssetsHelpersTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );
		$hooks_service->add_action( 'add_meta_boxes', $this, 'remove_meta_boxes', 35 ); // priority must be higher than 30
		$hooks_service->add_action( 'woocommerce_process_shop_order_meta', $this, 'remove_meta_box_save', -1, 2 ); // priority must be lower than 10

		$hooks_service->add_action( 'add_meta_boxes', $this, 'add_meta_boxes', 25 ); // priority must be lower than 30
		$hooks_service->add_action( 'woocommerce_process_shop_order_meta', null, 'DWS_QRWC_Meta_Box_Data::save', 40, 2 ); // same priority as the meta box it replaces
	}

	// endregion

	// region HOOKS

	/**
	 * Enqueues assets on the quote edit page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $hook_suffix    The current admin page.
	 */
	public function enqueue_admin_assets( string $hook_suffix ) {
		if ( ! \in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || 'dws_shop_quote' !== \get_post_type() ) {
			return;
		}

		$plugin        = $this->get_plugin();
		$minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/css/admin/quotes-metaboxes.css', 'DWS_QRWC_SCRIPT_DEBUG' );
		\wp_enqueue_style(
			$this->get_asset_handle(),
			$minified_path,
			array(),
			Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() )
		);

		$minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/js/admin/quotes-metaboxes.js', 'DWS_QRWC_SCRIPT_DEBUG' );
		\wp_enqueue_script(
			$this->get_asset_handle(),
			$minified_path,
			array( 'wc-admin-meta-boxes' ),
			Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() ),
			true
		);
	}

	/**
	 * Removes the core order data meta box since we replace it with a custom-built one.
	 * Also removes the core downloadable permissions meta box since it's not needed.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function remove_meta_boxes(): void {
		\remove_meta_box( 'woocommerce-order-data', 'dws_shop_quote', 'normal' );
		\remove_meta_box( 'woocommerce-order-downloads', 'dws_shop_quote', 'normal' );
	}

	/**
	 * Removes the saving hooks on meta boxes that we've removed.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   int         $post_id    The ID of the order being saved.
	 * @param   \WP_Post    $post       The post object of the order being saved.
	 */
	public function remove_meta_box_save( int $post_id, \WP_Post $post ) {
		if ( 'dws_shop_quote' === $post->post_type ) {
			\remove_action( 'woocommerce_process_shop_order_meta', 'WC_Meta_Box_Order_Data::save', 40 );
			\remove_action( 'woocommerce_process_shop_order_meta', 'WC_Meta_Box_Order_Downloads::save', 30 );
		}
	}

	/**
	 * Adds new meta boxes to admin screens.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_meta_boxes(): void {
		\add_meta_box(
			'dws-quote-data',
			\_x( 'Quote Data', 'meta box title', 'quote-requests-for-woocommerce' ),
			'DWS_QRWC_Meta_Box_Data::output',
			'dws_shop_quote',
			'normal',
			'high'
		);

		/**
		 * Triggered after registering the quote data meta-box.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		\do_action( $this->get_hook_tag( 'register_meta_boxes' ) );
	}

	// endregion
}
