import * as jQuery from "jquery";
import { QRWC_Requests_Product_Settings } from "./models/QRWC_Requests_Product_Settings";
import { Window } from "../interfaces";
declare let window: Window;

jQuery( function () {
    // Instantiate the quote requests product settings singleton class.
    window.dws_qrwc_requests_product_settings = QRWC_Requests_Product_Settings.get_instance();
    window.dws_qrwc_requests_product_settings.show_or_hide_all_fields();
} );
