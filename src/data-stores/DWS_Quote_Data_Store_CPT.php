<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Abstraction layer for CRUD operations on quote objects.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Quote_Data_Store_CPT extends WC_Order_Data_Store_CPT {
	// region FIELDS AND CONSTANTS

	/**
	 * Data specific to a quote that should also be considered internal.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string[]
	 */
	protected array $quote_internal_meta_keys = array(
		'_date_accepted',
		'_date_rejected',
		'_date_expired',
		'_date_cancelled',
	);

	/**
	 * Map from meta keys to quote specific data.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string[]
	 */
	protected array $quote_meta_keys_to_props = array(
		'_date_accepted'  => 'date_accepted',
		'_date_rejected'  => 'date_rejected',
		'_date_expired'   => 'date_expired',
		'_date_cancelled' => 'date_cancelled',
	);

	// endregion

	// region MAGIC METHODS

	/**
	 * DWS_Quote_Data_Store_CPT constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		foreach ( array_keys( dws_qrwc_get_quote_date_types() ) as $date_type ) {
			$meta_key = Strings::maybe_prefix( $date_type, '_date_' );

			// Only append non-core date types.
			if ( isset( $this->quote_meta_keys_to_props[ $meta_key ] ) ) {
				continue;
			}

			$this->quote_meta_keys_to_props[ $meta_key ] = Strings::maybe_prefix( $date_type, 'date_' );
			$this->quote_internal_meta_keys[]            = $meta_key;
		}

		$this->internal_meta_keys = array_merge( $this->internal_meta_keys, $this->quote_internal_meta_keys );
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function read_order_data( &$quote, $post_object ) {
		// Set all order metadata, as well as date defined by DWS_Quote::$extra_keys which has corresponding setter methods.
		parent::read_order_data( $quote, $post_object );

		foreach ( $this->quote_meta_keys_to_props as $meta_key => $prop_key ) {
			if ( false === Strings::starts_with( $meta_key, '_date_' ) ) {
				continue;
			}

			// Technically, the core data types can be set in the parent, but they would be set wrong.
			// You see, WC_Data::set_date_prop() assumes that any string it gets is using the site timezone and converts
			// it to UTC, but we already did that when saving to the DB. Hence, this string is already in UTC. For it to be
			// set properly, we need to convert it to a UNIX timestamp first.
			$date_value = get_post_meta( $quote->get_id(), $meta_key, true );
			$quote->set_date( $prop_key, Strings::validate( $date_value ) ? dws_qrwc_date_to_time( $date_value ) : null );
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function update_post_meta( &$quote ) {
		$updated_props = array();

		foreach ( $this->get_props_to_update( $quote, $this->quote_meta_keys_to_props ) as $meta_key => $prop ) {
			$meta_value = Strings::starts_with( $prop, 'date_' ) ? $quote->get_formatted_date( $prop ) : $quote->{"get_$prop"}( 'edit' );

			update_post_meta( $quote->get_id(), $meta_key, $meta_value );
			$updated_props[] = $prop;
		}

		/**
		 * Triggered after updating the quote.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		do_action( dws_qrwc_get_hook_tag( 'quote_data_store', 'updated_props' ), $quote, $updated_props );

		parent::update_post_meta( $quote );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_props_to_update( $object, $meta_key_to_props, $meta_type = 'post' ): array {
		$props_to_update = parent::get_props_to_update( $object, $meta_key_to_props, $meta_type );
		$props_to_ignore = $this->get_props_to_ignore();

		foreach ( array_keys( $props_to_ignore ) as $meta_key ) {
			unset( $props_to_update[ $meta_key ] );
		}

		return $props_to_update;
	}

	// endregion

	// region HELPERS

	/**
	 * Returns a list of properties set on a quote which we don't want used on a quote or which might be
	 * inherited from the order metadata.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_props_to_ignore(): array {
		$props_to_ignore = array(
			'_transaction_id' => 'transaction_id',
			'_date_completed' => 'date_completed',
			'_date_paid'      => 'date_paid',
		);

		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote_data_store', 'props_to_ignore' ),
			$props_to_ignore,
			$this
		);
	}

	// endregion
}
