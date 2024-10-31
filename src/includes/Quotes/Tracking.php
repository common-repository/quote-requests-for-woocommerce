<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Quotes;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UpdateFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\InstallableInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Shortcodes\Actions\SetupShortcodesTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Shortcodes\ShortcodesService;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers a shortcode for quote checking that allows even guest users to query their status.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Tracking extends AbstractPluginFunctionality implements InstallableInterface {
	// region TRAITS

	use SetupHooksTrait;
	use SetupShortcodesTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'is_woocommerce', $this, 'filter_is_woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_shortcodes( ShortcodesService $shortcodes_service ): void {
		$shortcodes_service->add_shortcode( \DWS_Quote_Tracking_SC::SHORTCODE, \DWS_Quote_Tracking_SC::instance(), 'return_output' );
	}

	// endregion

	// region INSTALLATION

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function install(): ?InstallFailureException {
		$result = null;

		$page = dws_qrwc_get_validated_setting( 'tracking-page', 'general' );
		if ( empty( $page ) ) {
			$page_id = \wp_insert_post(
				array(
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_author'    => \get_current_user_id(),
					'post_title'     => \__( 'Quote request status', 'quote-requests-for-woocommerce' ),
					'post_content'   => \sprintf( '[%s]', \DWS_Quote_Tracking_SC::SHORTCODE ),
					'post_parent'    => 0,
					'comment_status' => 'closed',
				)
			);
			if ( \is_numeric( $page_id ) && 0 < $page_id ) {
				if ( false === \update_option( 'dws-qrwc_general_tracking-page', $page_id ) ) {
					$result = new InstallFailureException( \__( 'Failed to set quote status page', 'quote-requests-for-woocommerce' ) );
				}
			} else {
				$result = new InstallFailureException( \__( 'Failed to create quote status page', 'quote-requests-for-woocommerce' ) );
			}
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function update( string $current_version ): ?UpdateFailureException {
		return null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function uninstall( ?string $current_version = null ): ?UninstallFailureException {
		$result = null;

		$page = dws_qrwc_get_validated_setting( 'tracking-page', 'general' );
		if ( ! empty( $page ) && 'trash' !== $page->post_status ) {
			if ( empty( \wp_trash_post( $page ) ) ) {
				$result = new UninstallFailureException( \__( 'Failed to delete the quote status page', 'quote-requests-for-woocommerce' ) );
			}
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_current_version(): string {
		return '1.0.0';
	}

	// endregion

	// region HOOKS

	/**
	 * Marks the quote tracking page as a WC page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $is_woocommerce     Whether the current page is already a WC page or not.
	 *
	 * @return  bool
	 */
	public function filter_is_woocommerce( bool $is_woocommerce ): bool {
		return $is_woocommerce || dws_qrwc_is_quote_tracking_page();
	}

	// endregion
}
