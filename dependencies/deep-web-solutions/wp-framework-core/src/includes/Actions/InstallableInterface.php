<?php

namespace DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UpdateFailureException;
\defined( 'ABSPATH' ) || exit;
/**
 * Describes an instance that has an installation routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
interface InstallableInterface extends UninstallableInterface {

	/**
	 * Describes the data installation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InstallFailureException|null
	 */
	public function install() : ?InstallFailureException;
	/**
	 * Describes the data update logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $current_version    The currently installed version.
	 *
	 * @return  UpdateFailureException|null
	 */
	public function update( string $current_version) : ?UpdateFailureException;
	/**
	 * Describes the data uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string|null     $current_version        The currently installed version.
	 *
	 * @return  UninstallFailureException|null
	 */
	public function uninstall( ?string $current_version = null) : ?UninstallFailureException;
	/**
	 * Returns the current version of the installable data of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_current_version() : string;
}
