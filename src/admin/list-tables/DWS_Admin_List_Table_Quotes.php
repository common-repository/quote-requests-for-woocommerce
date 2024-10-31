<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Tweaks the default WC order list table to support quotes instead.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Admin_List_Table_Quotes extends WC_Admin_List_Table_Orders {
	// region FIELDS AND CONSTANTS

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	protected $list_table_type = 'dws_shop_quote';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function define_columns( $columns ): array {
		return Arrays::insert_after( parent::define_columns( $columns ), 'order_status', $this->get_date_columns() );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function define_sortable_columns( $columns ): array {
		$date_types = array_keys( $this->get_date_columns() );
		return array_merge( parent::define_sortable_columns( $columns ), array_combine( $date_types, $date_types ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function define_hidden_columns(): array {
		return array_merge( parent::define_hidden_columns(), array_keys( $this->get_date_columns() ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function render_columns( $column, $post_id ): void {
		parent::render_columns( $column, $post_id );

		if ( array_key_exists( $column, $this->get_date_columns() ) ) {
			$this->render_date_column( $column );
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function define_bulk_actions( $actions ): array {
		$actions = parent::define_bulk_actions( $actions );
		foreach ( array_keys( $actions ) as $key ) {
			if ( Strings::starts_with( $key, 'mark_' ) ) {
				unset( $actions[ $key ] );
			}
		}

		return $actions;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function query_filters( $query_vars ): array {
		global $wp_post_statuses;

		// Override the orders statuses.
		if ( empty( $query_vars['post_status'] ) ) {
			$post_statuses = dws_qrwc_get_quote_statuses();

			foreach ( array_keys( $post_statuses ) as $status ) {
				if ( isset( $wp_post_statuses[ $status ] ) && false === $wp_post_statuses[ $status ]->show_in_admin_all_list ) {
					unset( $post_statuses[ $status ] );
				}
			}

			$query_vars['post_status'] = array_keys( $post_statuses );
		}

		// Enable sorting by date columns.
		if ( array_key_exists( $query_vars['orderby'] ?? '', $this->get_date_columns() ) ) {
			$query_vars = array_merge(
				$query_vars,
				array(
					'meta_key' => Strings::maybe_unprefix( $query_vars['orderby'], 'quote' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'orderby'  => 'meta_value_datetime',
				)
			);
		}

		return parent::query_filters( $query_vars );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function render_blank_state() {
		?>

		<div class="woocommerce-BlankState">
			<h2 class="woocommerce-BlankState-message">
				<?php esc_html_e( 'When you receive a new quote request, it will appear here.', 'quote-requests-for-woocommerce' ); ?>
			</h2>
			<div class="woocommerce-BlankState-buttons">
				<a class="woocommerce-BlankState-cta button-primary button" target="_blank" href="https://docs.deep-web-solutions.com/article-categories/quote-requests-for-woocommerce/?utm_source=blankslate&utm_medium=product&utm_content=doc&utm_campaign=plugin">
					<?php esc_html_e( 'Learn more about quotes', 'quote-requests-for-woocommerce' ); ?>
				</a>
			</div>

			<?php

			/**
			 * Triggered after the blank slate message.
			 *
			 * @since   1.0.0
			 * @version 1.0.0
			 */
			do_action( dws_qrwc_get_hook_tag( 'marketplace_suggestions', 'quotes_empty_state' ) );

			?>
		</div>

		<?php
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function render_order_status_column() {
		ob_start();

		parent::render_order_status_column();

		$column_content = ob_get_clean();

		// phpcs:ignore
		echo str_replace(
			'<span>' . esc_html( wc_get_order_status_name( $this->object->get_status() ) ) . '</span>',
			'<span>' . esc_html( dws_qrwc_get_quote_status_name( $this->object->get_status() ) ) . '</span>',
			$column_content
		);
	}

	// endregion

	// region METHODS

	/**
	 * Renders a custom date type column.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $date_column    The date type to display.
	 */
	protected function render_date_column( string $date_column ) {
		$date = Strings::maybe_unprefix( $date_column, 'quote_date_' );

		/* @noinspection PhpParamsInspection */
		if ( dws_qrwc_should_display_quote_date_type( $date, $this->object ) ) {
			echo esc_html( $this->object->get_date_to_display( $date ) );
		} else {
			echo '&mdash;';
		}
	}

	/**
	 * Returns the valid date columns.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_date_columns(): array {
		$quote_date_types = dws_qrwc_get_quote_date_types();
		return array_combine(
			array_map(
				function( string $date ) {
					return Strings::maybe_prefix( $date, 'quote_date_' );
				},
				array_keys( $quote_date_types )
			),
			array_values( $quote_date_types )
		);
	}

	// endregion
}
