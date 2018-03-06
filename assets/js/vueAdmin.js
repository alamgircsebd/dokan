dokanWebpack([0],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'App'
});

/***/ }),
/* 5 */,
/* 6 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_wp_list_table__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_Switches_vue__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_Api__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_Api___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__utils_Api__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_wp_list_table_dist_vue_wp_list_table_css__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_wp_list_table_dist_vue_wp_list_table_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_vue_wp_list_table_dist_vue_wp_list_table_css__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//






/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'Home',

    components: {
        ListTable: __WEBPACK_IMPORTED_MODULE_0_vue_wp_list_table__["default"],
        Switches: __WEBPACK_IMPORTED_MODULE_1__components_Switches_vue__["a" /* default */]
    },

    data: function data() {
        return {
            showCb: true,

            sortBy: 'title',
            sortOrder: 'asc',

            totalItems: 0,
            perPage: 10,
            totalPages: 1,
            currentPage: 1,
            loading: false,

            columns: {
                'store_name': {
                    label: 'Store',
                    sortable: true
                },
                'email': {
                    label: 'E-mail'
                },
                'phone': {
                    label: 'Phone'
                },
                'registered': {
                    label: 'Registered',
                    sortable: true
                },
                'enabled': {
                    label: 'Status'
                }
            },
            actionColumn: 'title',
            actions: [{
                key: 'edit',
                label: 'Edit'
            }, {
                key: 'trash',
                label: 'Delete'
            }],
            bulkActions: [{
                key: 'trash',
                label: 'Move to Trash'
            }],
            vendors: []
        };
    },
    created: function created() {

        this.fetchVendors();
    },


    methods: {
        fetchVendors: function fetchVendors() {
            var time = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 100;


            var self = this;

            self.loading = true;

            dokan.api.get('/stores?status=all').done(function (response, status, xhr) {
                console.log(response, status, xhr);
                self.vendors = response;
                self.totalItems = parseInt(xhr.getResponseHeader('X-WP-Total'));
                self.totalPages = parseInt(xhr.getResponseHeader('X-WP-TotalPages'));
                self.loading = false;
            });
        },
        onActionClick: function onActionClick(action, row) {
            if ('trash' === action) {
                if (confirm('Are you sure to delete?')) {
                    alert('deleted: ' + row.title);
                }
            }
        },
        onSwitch: function onSwitch(status, vendor_id) {
            console.log(status, vendor_id);

            var message = status === false ? 'The vendor has been disabled.' : 'Selling has been enabled';

            this.$notify({
                title: 'Success!',
                type: 'success',
                text: message
            });
        },
        goToPage: function goToPage(page) {
            console.log('Going to page: ' + page);
            this.currentPage = page;
            this.fetchVendors(1000);
        },
        onBulkAction: function onBulkAction(action, items) {
            console.log(action, items);
            alert(action + ': ' + items.join(', '));
        },
        sortCallback: function sortCallback(column, order) {
            this.sortBy = column;
            this.sortOrder = order;

            this.fetchVendors(1000);
        }
    }
});

/***/ }),
/* 7 */,
/* 8 */,
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'Switches',

    props: {
        enabled: {
            type: Boolean, // String, Number, Boolean, Function, Object, Array
            required: true,
            default: false
        },
        value: {
            type: [String, Number]
        }
    },

    data: function data() {
        return {};
    },


    methods: {
        trigger: function trigger(e) {
            this.$emit('input', e.target.checked, e.target.value);
        }
    }
});

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Dokan_API = function () {
    function Dokan_API() {
        _classCallCheck(this, Dokan_API);
    }

    _createClass(Dokan_API, [{
        key: 'endpoint',
        value: function endpoint() {
            return window.wpApiSettings.root + 'dokan/v1';
        }
    }, {
        key: 'headers',
        value: function headers() {
            return {};
        }
    }, {
        key: 'get',
        value: function get(path) {
            var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            return this.ajax(path, 'GET', this.headers(), data);
        }
    }, {
        key: 'post',
        value: function post(path) {
            var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            return this.ajax(path, 'POST', this.headers(), data);
        }
    }, {
        key: 'put',
        value: function put(path) {
            var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            data._method = 'PUT';
            return this.post(path, data);
        }
    }, {
        key: 'delete',
        value: function _delete(path) {
            var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

            return this.ajax(path, 'DELETE', this.headers(), data);
        }

        // jQuery ajax wrapper

    }, {
        key: 'ajax',
        value: function ajax(path, method, headers, data) {

            return $.ajax({
                url: this.endpoint() + path,
                beforeSend: function beforeSend(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
                },
                type: method,
                data: data
            });
        }
    }]);

    return Dokan_API;
}();

