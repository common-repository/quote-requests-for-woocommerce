<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\Actions\UninstallableInterface ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Users ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the registration of the quotes CPT and the higher-level functionalities of a quote.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Quotes extends AbstractPluginFunctionality implements  UninstallableInterface 
{
    // region TRAITS
    use  SetupHooksTrait ;
    // endregion
    // region INHERITED FUNCTIONS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_di_container_children() : array
    {
        $children = array(
            Quotes\Actions::class,
            Quotes\Tracking::class,
            Quotes\PostType\ListTable::class,
            Quotes\PostType\MetaBoxes::class
        );
        return $children;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_hooks( HooksService $hooks_service ) : void
    {
        $hooks_service->add_action(
            'woocommerce_after_register_post_type',
            $this,
            'register_order_type',
            6
        );
        $hooks_service->add_filter( 'woocommerce_data_stores', $this, 'register_data_store' );
        $hooks_service->add_filter( 'woocommerce_register_shop_order_post_statuses', $this, 'register_post_statuses' );
        $hooks_service->add_filter(
            'woocommerce_payment_complete_reduce_order_stock',
            $this,
            'maybe_prevent_reduce_quote_stock',
            999,
            2
        );
        $hooks_service->add_filter(
            'woocommerce_payment_complete_order_status',
            $this,
            'maybe_filter_payment_complete_status',
            999,
            3
        );
        $hooks_service->add_filter(
            'woocommerce_new_order_note_data',
            $this,
            'filter_new_order_note_data',
            999,
            2
        );
        $hooks_service->add_filter(
            'gettext_woocommerce',
            $this,
            'maybe_filter_translations',
            99,
            2
        );
    }
    
    // endregion
    // region INSTALLATION
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function uninstall() : ?UninstallFailureException
    {
        $quote_posts = \get_posts( array(
            'post_type'   => 'dws_shop_quote',
            'numberposts' => -1,
            'fields'      => 'ids',
        ) );
        foreach ( $quote_posts as $post_id ) {
            \wp_delete_post( $post_id, true );
        }
        return null;
    }
    
    // endregion
    // region HOOKS
    /**
     * Registers the quotes order type with WooCommerce.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_order_type() : void
    {
        \wc_register_order_type( 'dws_shop_quote', \apply_filters( $this->get_hook_tag( 'register_order_type_args' ), array(
            'labels'                           => array(
            'name'               => __( 'Quotes', 'quote-requests-for-woocommerce' ),
            'singular_name'      => __( 'Quote', 'quote-requests-for-woocommerce' ),
            'add_new'            => _x( 'Add Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'add_new_item'       => _x( 'Add New Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'edit'               => _x( 'Edit', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'edit_item'          => _x( 'Edit Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'new_item'           => _x( 'New Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'view'               => _x( 'View Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'view_item'          => _x( 'View Quote', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'search_items'       => __( 'Search Quotes', 'quote-requests-for-woocommerce' ),
            'not_found'          => __( 'No Quotes found', 'quote-requests-for-woocommerce' ),
            'not_found_in_trash' => _x( 'No Quotes found in trash', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'parent'             => _x( 'Parent Quotes', 'custom post type argument', 'quote-requests-for-woocommerce' ),
            'menu_name'          => __( 'Quotes', 'quote-requests-for-woocommerce' ),
        ),
            'description'                      => __( 'This is where quotes are stored.', 'quote-requests-for-woocommerce' ),
            'public'                           => false,
            'show_ui'                          => true,
            'capability_type'                  => 'shop_order',
            'map_meta_cap'                     => true,
            'publicly_queryable'               => false,
            'exclude_from_search'              => true,
            'show_in_menu'                     => ( Users::has_capabilities( 'edit_others_shop_orders' ) ? 'woocommerce' : true ),
            'hierarchical'                     => false,
            'show_in_nav_menus'                => false,
            'rewrite'                          => false,
            'query_var'                        => false,
            'supports'                         => array( 'title', 'comments', 'custom-fields' ),
            'has_archive'                      => false,
            'exclude_from_orders_screen'       => true,
            'add_order_meta_boxes'             => true,
            'exclude_from_order_count'         => true,
            'exclude_from_order_views'         => true,
            'exclude_from_order_webhooks'      => true,
            'exclude_from_order_reports'       => true,
            'exclude_from_order_sales_reports' => true,
            'class_name'                       => \DWS_Quote::class,
        ) ) );
    }
    
    /**
     * Registers data stores with WooCommerce.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   array   $data_stores    Data stores already registered.
     *
     * @return  array
     */
    public function register_data_store( array $data_stores ) : array
    {
        $data_stores['dws-quote'] = \DWS_Quote_Data_Store_CPT::class;
        return $data_stores;
    }
    
    /**
     * Register our custom post statuses, used for quote status.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   array   $order_statuses     Order statuses registered already.
     *
     * @return  array
     */
    public function register_post_statuses( array $order_statuses ) : array
    {
        $quote_statuses = dws_qrwc_get_quote_statuses();
        $registered_statuses = \apply_filters( $this->get_hook_tag( 'register_order_post_statuses' ), \array_combine( \array_keys( $quote_statuses ), \array_map( function ( string $slug, string $label ) {
            switch ( $slug ) {
                case 'wc-quote-request':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'New request <span class="count">(%s)</span>',
                        'New request <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                case 'wc-quote-waiting':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'Waiting on customer <span class="count">(%s)</span>',
                        'Waiting on customer <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                case 'wc-quote-expired':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'Expired <span class="count">(%s)</span>',
                        'Expired <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                case 'wc-quote-rejected':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'Rejected by customer <span class="count">(%s)</span>',
                        'Rejected by customer <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                case 'wc-quote-accepted':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'Accepted by customer <span class="count">(%s)</span>',
                        'Accepted by customer <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                case 'wc-quote-cancelled':
                    /* translators: %s: number of quotes */
                    $label_count = _nx_noop(
                        'Cancelled <span class="count">(%s)</span>',
                        'Cancelled <span class="count">(%s)</span>',
                        'post status label including post count',
                        'quote-requests-for-woocommerce'
                    );
                    break;
                default:
                    $label_count = \apply_filters( $this->get_hook_tag( 'status_label_count' ), '', $slug );
            }
            return array(
                'label'                     => $label,
                'public'                    => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => $label_count,
            );
        }, \array_keys( $quote_statuses ), $quote_statuses ) ) );
        return \array_merge( $order_statuses, $registered_statuses );
    }
    
    /**
     * Prevents stock from being reduced on quote requests.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $reduce_stock   Whether to reduce stock or not.
     * @param   int     $order_id       The order ID.
     *
     * @return  bool
     */
    public function maybe_prevent_reduce_quote_stock( bool $reduce_stock, int $order_id ) : bool
    {
        if ( true === dws_qrwc_is_quote( $order_id ) ) {
            $reduce_stock = false;
        }
        return $reduce_stock;
    }
    
    /**
     * By default, WC tries to set paid orders in the status 'processing' but that is false for quote requests since
     * they should be set into the status 'quote-request'.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param   string      $status     Status to set the order/quote to.
     * @param   int         $order_id   The ID of the order/quote.
     * @param   \WC_Order   $order      The order/quote object.
     *
     * @return  string
     */
    public function maybe_filter_payment_complete_status( string $status, int $order_id, \WC_Order $order ) : string
    {
        if ( true === dws_qrwc_is_quote( $order ) ) {
            $status = 'quote-request';
        }
        return $status;
    }
    
    /**
     * Replace status slugs with status labels in quote notes.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   array   $comment_data   The data passed on for inserting the comment/note.
     * @param   array   $args           WC arguments.
     *
     * @return  array
     */
    public function filter_new_order_note_data( array $comment_data, array $args ) : array
    {
        if ( dws_qrwc_is_quote( $args['order_id'] ?? 0 ) ) {
            $comment_data['comment_content'] = Strings::replace_placeholders( $comment_data['comment_content'], dws_qrwc_get_quote_statuses( false ) );
        }
        return $comment_data;
    }
    
    /**
     * Replace certain WC translations on the order-received endpoint for quotes.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   string  $translation    Translated text.
     * @param   string  $text           Original text.
     *
     * @return  string
     */
    public function maybe_filter_translations( string $translation, string $text ) : string
    {
        
        if ( true === \is_wc_endpoint_url( 'order-received' ) ) {
            $quote_id = \wc_get_order_id_by_order_key( Strings::maybe_cast_input( INPUT_GET, 'key', '' ) );
            if ( true === dws_qrwc_is_quote( $quote_id ) ) {
                $translation = dws_qrwc_get_wc_order_received_translation( $text ) ?? $translation;
            }
        }
        
        return $translation;
    }

}