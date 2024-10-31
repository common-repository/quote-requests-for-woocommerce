<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Model for quote objects.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Quote extends WC_Order {
	// region FIELDS AND CONSTANTS

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	protected $data_store_name = 'dws-quote';

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	protected $object_type = 'dws_quote';

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     array
	 */
	protected $extra_data = array(
		'date_accepted'  => null,
		'date_rejected'  => null,
		'date_expired'   => null,
		'date_cancelled' => null,
	);

	// endregion

	// region MAGIC METHODS

	/**
	 * DWS_Quote constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int|DWS_Quote   $quote      The quote ID or quote object to load data from.
	 */
	public function __construct( $quote = 0 ) {
		foreach ( array_keys( dws_qrwc_get_quote_date_types() ) as $date_type ) {
			$date_type_key = Strings::maybe_prefix( $date_type, 'date_' );

			// Include only custom date that are not core types.
			if ( ! isset( $this->extra_data[ $date_type_key ] ) ) {
				$this->extra_data[ $date_type_key ] = null;
			}
		}

		parent::__construct( $quote );
	}

	// endregion

	// region DATE METHODS

	/**
	 * Returns the datetime object of the acceptance time or null if not set.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  WC_DateTime|null
	 */
	public function get_date_accepted( string $context = 'view' ): ?\WC_DateTime {
		return $this->get_date( 'date_accepted', $context );
	}

	/**
	 * Returns the datetime object of the rejection time or null if not set.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  WC_DateTime|null
	 */
	public function get_date_rejected( string $context = 'view' ): ?\WC_DateTime {
		return $this->get_date( 'date_rejected', $context );
	}

	/**
	 * Returns the datetime object of the expiration time or null if not set.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  WC_DateTime|null
	 */
	public function get_date_expired( string $context = 'view' ): ?\WC_DateTime {
		return $this->get_date( 'date_expired', $context );
	}

	/**
	 * Returns the datetime object of the expiration time or null if not set.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  WC_DateTime|null
	 */
	public function get_date_cancelled( string $context = 'view' ): ?\WC_DateTime {
		return $this->get_date( 'date_cancelled', $context );
	}

	/**
	 * Returns the datetime object of a given date or null if not set.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $date_type  One of the dates defined in the $extra_data array.
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  WC_DateTime|null
	 */
	public function get_date( string $date_type, string $context = 'view' ): ?\WC_DateTime {
		$date_type = Strings::maybe_prefix( $date_type, 'date_' );
		return $this->get_prop( $date_type, $context );
	}

	/**
	 * Returns the MySQL formatted date for a given date property.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $date_type  One of the dates defined in the $extra_data array.
	 * @param   string  $timezone   The timezone to return the value in. Either 'gmt' or 'site'.
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  string|null
	 */
	public function get_formatted_date( string $date_type, string $timezone = 'gmt', string $context = 'view' ): ?string {
		$date = $this->get_date( $date_type, $context );

		if ( is_a( $date, 'WC_DateTime' ) ) {
			// Don't change the original date object's timezone as this may affect the prop stored on the quote.
			$date = clone $date;

			if ( 'gmt' === strtolower( $timezone ) ) {
				$date->setTimezone( new DateTimeZone( 'UTC' ) );
			}

			$date = $date->date( 'Y-m-d H:i:s' );
		}

		return $date;
	}

	/**
	 * Returns the timestamp for a given date property.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $date_type  One of the dates defined in the $extra_data array.
	 * @param   string  $timezone   The timezone to return the value in. Either 'gmt' or 'site'.
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return int|null
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function get_timestamp( string $date_type, string $timezone = 'gmt', string $context = 'view' ): ?int {
		$datetime = $this->get_formatted_date( $date_type, $timezone, $context );

		if ( \is_null( $datetime ) ) {
			$timestamp = null;
		} else {
			/* @noinspection PhpUnhandledExceptionInspection */
			$datetime  = new WC_DateTime( $datetime, new DateTimeZone( 'UTC' ) );
			$timestamp = $datetime->getTimestamp();
		}

		return $timestamp;
	}

	/**
	 * Returns a string representation of a quote date in the site's time (i.e., not GMT/UTC timezone).
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $date_type  One of the dates defined in the $extra_data array.
	 *
	 * @return  string
	 */
	public function get_date_to_display( string $date_type ): string {
		$timestamp_gmt = $this->get_timestamp( $date_type );

		if ( is_null( $timestamp_gmt ) ) {
			$date_to_display = _x( '-', 'denotes that there is no date to display', 'quote-requests-for-woocommerce' );
		} else {
			$time_diff = $timestamp_gmt - time();

			if ( $time_diff > 0 && $time_diff < WEEK_IN_SECONDS ) {
				// translators: placeholder is human time diff (e.g. "3 weeks")
				$date_to_display = sprintf( __( 'In %s', 'quote-requests-for-woocommerce' ), human_time_diff( time(), $timestamp_gmt ) );
			} elseif ( $time_diff < 0 && absint( $time_diff ) < WEEK_IN_SECONDS ) {
				// translators: placeholder is human time diff (e.g. "3 weeks")
				$date_to_display = sprintf( __( '%s ago', 'quote-requests-for-woocommerce' ), human_time_diff( time(), $timestamp_gmt ) );
			} else {
				$date_to_display = date_i18n( wc_date_format(), $this->get_timestamp( $date_type, 'site' ) );
			}
		}

		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'get_date_to_display' ),
			$date_to_display,
			$date_type,
			$this
		);
	}

	/**
	 * Sets a quote date property.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $prop   The date property to set. Must be a key of @see dws_qrwc_get_quote_date_types().
	 * @param   null    $date   UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 */
	public function set_date( string $prop, $date = null ) {
		$prop = Strings::maybe_unprefix( $prop, 'date_' );
		if ( array_key_exists( $prop, dws_qrwc_get_quote_date_types() ) ) {
			$date_key = Strings::maybe_prefix( $prop, 'date_' );
			$this->set_date_prop( $date_key, $date );
		}
	}

	// endregion

	// region METHODS

	/**
	 * Semantic wrapper around the 'get_view_order_url' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_view_quote_url(): string {
		return $this->get_view_order_url();
	}

	/**
	 * Generates a URL to accept a quote.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $redirect   Redirect URL.
	 *
	 * @return  string
	 */
	public function get_accept_quote_url( string $redirect = '' ): string {
		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'get_accept_url' ),
			wp_nonce_url(
				add_query_arg(
					array(
						'accept-quote' => 'true',
						'quote-key'    => $this->get_quote_key(),
						'quote-id'     => $this->get_id(),
						'redirect'     => \rawurlencode( $redirect ),
					),
					$this->get_cancel_endpoint()
				),
				'dws-qrwc-accept-quote'
			),
			$redirect,
			$this
		);
	}

	/**
	 * Returns the order created when the quote was accepted.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  WC_Order|null
	 */
	public function get_accepted_order(): ?WC_Order {
		$order = wc_get_order( $this->get_meta( '_accepted_order_id' ) );
		return $order instanceof WC_Order ? $order : null;
	}

	/**
	 * Generates a URL to reject a quote.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $redirect   Redirect URL.
	 *
	 * @return  string
	 */
	public function get_reject_quote_url( string $redirect = '' ): string {
		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'get_reject_url' ),
			wp_nonce_url(
				add_query_arg(
					array(
						'reject-quote' => 'true',
						'quote-key'    => $this->get_quote_key(),
						'quote-id'     => $this->get_id(),
						'redirect'     => \rawurlencode( $redirect ),
					),
					$this->get_cancel_endpoint()
				),
				'dws-qrwc-reject-quote'
			),
			$redirect,
			$this
		);
	}

	/**
	 * Semantic wrapper around the 'get_cancel_order_url' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $redirect   Redirect URL.
	 *
	 * @return  string
	 */
	public function get_cancel_quote_url( string $redirect = '' ): string {
		return $this->get_cancel_order_url( $redirect );
	}

	/**
	 * Semantic wrapper around the 'get_order_number' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_quote_number(): string {
		return $this->get_order_number();
	}

	/**
	 * Semantic wrapper around the 'get_order_key' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $context    What the value is for. Valid values are view and edit.
	 *
	 * @return  string
	 */
	public function get_quote_key( string $context = 'view' ): string {
		return $this->get_order_key( $context );
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_type(): string {
		return 'dws_shop_quote';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_valid_statuses(): array {
		return array_keys( dws_qrwc_get_quote_statuses() );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_editable(): bool {
		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'is_editable' ),
			in_array(
				$this->get_status(),
				dws_qrwc_get_valid_quote_is_editable_statuses( $this ),
				true
			),
			$this
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function needs_payment(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_view_order_url(): string {
		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'get_view_url' ),
			wc_get_endpoint_url( 'view-quote', $this->get_id(), wc_get_page_permalink( 'myaccount' ) ),
			$this
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_cancel_order_url( $redirect = '' ): string {
		return apply_filters(
			dws_qrwc_get_hook_tag( 'quote', 'get_cancel_url' ),
			wp_nonce_url(
				add_query_arg(
					array(
						'cancel-quote' => 'true',
						'quote-key'    => $this->get_quote_key(),
						'quote-id'     => $this->get_id(),
						'redirect'     => \rawurlencode( $redirect ),
					),
					$this->get_cancel_endpoint()
				),
				'dws-qrwc-cancel-quote'
			),
			$redirect,
			$this
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_cancel_order_url_raw( $redirect = '' ): string {
		return $redirect; // not needed since quotes don't have payment gateways
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function set_status( $new_status, $note = '', $manual_update = false ): array {
		if ( $manual_update && empty( $note ) && in_array( Strings::maybe_unprefix( $new_status, 'wc-' ), array( 'quote-accepted', 'quote-rejected' ), true ) ) {
			// Prevent manually setting the status to 'accepted'/'rejected'. This should be done either through a customer action or an admin action.
			return array(
				'from' => $this->get_status(),
				'to'   => $this->get_status(),
			);
		}

		$result = parent::set_status( $new_status, $note, $manual_update );

		if ( true === $this->object_read && $result['from'] !== $result['to'] ) {
			foreach ( array( 'accepted', 'rejected', 'cancelled', 'expired' ) as $date ) {
				$this->set_date( $date, "quote-$date" === $result['to'] ? time() : null );
			}
		}

		return $result;
	}

	// endregion
}