exports.default = Dokan_API;

/***/ }),
/* 11 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_content_loading__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_content_loading___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_vue_content_loading__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'VendorSingle',

    components: {
        VclFacebook: __WEBPACK_IMPORTED_MODULE_0_vue_content_loading__["VclFacebook"],
        VclTwitch: __WEBPACK_IMPORTED_MODULE_0_vue_content_loading__["VclTwitch"]
    },

    data: function data() {
        return {
            store: {}
        };
    },


    computed: {
        id: function id() {
            return this.$route.params.id;
        }
    },

    created: function created() {
        this.fetch();
    },


    methods: {
        fetch: function fetch() {
            var _this = this;

            dokan.api.get('/stores/' + this.id).done(function (response) {
                return _this.store = response;
            });
        }
    }
});

/***/ }),
/* 12 */,
/* 13 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["a"] = ({

    name: 'Settings',

    data: function data() {
        return {};
    }
});

/***/ }),
/* 14 */,
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(1);

var _vue2 = _interopRequireDefault(_vue);

var _App = __webpack_require__(18);

var _App2 = _interopRequireDefault(_App);

var _router = __webpack_require__(22);

var _router2 = _interopRequireDefault(_router);

var _adminMenuFix = __webpack_require__(42);

var _adminMenuFix2 = _interopRequireDefault(_adminMenuFix);

var _Api = __webpack_require__(10);

var _Api2 = _interopRequireDefault(_Api);

var _vueNotification = __webpack_require__(14);

var _vueNotification2 = _interopRequireDefault(_vueNotification);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vueNotification2.default);

window.dokan.api = new _Api2.default();

_vue2.default.config.productionTip = false;

/* eslint-disable no-new */
new _vue2.default({
    el: '#vue-admin-app',
    router: _router2.default,
    render: function render(h) {
        return h(_App2.default);
    }
});

// fix the admin menu for the slug "vue-app"
(0, _adminMenuFix2.default)('vue-app');

/***/ }),
/* 16 */,
/* 17 */,
/* 18 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__ = __webpack_require__(4);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3a030f38_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__ = __webpack_require__(21);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(19)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_App_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3a030f38_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_App_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "src/admin/App.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-3a030f38", Component.options)
  } else {
    hotAPI.reload("data-v-3a030f38", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 19 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 20 */,
/* 21 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { attrs: { id: "vue-admin-app" } },
    [
      _c("router-view"),
      _vm._v(" "),
      _c("notifications", { attrs: { position: "bottom right" } })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-3a030f38", esExports)
  }
}

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _vue = __webpack_require__(1);

var _vue2 = _interopRequireDefault(_vue);

var _vueRouter = __webpack_require__(5);

var _vueRouter2 = _interopRequireDefault(_vueRouter);

var _Vendors = __webpack_require__(23);

var _Vendors2 = _interopRequireDefault(_Vendors);

var _VendorSingle = __webpack_require__(36);

var _VendorSingle2 = _interopRequireDefault(_VendorSingle);

var _Settings = __webpack_require__(39);

var _Settings2 = _interopRequireDefault(_Settings);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vueRouter2.default);

exports.default = new _vueRouter2.default({
    routes: [{
        path: '/',
        name: 'Vendors',
        component: _Vendors2.default
    }, {
        path: '/vendors/:id',
        name: 'VendorSingle',
        component: _VendorSingle2.default
    }, {
        path: '/settings',
        name: 'Settings',
        component: _Settings2.default
    }]
});

/***/ }),
/* 23 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Vendors_vue__ = __webpack_require__(6);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7a477aab_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Vendors_vue__ = __webpack_require__(35);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(24)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Vendors_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7a477aab_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Vendors_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "src/admin/components/Vendors.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-7a477aab", Component.options)
  } else {
    hotAPI.reload("data-v-7a477aab", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 24 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 25 */,
