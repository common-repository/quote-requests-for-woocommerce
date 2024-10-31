<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Integrations\Plugins;

use DeepWebSolutions\WC_Plugins\QuoteRequests\Integrations\AbstractPluginIntegration;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Integration for the Linked Orders for WC plugin by Deep Web Solutions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class LinkedOrdersForWC extends AbstractPluginIntegration {
	// region TRAITS

	use ActiveLocalTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_dependent_plugin(): array {
		return array(
			'plugin'         => 'linked-orders-for-woocommerce/linked-orders-for-woocommerce.php',
			'fallback_name'  => 'Linked Orders for WooCommerce',
			'min_version'    => '1.2',
			'version_getter' => function() {
				return \function_exists( 'dws_lowc_version' ) ? dws_lowc_version() : '0.0.0';
			},
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_active_local(): bool {
		return dws_qrwc_get_validated_setting( 'allow-quotes-linking', 'linked-orders-for-wc-integration' )
			|| dws_qrwc_get_validated_setting( 'allow-quotes-as-order-children', 'linked-orders-for-wc-integration' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( dws_lowc_get_hook_tag( 'post_type', 'supported_order_types' ), $this, 'register_supported_order_type' );
		$hooks_service->add_filter( dws_lowc_get_hook_tag( 'node', 'status_name' ), $this, 'filter_quote_node_status_name', 10, 2 );

		$hooks_service->add_filter( dws_lowc_get_hook_tag( 'create_linked_order_args' ), $this, 'filter_create_linked_quote_args', 10, 2 );
		$hooks_service->add_filter( dws_lowc_get_component_hook_tag( 'autocompletion', 'should_autocomplete' ), $this, 'prevent_linked_quotes_autocompletion', 99, 2 );

		if ( true === dws_qrwc_get_validated_setting( 'allow-quotes-linking', 'linked-orders-for-wc-integration' ) ) {
			$hooks_service->add_filter( dws_lowc_get_hook_tag( 'post_type', 'valid_statuses_for_new_child' ), $this, 'filter_valid_quote_statuses_for_new_child', 10, 2 );
			$hooks_service->add_filter( dws_qrwc_get_component_hook_tag( 'quote-actions', 'reset_list_table_actions' ), $this, 'preserve_lowc_list_table_actions', 10, 2 );
		}

		if ( true === dws_qrwc_get_validated_setting( 'allow-quotes-as-order-children', 'linked-orders-for-wc-integration' ) ) {
			$hooks_service->add_action( dws_lowc_get_component_hook_tag( 'metabox-output', 'after_add_new_child_button' ), $this, 'maybe_output_add_new_child_quote_button' );
			$hooks_service->add_action( dws_qrwc_get_hook_tag( 'created_order_from_quote' ), $this, 'maybe_link_order_to_quote_parent', 10, 2 );
		}
	}

	// endregion

	// region HOOKS

	/**
	 * Registers quotes as a supported order type for linking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $order_types    Supported order types.
	 *
	 * @return  array
	 */
	public function register_supported_order_type( array $order_types ): array {
		$order_types[] = 'dws_shop_quote';
		return $order_types;
	}

	/**
	 * Filters the quote status name inside the meta-box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $status_name    The status name to display.
	 * @param   \WP_Post_Type   $post_type      The post type object of the child.
	 *
	 * @return  string
	 */
	public function filter_quote_node_status_name( string $status_name, \WP_Post_Type $post_type ): string {
		if ( 'dws_shop_quote' === $post_type->name ) {
			$status_name = dws_qrwc_get_quote_status_name( $status_name );
		}

		return $status_name;
	}

	/**
	 * Filters the arguments for creating a new linked order to create a new quote if the parent is a quote.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $args       The 'create new order' arguments.
	 * @param   \WC_Order   $parent     The parent order.
	 *
	 * @return  array
	 */
	public function filter_create_linked_quote_args( array $args, \WC_Order $parent ): array {
		if ( true === dws_qrwc_is_quote( $parent ) || true === Booleans::maybe_cast_input( INPUT_GET, 'create_quote', false ) ) {
			$args['status']          = 'quote-request';
			$args['create_function'] = 'dws_qrwc_create_quote';
		}

		return $args;
	}

	/**
	 * Quote descendants should NOT be autocompleted.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $should_autocomplete    Whether to autocomplete the descendant or not.
	 * @param   int     $descendant_id          The ID of the descendant.
	 *
	 * @return  bool
	 */
	public function prevent_linked_quotes_autocompletion( bool $should_autocomplete, int $descendant_id ): bool {
		if ( true === dws_qrwc_is_quote( $descendant_id ) ) {
			$should_autocomplete = false;
		}

		return $should_autocomplete;
	}

	/**
	 * Registers valid quote statuses for linked orders.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $statuses       Valid statuses.
	 * @param   string  $order_type     Order type.
	 *
	 * @return  array
	 */
	public function filter_valid_quote_statuses_for_new_child( array $statuses, string $order_type ): array {
		if ( 'dws_shop_quote' === $order_type ) {
			$statuses = \array_keys( dws_qrwc_get_quote_statuses( false ) );
		}

		return $statuses;
	}

	/**
	 * Preserves the Linked Orders for WC list table actions for quotes.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $actions            Quote list table actions.
	 * @param   array   $original_actions   Original quote list table actions.
	 *
	 * @return  array
	 */
	public function preserve_lowc_list_table_actions( array $actions, array $original_actions ): array {
		$actions += \array_intersect_key(
			$original_actions,
			array(
				'view_all_customer_orders'  => '',
				'view_all_linked_orders'    => '',
				'create_empty_linked_child' => '',
			)
		);

		return $actions;
	}

	/**
	 * Outputs a new button on orders to link a quote child.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Order_Node     $dws_node   The DWS Order Node object.
	 *
	 * @return  void
	 */
	public function maybe_output_add_new_child_quote_button( \DWS_Order_Node $dws_node ): void {
		if ( 'shop_order' === $dws_node->get_post_type()->name ) {
			$link = \wp_nonce_url( \admin_url( 'admin-post.php?action=dws_lowc_create_linked_child&parent_id=' . $dws_node->get_id() . '&create_quote=' . Booleans::to_string( true ) ), 'dws_create_linked_child' );
			?>

			<a class="button button-alt" href="<?php echo \esc_url( $link ); ?>">
				<?php \esc_html_e( 'Add new child Quote', 'quote-requests-for-woocommerce' ); ?>
			</a>

			<?php
		}
	}

	/**
	 * If the quote has an order as a parent, link the newly created order as to the same parent.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \WC_Order   $order  The object of the newly created order.
	 * @param   \DWS_Quote  $quote  The object of the accepted quote.
	 *
	 * @return  void
	 */
	public function maybe_link_order_to_quote_parent( \WC_Order $order, \DWS_Quote $quote ): void {
		$root_object = dws_lowc_get_root_order( $quote );
		if ( 'shop_order' === $root_object->get_order()->get_type() ) {
			dws_lowc_link_orders( $root_object->get_id(), $order->get_id() );
		}
	}

	// endregion
}
