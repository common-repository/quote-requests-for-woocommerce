<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Account\Endpoints;

use DeepWebSolutions\WC_Plugins\QuoteRequests\Account\AbstractEndpoint;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Users;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the output of the single quote view.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class ViewQuote extends AbstractEndpoint {
	// region FIELDS AND CONSTANTS

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const ENDPOINT = 'view-quote';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_endpoint_title(): string {
		global $wp;

		$title = \is_admin() ? \__( 'View quote', 'quote-requests-for-woocommerce' ) : '';

		if ( ! empty( $wp->query_vars['view-quote'] ?? null ) ) {
			$quote = dws_qrwc_get_quote( $wp->query_vars['view-quote'] );
			if ( ! \is_null( $quote ) ) {
				/* translators: %s: order number */
				$title = \sprintf( \__( 'Quote #%s', 'quote-requests-for-woocommerce' ), $quote->get_quote_number() );
			}
		}

		return $title;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_endpoint_description(): string {
		return \__( 'Endpoint for the "My account &rarr; View Quote" page.', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string|null     $quote_id   The ID of the quote being viewed.
	 */
	public function output_endpoint_content( ?string $quote_id = '0' ): void {
		$quote = dws_qrwc_get_quote( $quote_id );

		if ( ! $quote || ! Users::has_capabilities( 'view_dws_quote', array( $quote_id ) ) ) {
			?>

			<div class="woocommerce-error">
				<?php \esc_html_e( 'Invalid quote.', 'quote-requests-for-woocommerce' ); ?>
				<a href="<?php echo \esc_url( \wc_get_page_permalink( 'myaccount' ) ); ?>" class="wc-forward">
					<?php \esc_html_e( 'My account', 'quote-requests-for-woocommerce' ); ?>
				</a>
			</div>

			<?php

			return;
		}

		dws_qrwc_wc_get_template( 'myaccount/view-quote.php', array( 'quote' => $quote ) );
	}

	// endregion
}
