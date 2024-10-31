import * as jQuery from "jquery";
import { Window } from "../../interfaces";
declare let window: Window;

/**
 * @since   1.0.0
 * @version 1.0.0
 */
export class QRWC_Requests_Product_Settings {
    // region FIELDS AND CONSTANTS

    /**
     * @since   1.0.0
     * @version 1.0.0
     *
     * @private
     */
    private static instance: QRWC_Requests_Product_Settings;

    /**
     * @since   1.0.0
     * @version 1.0.0
     *
     * @private
     */
    protected $panel: JQuery;

    // endregion

    // region CONSTRUCTORS

    /**
     * @since   1.0.0
     * @version 1.0.0
     *
     * @private
     */
    private constructor() {
        this.$panel = jQuery( '#dws_quote_requests_product_data' );

        this.$panel.on( 'change', 'select#dws_qrwc_general_is_valid_product', this.show_or_hide_all_fields );
        window.wp.hooks.doAction( 'dws_qrwc.requests_product_settings.register_conditional_logic_triggers', this.$panel, this );
    }

    // endregion

    // region GETTERS

    /**
     * @since   1.0.0
     * @version 1.0.0
     */
    public static get_instance(): QRWC_Requests_Product_Settings {
        if ( ! QRWC_Requests_Product_Settings.instance ) {
            QRWC_Requests_Product_Settings.instance = new QRWC_Requests_Product_Settings();
        }

        return QRWC_Requests_Product_Settings.instance;
    }

    /**
     * @since   1.0.0
     * @version 1.0.0
     */
    public static get_panel(): JQuery {
        return QRWC_Requests_Product_Settings.get_instance().$panel;
    }

    /**
     * @since   1.0.0
     * @version 1.0.0
     */
    public get_panel(): JQuery {
        return this.$panel;
    }

    // endregion

    // region METHODS

    /**
     * @since   1.0.0
     * @version 1.0.0
     */
    public show_or_hide_all_fields() {
        const is_valid_requests_product = QRWC_Requests_Product_Settings.get_panel().find( 'select#dws_qrwc_general_is_valid_product' ).val();

        if ( 'no' === is_valid_requests_product ) {
            QRWC_Requests_Product_Settings.get_panel().find( 'p.form-field:not(.dws_qrwc_general_is_valid_product_field)' ).hide();
            window.wp.hooks.doAction( 'dws_qrwc.requests_product_settings.hide_all_fields' );
        } else {
            QRWC_Requests_Product_Settings.get_panel().find( 'p.form-field:not(.dws_qrwc_general_is_valid_product_field)' ).show();
            window.wp.hooks.doAction( 'dws_qrwc.requests_product_settings.show_or_hide_fields', QRWC_Requests_Product_Settings.get_instance() );
        }
    }

    // endregion
}