/* 26 */,
/* 27 */,
/* 28 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Switches_vue__ = __webpack_require__(9);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_defeb3dc_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Switches_vue__ = __webpack_require__(30);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(29)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Switches_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_defeb3dc_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Switches_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "src/components/Switches.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-defeb3dc", Component.options)
  } else {
    hotAPI.reload("data-v-defeb3dc", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 29 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 30 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("label", { staticClass: "switch tips" }, [
    _c("input", {
      staticClass: "toogle-checkbox",
      attrs: { type: "checkbox" },
      domProps: { checked: _vm.enabled, value: _vm.value },
      on: { change: _vm.trigger }
    }),
    _vm._v(" "),
    _c("span", { staticClass: "slider round" })
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-defeb3dc", esExports)
  }
}

/***/ }),
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */,
/* 35 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "home" },
    [
      _c("h1", { staticClass: "wp-heading-inline" }, [_vm._v("Vendors")]),
      _vm._v(" "),
      _c("a", { staticClass: "page-title-action", attrs: { href: "#" } }, [
        _vm._v("Add New")
      ]),
      _vm._v(" "),
      _c("list-table", {
        attrs: {
          columns: _vm.columns,
          loading: _vm.loading,
          rows: _vm.vendors,
          actions: _vm.actions,
          "show-cb": _vm.showCb,
          "total-items": _vm.totalItems,
          "bulk-actions": _vm.bulkActions,
          "total-pages": _vm.totalPages,
          "per-page": _vm.perPage,
          "current-page": _vm.currentPage,
          "action-column": _vm.actionColumn,
          "sort-by": _vm.sortBy,
          "sort-order": _vm.sortOrder
        },
        on: {
          sort: _vm.sortCallback,
          pagination: _vm.goToPage,
          "action:click": _vm.onActionClick,
          "bulk:click": _vm.onBulkAction
        },
        scopedSlots: _vm._u([
          {
            key: "store_name",
            fn: function(data) {
              return [
                _c("img", {
                  attrs: {
                    src: data.row.gravatar,
                    alt: data.row.store_name,
                    width: "50"
                  }
                }),
                _vm._v(" "),
                _c(
                  "strong",
                  [
                    _c(
                      "router-link",
                      { attrs: { to: "/vendors/" + data.row.id } },
                      [_vm._v(_vm._s(data.row.store_name))]
                    )
                  ],
                  1
                )
              ]
            }
          },
          {
            key: "email",
            fn: function(data) {
              return [
                _c("a", { attrs: { href: "mailto:" + data.row.email } }, [
                  _vm._v(_vm._s(data.row.email))
                ])
              ]
            }
          },
          {
            key: "enabled",
            fn: function(data) {
              return [
                _c("switches", {
                  attrs: { enabled: data.row.enabled, value: data.row.id },
                  on: { input: _vm.onSwitch }
                })
              ]
            }
          }
        ])
      })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-7a477aab", esExports)
  }
}

/***/ }),
/* 36 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_VendorSingle_vue__ = __webpack_require__(11);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_849fac40_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_VendorSingle_vue__ = __webpack_require__(38);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(37)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_VendorSingle_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_849fac40_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_VendorSingle_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "src/admin/components/VendorSingle.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-849fac40", Component.options)
  } else {
    hotAPI.reload("data-v-849fac40", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 37 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 38 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "dokan-vendor-single" },
    [
      _c(
        "div",
        { staticStyle: { "margin-bottom": "10px" } },
        [
          _c("router-link", { staticClass: "button", attrs: { to: "/" } }, [
            _vm._v("← Back to listing")
          ])
        ],
        1
      ),
      _vm._v(" "),
      _vm.store.id
        ? _c("div", { staticClass: "vendor-header" }, [
            _c("div", { staticClass: "profile-banner" }, [
              _c("div", { staticClass: "banner-wrap" }, [
                _vm.store.banner
                  ? _c("img", {
                      attrs: {
                        src: _vm.store.banner,
                        alt: _vm.store.store_name
                      }
                    })
                  : _vm._e()
              ])
            ]),
            _vm._v(" "),
            _c("div", { staticClass: "profile-info" }, [
              _c("div", { staticClass: "profile-icon" }, [
                _c("div", { staticClass: "profile-icon-wrap" }, [
                  _c("img", {
                    attrs: {
                      src: _vm.store.gravatar,
                      alt: _vm.store.store_name
                    }
                  })
                ])
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "store-info" }, [
                _c("h2", { staticClass: "store-name" }, [
                  _vm._v(_vm._s(_vm.store.store_name))
                ]),
                _vm._v(" "),
                _c("ul", { staticClass: "store-info" }, [
                  _c("li", { staticClass: "address" }, [
                    _c("span", { staticClass: "dashicons dashicons-location" }),
                    _vm._v(" "),
                    _c("span", { staticClass: "street_1" }, [
                      _vm._v(_vm._s(_vm.store.address.street_1) + ", ")
                    ]),
                    _vm._v(" "),
                    _c("span", { staticClass: "city" }, [
                      _vm._v(_vm._s(_vm.store.address.city) + ", ")
                    ]),
                    _vm._v(" "),
                    _c("span", { staticClass: "state-zip" }, [
                      _vm._v(
                        _vm._s(_vm.store.address.state) +
                          " " +
                          _vm._s(_vm.store.address.zip)
                      )
                    ])
                  ]),
                  _vm._v(" "),
                  _c("li", { staticClass: "phone" }, [
                    _c("span", { staticClass: "dashicons dashicons-phone" }),
                    _vm._v(
                      "\n                        " +
                        _vm._s(_vm.store.phone) +
                        "\n                    "
                    )
                  ]),
                  _vm._v(" "),
                  _c("li", { staticClass: "rating" }, [
                    _c("span", {
                      staticClass: "dashicons dashicons-star-filled"
                    }),
                    _vm._v(
                      "\n                        " +
                        _vm._s(_vm.store.rating.rating) +
                        "\n                    "
                    )
                  ])
                ])
              ])
            ])
          ])
        : _c("vcl-twitch", { attrs: { height: "300", primary: "#ffffff" } })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-849fac40", esExports)
  }
}

/***/ }),
/* 39 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Settings_vue__ = __webpack_require__(13);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_61e400d3_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Settings_vue__ = __webpack_require__(41);
var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(40)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-61e400d3"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_Settings_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_61e400d3_hasScoped_true_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_Settings_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "src/admin/components/Settings.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-61e400d3", Component.options)
  } else {
    hotAPI.reload("data-v-61e400d3", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),
/* 40 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 41 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "app-settings" }, [
    _vm._v("\n    The Settings Page\n")
  ])
}
var staticRenderFns = []
render._withStripped = true
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-61e400d3", esExports)
  }
}

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * As we are using hash based navigation, hack fix
 * to highlight the current selected menu
 *
 * Requires jQuery
 */
function menuFix(slug) {
    var $ = jQuery;

    var menuRoot = $('#toplevel_page_' + slug);
    var currentUrl = window.location.href;
    var currentPath = currentUrl.substr(currentUrl.indexOf('admin.php'));

    menuRoot.on('click', 'a', function () {
        var self = $(this);

        $('ul.wp-submenu li', menuRoot).removeClass('current');

        if (self.hasClass('wp-has-submenu')) {
            $('li.wp-first-item', menuRoot).addClass('current');
        } else {
            self.parents('li').addClass('current');
        }
    });

    $('ul.wp-submenu a', menuRoot).each(function (index, el) {
        if ($(el).attr('href') === currentPath) {
            $(el).parent().addClass('current');
            return;
        }
    });
}

exports.default = menuFix;

/***/ })
],[15]);