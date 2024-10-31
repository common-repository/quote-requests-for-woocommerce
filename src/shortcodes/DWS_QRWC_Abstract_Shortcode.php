<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\OutputtableInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating some of the most often needed functionalities of shortcodes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
abstract class DWS_QRWC_Abstract_Shortcode implements OutputtableInterface {
	// region FIELDS AND CONSTANTS

	/**
	 * The shortcode tag.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public const SHORTCODE = null;

	/**
	 * Single instances of the inheriting classes.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     array
	 */
	protected static $instance = array();

	// endregion

	// region MAGIC METHODS

	/**
	 * DWS_QRWC_Abstract_Shortcode constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function __construct() {}

	/**
	 * Prevent cloning.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	final public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, 'Unserializing instances of this class is forbidden.', '1.0.0' );
		die();
	}

	// endregion

	// region METHODS

	/**
	 * Returns the singleton class instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  DWS_QRWC_Abstract_Shortcode
	 */
	final public static function instance(): DWS_QRWC_Abstract_Shortcode {
		if ( ! isset( static::$instance[ static::class ] ) ) {
			static::$instance[ static::class ] = new static();
		}

		return static::$instance[ static::class ];
	}

	/**
	 * Outputs the shortcode's content.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string|array    $attributes     An associative array of attributes, or an empty string if no attributes are given.
	 * @param   string|null     $content        The enclosed content (if the shortcode is used in its enclosing form).
	 *
	 * @return  OutputFailureException|null
	 */
	final public function output( $attributes = '', ?string $content = null ): ?OutputFailureException {
		if ( ! Request::is_type( 'front' ) ) {
			return new OutputFailureException( 'Shortcodes can only be outputted on front-end requests.' );
		}

		return $this->output_helper( $attributes, $content );
	}

	/**
	 * Inheriting classes should define their output content in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string|array    $attributes     An associative array of attributes, or an empty string if no attributes are given.
	 * @param   string|null     $content        The enclosed content (if the shortcode is used in its enclosing form).
	 *
	 * @return  OutputFailureException|null
	 */
	abstract protected function output_helper( $attributes = '', ?string $content = null ): ?OutputFailureException;

	/**
	 * Returns the shortcode's content.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string|array    $attributes     An associative array of attributes, or an empty string if no attributes are given.
	 * @param   string|null     $content        The enclosed content (if the shortcode is used in its enclosing form).
	 *
	 * @return  string
	 */
	public function return_output( $attributes = '', ?string $content = null ): string {
		ob_start();

		$this->output( $attributes, $content );

		return Strings::validate( ob_get_clean(), '' );
	}

	// endregion
}
