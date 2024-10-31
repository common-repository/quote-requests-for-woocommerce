/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
jQuery(function ($) {
  $('.dws-qrwc-date-input [name$="_hour"], .dws-qrwc-date-input [name$="_minute"]').on('change', function () {
    $('#' + $(this).attr('name').replace('_hour', '').replace('_minute', '')).trigger('change');
  });
});
/******/ })()
;
//# sourceMappingURL=quotes-metaboxes.js.map