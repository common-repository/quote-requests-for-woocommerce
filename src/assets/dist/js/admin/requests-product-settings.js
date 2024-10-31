/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

;// CONCATENATED MODULE: ./src/assets/dev/ts/admin/models/QRWC_Requests_Product_Settings.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

var QRWC_Requests_Product_Settings = function () {
  function QRWC_Requests_Product_Settings() {
    _classCallCheck(this, QRWC_Requests_Product_Settings);

    this.$panel = jQuery('#dws_quote_requests_product_data');
    this.$panel.on('change', 'select#dws_qrwc_general_is_valid_product', this.show_or_hide_all_fields);
    window.wp.hooks.doAction('dws_qrwc.requests_product_settings.register_conditional_logic_triggers', this.$panel, this);
  }

  _createClass(QRWC_Requests_Product_Settings, [{
    key: "get_panel",
    value: function get_panel() {
      return this.$panel;
    }
  }, {
    key: "show_or_hide_all_fields",
    value: function show_or_hide_all_fields() {
      var is_valid_requests_product = QRWC_Requests_Product_Settings.get_panel().find('select#dws_qrwc_general_is_valid_product').val();

      if ('no' === is_valid_requests_product) {
        QRWC_Requests_Product_Settings.get_panel().find('p.form-field:not(.dws_qrwc_general_is_valid_product_field)').hide();
        window.wp.hooks.doAction('dws_qrwc.requests_product_settings.hide_all_fields');
      } else {
        QRWC_Requests_Product_Settings.get_panel().find('p.form-field:not(.dws_qrwc_general_is_valid_product_field)').show();
        window.wp.hooks.doAction('dws_qrwc.requests_product_settings.show_or_hide_fields', QRWC_Requests_Product_Settings.get_instance());
      }
    }
  }], [{
    key: "get_instance",
    value: function get_instance() {
      if (!QRWC_Requests_Product_Settings.instance) {
        QRWC_Requests_Product_Settings.instance = new QRWC_Requests_Product_Settings();
      }

      return QRWC_Requests_Product_Settings.instance;
    }
  }, {
    key: "get_panel",
    value: function get_panel() {
      return QRWC_Requests_Product_Settings.get_instance().$panel;
    }
  }]);

  return QRWC_Requests_Product_Settings;
}();
;// CONCATENATED MODULE: ./src/assets/dev/ts/admin/requests-product-settings.ts

jQuery(function () {
  window.dws_qrwc_requests_product_settings = QRWC_Requests_Product_Settings.get_instance();
  window.dws_qrwc_requests_product_settings.show_or_hide_all_fields();
});
/******/ })()
;
//# sourceMappingURL=requests-product-settings.js.map