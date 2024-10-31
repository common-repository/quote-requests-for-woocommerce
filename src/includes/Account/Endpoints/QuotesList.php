<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Account\Endpoints;

use DeepWebSolutions\WC_Plugins\QuoteRequests\Account\AbstractEndpoint;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the output of the quotes list.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class QuotesList extends AbstractEndpoint {
	// region FIELDS AND CONSTANTS

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const ENDPOINT = 'quotes';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		parent::register_hooks( $hooks_service );

		$hooks_service->add_filter( 'woocommerce_account_menu_items', $this, 'add_menu_items' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $ignore_pagination      Whether to ignore the current page when returning the title or not.
	 */
	public function get_endpoint_title( bool $ignore_pagination = false ): string {
		global $wp;

		return \is_admin() || empty( $wp->query_vars['quotes'] ?? null ) || $ignore_pagination
			? \__( 'Quotes', 'quote-requests-for-woocommerce' )
			/* translators: %d: page number. */
			: \sprintf( \__( 'Quotes (page %d)', 'quote-requests-for-woocommerce' ), \max( \absint( $wp->query_vars[ self::ENDPOINT ] ), 1 ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_endpoint_description(): string {
		return \__( 'Endpoint for the "My account &rarr; Quotes" page.', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string      $current_page   Current pagination page.
	 */
	public function output_endpoint_content( string $current_page = '1' ): void {
		$current_page    = empty( $current_page ) ? 1 : \absint( $current_page );
		$customer_quotes = \wc_get_orders(
			\apply_filters(
				$this->get_hook_tag( 'query_args' ),
				array(
					'type'     => 'dws_shop_quote',
					'status'   => \array_keys( dws_qrwc_get_quote_statuses() ),
					'customer' => \get_current_user_id(),
					'page'     => $current_page,
					'paginate' => true,
				)
			)
		);

		dws_qrwc_wc_get_template(
			'myaccount/quotes.php',
			array(
				'current_page'    => $current_page,
				'customer_quotes' => $customer_quotes,
				'has_quotes'      => 0 < $customer_quotes->total,
			)
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Registers a new menu item for this endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $menu_items     Registered menu items.
	 *
	 * @return  array
	 */
	public function add_menu_items( array $menu_items ): array {
		if ( empty( \WC()->query->get_query_vars()[ self::ENDPOINT ] ) ) {
			return $menu_items; // Hide the quotes' endpoint if the setting is empty.
		}

		return isset( $menu_items['orders'] )
			? Arrays::insert_after( $menu_items, 'orders', array( self::ENDPOINT => $this->get_endpoint_title( true ) ) )
			: \array_merge( $menu_items, array( self::ENDPOINT => $this->get_endpoint_title( true ) ) );
	}

	// endregion
}
