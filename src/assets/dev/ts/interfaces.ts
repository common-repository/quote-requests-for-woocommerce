import { Hooks } from '@wordpress/hooks/build-types';
import { QRWC_Requests_Product_Settings } from "./admin/models/QRWC_Requests_Product_Settings";

/**
 * @since   1.0.0
 * @version 1.0.0
 */
export interface Window {
    wp: {
        hooks: Hooks;
        [x: string]: any;
    };
    dws_qrwc_requests_product_settings: QRWC_Requests_Product_Settings;
    [x: string]: any;
}
