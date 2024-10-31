<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Account;
use DeepWebSolutions\WC_Plugins\QuoteRequests\CustomerRequests;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Customers;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Emails;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Integrations;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Permissions;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin;
use DeepWebSolutions\WC_Plugins\QuoteRequests\ProductSettings;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Quotes;
use DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Requests;
use DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\PluginInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Settings\SettingsService;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\Handlers\MultiContainerValidationHandler;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Logging\WC_LoggingHandler;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\WC_SettingsHandler;
use DWS_QRWC_Deps\DI\ContainerBuilder;
use function DWS_QRWC_Deps\DI\autowire;
use function DWS_QRWC_Deps\DI\factory;
use function DWS_QRWC_Deps\DI\get;

defined( 'ABSPATH' ) || exit;

return array_merge(
	// Foundations
	array(
		PluginInterface::class => get( Plugin::class ),
		LoggingService::class  => factory(
			function( PluginInterface $plugin ) {
				$logging_handlers = array();
				$is_debug_active  = Request::has_debug( 'DWS_QRWC_DEBUG' );

				if ( class_exists( 'WC_Log_Levels' ) ) { // in case the WC plugin is not active
					$min_log_level  = $is_debug_active ? WC_Log_Levels::DEBUG : WC_Log_Levels::ERROR;

					$logging_handlers = array(
						new WC_LoggingHandler( 'framework', null, $min_log_level ),
						new WC_LoggingHandler( 'plugin', null, $min_log_level ),
					);
				}

				return new LoggingService( $plugin, $logging_handlers, $is_debug_active );
			}
		),
	),
	// Settings
	array(
		'settings-validation-handler'         => factory(
			function() {
				$config             = require_once __DIR__ . '/src/configs/settings.php';
				$defaults_container = ( new ContainerBuilder() )->addDefinitions( $config['defaults'] )->build();
				$options_container  = ( new ContainerBuilder() )->addDefinitions( $config['options'] )->build();

				return new MultiContainerValidationHandler( 'settings', $defaults_container, $options_container );
			}
		),
		'product-settings-validation-handler' => factory(
			function() {
				$config             = require_once __DIR__ . '/src/configs/product-settings.php';
				$defaults_container = ( new ContainerBuilder() )->addDefinitions( $config['defaults'] )->build();
				$options_container  = ( new ContainerBuilder() )->addDefinitions( $config['options'] )->build();

				return new MultiContainerValidationHandler( 'product-settings', $defaults_container, $options_container );
			}
		),

		SettingsService::class                => autowire( SettingsService::class )
			->method( 'register_handler', new WC_SettingsHandler() ),
		ValidationService::class              => autowire( ValidationService::class )
			->method( 'register_handler', get( 'settings-validation-handler' ) )
			->method( 'register_handler', get( 'product-settings-validation-handler' ) ),
	),
	// Plugin
	array(
		Plugin::class                                   => autowire( Plugin::class )
			->constructorParameter( 'plugin_slug', 'quote-requests-for-woocommerce' )
			->constructorParameter( 'plugin_file_path', dws_qrwc_path() ),

		Quotes::class                                   => autowire( Quotes::class )
			->constructorParameter( 'component_id', 'quotes' )
			->constructorParameter( 'component_name', 'Quotes' ),
		Quotes\Actions::class                           => autowire( Quotes\Actions::class )
			->constructorParameter( 'component_id', 'quote-actions' )
			->constructorParameter( 'component_name', 'Quote Actions' ),
		Quotes\Tracking::class                          => autowire( Quotes\Tracking::class )
			->constructorParameter( 'component_id', 'quote-tracking' )
			->constructorParameter( 'component_name', 'Quote Tracking' ),
		Quotes\PostType\ListTable::class                => autowire( Quotes\PostType\ListTable::class )
			->constructorParameter( 'component_id', 'quotes-cpt-list-table' )
			->constructorParameter( 'component_name', 'Quotes Post Type List Table' ),
		Quotes\PostType\MetaBoxes::class                => autowire( Quotes\PostType\MetaBoxes::class )
			->constructorParameter( 'component_id', 'quotes-cpt-meta-boxes' )
			->constructorParameter( 'component_name', 'Quotes Post Type MetaBoxes' ),

		Customers::class                                => autowire( Customers::class )
			->constructorParameter( 'component_id', 'customers' )
			->constructorParameter( 'component_name', 'Customers' ),
		CustomerRequests::class                         => autowire( CustomerRequests::class )
			->constructorParameter( 'component_id', 'customer-requests' )
			->constructorParameter( 'component_name', 'Customer Requests' ),
		Requests\PriceDisclaimer::class                 => autowire( Requests\PriceDisclaimer::class )
			->constructorParameter( 'component_id', 'requests-price-disclaimer' )
			->constructorParameter( 'component_name', 'Requests Price Disclaimer' ),

		RequestLists\AddToListButton::class             => autowire( RequestLists\AddToListButton::class )
			->constructorParameter( 'component_id', 'add-to-request-list-button' )
			->constructorParameter( 'component_name', 'Add-to-Request-List Button' ),

		RequestLists\CartList::class                    => autowire( RequestLists\CartList::class )
			->constructorParameter( 'component_id', 'cart-request-list' )
			->constructorParameter( 'component_name', 'WC Cart Request List' ),
		RequestLists\CartList\AddToCartList::class      => autowire( RequestLists\CartList\AddToCartList::class )
			->constructorParameter( 'component_id', 'add-to-cart-request-list' )
			->constructorParameter( 'component_name', 'Add-to-WC-Cart-Request-List' ),
		RequestLists\CartList\PriceDisclaimerCartList::class => autowire( RequestLists\CartList\PriceDisclaimerCartList::class )
			->constructorParameter( 'component_id', 'cart-request-list-price-disclaimer' )
			->constructorParameter( 'component_name', 'WC Cart Request List Price Disclaimer' ),
		RequestLists\CartList\CheckoutCartList::class   => autowire( RequestLists\CartList\CheckoutCartList::class )
			->constructorParameter( 'component_id', 'cart-request-list-checkout' )
			->constructorParameter( 'component_name', 'WC Cart Request List Checkout' ),

		Emails::class                                   => autowire( Emails::class )
			->constructorParameter( 'component_id', 'emails' )
			->constructorParameter( 'component_name', 'Emails' ),

		Account\Actions::class                          => autowire( Account\Actions::class )
			->constructorParameter( 'component_id', 'account-actions' )
			->constructorParameter( 'component_name', 'Account Actions' ),
		Account\Endpoints::class                        => autowire( Account\Endpoints::class )
			->constructorParameter( 'component_id', 'account-endpoints' )
			->constructorParameter( 'component_name', 'Account Endpoints' ),
		Account\Endpoints\QuotesList::class             => autowire( Account\Endpoints\QuotesList::class )
			->constructorParameter( 'component_id', 'account-quotes-list-endpoint' )
			->constructorParameter( 'component_name', 'Account Quotes List Endpoint' ),
		Account\Endpoints\ViewQuote::class              => autowire( Account\Endpoints\ViewQuote::class )
			->constructorParameter( 'component_id', 'account-view-quote-endpoint' )
			->constructorParameter( 'component_name', 'Account View Quote Endpoint' ),

		Integrations::class                             => autowire( Integrations::class )
			->constructorParameter( 'component_id', 'integrations' )
			->constructorParameter( 'component_name', 'Integrations' ),
		Integrations\Plugins\LinkedOrdersForWC::class   => autowire( Integrations\Plugins\LinkedOrdersForWC::class )
			->constructorParameter( 'component_id', 'linked-orders-for-wc-plugin-integration' )
			->constructorParameter( 'component_name', 'Linked Orders for WC Plugin Integration' ),

		Permissions::class                              => autowire( Permissions::class )
			->constructorParameter( 'component_id', 'permissions' )
			->constructorParameter( 'component_name', 'Permissions' ),

		Settings::class                                 => autowire( Settings::class )
			->constructorParameter( 'component_id', 'settings' )
			->constructorParameter( 'component_name', 'Settings' ),
		Settings\GeneralSettings::class                 => autowire( Settings\GeneralSettings::class )
			->constructorParameter( 'component_id', 'general-settings' )
			->constructorParameter( 'component_name', 'General Settings' ),
		Settings\RequestsSettings::class                => autowire( Settings\RequestsSettings::class )
			->constructorParameter( 'component_id', 'requests-settings' )
			->constructorParameter( 'component_name', 'Requests Settings' ),
		Settings\RequestMessagesSettings::class         => autowire( Settings\RequestMessagesSettings::class )
			->constructorParameter( 'component_id', 'request-messages-settings' )
			->constructorParameter( 'component_name', 'Request Messages Settings' ),
		Settings\RequestListsSettings::class            => autowire( Settings\RequestListsSettings::class )
			->constructorParameter( 'component_id', 'request-lists-settings' )
			->constructorParameter( 'component_name', 'Request Lists Settings' ),
		Settings\RequestListMessagesSettings::class     => autowire( Settings\RequestListMessagesSettings::class )
			->constructorParameter( 'component_id', 'request-list-messages-settings' )
			->constructorParameter( 'component_name', 'Request List Messages Settings' ),
		Settings\PluginSettings::class                  => autowire( Settings\PluginSettings::class )
			->constructorParameter( 'component_id', 'plugin-settings' )
			->constructorParameter( 'component_name', 'Plugin Settings' ),
		Settings\Integrations\LinkedOrdersForWCSettings::class => autowire( Settings\Integrations\LinkedOrdersForWCSettings::class )
			->constructorParameter( 'component_id', 'linked-orders-for-wc-integration-settings' )
			->constructorParameter( 'component_name', 'Linked Orders for WC Integration Settings' ),

		ProductSettings::class                          => autowire( ProductSettings::class )
			->constructorParameter( 'component_id', 'product-settings' )
			->constructorParameter( 'component_name', 'Product Settings' ),
		Settings\Products\GeneralProductSettings::class => autowire( Settings\Products\GeneralProductSettings::class )
			->constructorParameter( 'component_id', 'general-product-settings' )
			->constructorParameter( 'component_name', 'General Product Settings' ),
		Settings\Products\UserInterfaceProductSettings::class => autowire( Settings\Products\UserInterfaceProductSettings::class )
			->constructorParameter( 'component_id', 'ui-product-settings' )
			->constructorParameter( 'component_name', 'UI Product Settings' ),
	),
	// Plugin aliases
	array(
		'quotes'                                    => get( Quotes::class ),
		'quote-actions'                             => get( Quotes\Actions::class ),
		'quote-tracking'                            => get( Quotes\Tracking::class ),
		'quotes-cpt-list-table'                     => get( Quotes\PostType\ListTable::class ),
		'quotes-cpt-meta-boxes'                     => get( Quotes\PostType\MetaBoxes::class ),

		'customers'                                 => get( Customers::class ),
		'customer-requests'                         => get( CustomerRequests::class ),
		'requests-price-disclaimer'                 => get( Requests\PriceDisclaimer::class ),

		'add-to-request-list-button'                => get( RequestLists\AddToListButton::class ),

		'cart-request-list'                         => get( RequestLists\CartList::class ),
		'add-to-cart-request-list'                  => get( RequestLists\CartList\AddToCartList::class ),
		'cart-request-list-price-disclaimer'        => get( RequestLists\CartList\PriceDisclaimerCartList::class ),
		'cart-request-list-checkout'                => get( RequestLists\CartList\AddToCartList::class ),

		'emails'                                    => get( Emails::class ),

		'account-actions'                           => get( Account\Actions::class ),
		'account-endpoints'                         => get( Account\Endpoints::class ),
		'account-quotes-list-endpoint'              => get( Account\Endpoints\QuotesList::class ),
		'account-view-quote-endpoint'               => get( Account\Endpoints\ViewQuote::class ),

		'integrations'                              => get( Integrations::class ),
		'linked-orders-for-wc-plugin-integration'   => get( Integrations\Plugins\LinkedOrdersForWC::class ),

		'permissions'                               => get( Permissions::class ),

		'settings'                                  => get( Settings::class ),
		'general-settings'                          => get( Settings\GeneralSettings::class ),
		'requests-settings'                         => get( Settings\RequestsSettings::class ),
		'request-messages-settings'                 => get( Settings\RequestMessagesSettings::class ),
		'request-lists-settings'                    => get( Settings\RequestListsSettings::class ),
		'request-list-messages-settings'            => get( Settings\RequestListMessagesSettings::class ),
		'plugin-settings'                           => get( Settings\PluginSettings::class ),
		'linked-orders-for-wc-integration-settings' => get( Settings\Integrations\LinkedOrdersForWCSettings::class ),

		'product-settings'                          => get( ProductSettings::class ),
		'general-product-settings'                  => get( Settings\Products\GeneralProductSettings::class ),
		'ui-product-settings'                       => get( Settings\Products\UserInterfaceProductSettings::class ),
	)
);
