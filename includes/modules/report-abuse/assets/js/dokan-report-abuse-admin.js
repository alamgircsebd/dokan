/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 68);
/******/ })
/************************************************************************/
/******/ ({

/***/ 0:
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file.
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate

    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ 16:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
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

var ListTable = dokan_get_lib('ListTable');
var Modal = dokan_get_lib('Modal');

/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'AbuseReports',

    components: {
        ListTable: ListTable,
        Modal: Modal
    },

    data: function data() {
        return {
            columns: {
                reason: {
                    label: this.__('Reason', 'dokan')
                },

                product: {
                    label: this.__('Product', 'dokan')
                },

                vendor: {
                    label: this.__('Vendor', 'dokan')
                },

                reported_by: {
                    label: this.__('Reported by', 'dokan')
                },

                reported_at: {
                    label: this.__('Reported at', 'dokan')
                }
            },
            loading: false,
            reports: [],
            actions: [],
            bulkActions: [],
            totalItems: 0,
            totalPages: 1,
            perPage: 10,
            showModal: false,
            report: {}
        };
    },


    computed: {
        currentPage: function currentPage() {
            var page = this.$route.query.page || 1;
            return parseInt(page);
        }
    },

    created: function created() {
        this.fetchReports();
    },


    watch: {
        '$route.query.page': function $routeQueryPage() {
            this.fetchReports();
        }
    },

    methods: {
        fetchReports: function fetchReports() {
            var _this = this;

            var self = this;

            self.loading = true;

            dokan.api.get('/abuse-reports', {
                page: this.currentPage
            }).done(function (response, status, xhr) {
                self.reports = response;
                self.loading = false;

                _this.updatePagination(xhr);
            });
        },
        updatePagination: function updatePagination(xhr) {
            this.totalPages = parseInt(xhr.getResponseHeader('X-Dokan-AbuseReports-TotalPages'));
            this.totalItems = parseInt(xhr.getResponseHeader('X-Dokan-AbuseReports-Total'));
        },
        moment: function (_moment) {
            function moment(_x) {
                return _moment.apply(this, arguments);
            }

            moment.toString = function () {
                return _moment.toString();
            };

            return moment;
        }(function (date) {
            return moment(date);
        }),
        goToPage: function goToPage(page) {
            this.$router.push({
                name: 'AbuseReports',
                query: {
                    page: page
                }
            });
        },
        showReport: function showReport(report) {
            this.report = report;
            this.showModal = true;
        },
        hideReport: function hideReport() {
            this.report = {};
            this.showModal = false;
        }
    }
});

/***/ }),

/***/ 68:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _AbuseReports = __webpack_require__(69);

var _AbuseReports2 = _interopRequireDefault(_AbuseReports);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

dokan_add_route(_AbuseReports2.default);

/***/ }),

/***/ 69:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AbuseReports_vue__ = __webpack_require__(16);
/* empty harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_23efc86a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AbuseReports_vue__ = __webpack_require__(70);
var disposed = false
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vue_loader_lib_selector_type_script_index_0_AbuseReports_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_1__node_modules_vue_loader_lib_template_compiler_index_id_data_v_23efc86a_hasScoped_false_buble_transforms_node_modules_vue_loader_lib_selector_type_template_index_0_AbuseReports_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "includes/modules/report-abuse/src/js/admin/pages/AbuseReports.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-23efc86a", Component.options)
  } else {
    hotAPI.reload("data-v-23efc86a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

/* harmony default export */ __webpack_exports__["default"] = (Component.exports);


/***/ }),

/***/ 70:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _c("h1", { staticClass: "wp-heading-inline" }, [
        _vm._v(_vm._s(_vm.__("Abuse Reports", "dokan")))
      ]),
      _vm._v(" "),
      _c("hr", { staticClass: "wp-header-end" }),
      _vm._v(" "),
      _c("list-table", {
        attrs: {
          columns: _vm.columns,
          loading: _vm.loading,
          rows: _vm.reports,
          actions: _vm.actions,
          "bulk-actions": _vm.bulkActions,
          "show-cb": false,
          "total-items": _vm.totalItems,
          "total-pages": _vm.totalPages,
          "per-page": _vm.perPage,
          "current-page": _vm.currentPage
        },
        on: { pagination: _vm.goToPage },
        scopedSlots: _vm._u([
          {
            key: "reason",
            fn: function(ref) {
              var row = ref.row
              return [
                _c("strong", [
                  _c(
                    "a",
                    {
                      attrs: { href: "#view-report" },
                      on: {
                        click: function($event) {
                          $event.preventDefault()
                          _vm.showReport(row)
                        }
                      }
                    },
                    [_vm._v(_vm._s(row.reason))]
                  )
                ])
              ]
            }
          },
          {
            key: "product",
            fn: function(ref) {
              var row = ref.row
              return [
                _c("a", { attrs: { href: row.product.admin_url } }, [
                  _vm._v(_vm._s(row.product.title))
                ])
              ]
            }
          },
          {
            key: "vendor",
            fn: function(ref) {
              var row = ref.row
              return [
                _c(
                  "router-link",
                  { attrs: { to: "/vendors/" + row.vendor.id } },
                  [
                    _vm._v(
                      "\n                " +
                        _vm._s(
                          row.vendor.name
                            ? row.vendor.name
                            : _vm.__("(no name)", "dokan")
                        ) +
                        "\n            "
                    )
                  ]
                )
              ]
            }
          },
          {
            key: "reported_by",
            fn: function(ref) {
              var row = ref.row
              return [
                row.reported_by.admin_url
                  ? _c("a", {
                      attrs: {
                        href: row.reported_by.admin_url,
                        target: "_blank"
                      },
                      domProps: { textContent: _vm._s(row.reported_by.name) }
                    })
                  : [
                      _vm._v(
                        "\n                " +
                          _vm._s(row.reported_by.name) +
                          " <" +
                          _vm._s(row.reported_by.email) +
                          ">\n            "
                      )
                    ]
              ]
            }
          },
          {
            key: "reported_at",
            fn: function(ref) {
              var row = ref.row
              return [
                _vm._v(
                  "\n            " +
                    _vm._s(
                      _vm
                        .moment(row.reported_at)
                        .format("MMM D, YYYY h:mm:ss a")
                    ) +
                    "\n        "
                )
              ]
            }
          }
        ])
      }),
      _vm._v(" "),
      _vm.showModal
        ? _c(
            "modal",
            {
              attrs: {
                title: _vm.__("Product Abuse Report", "dokan"),
                footer: false
              },
              on: { close: _vm.hideReport }
            },
            [
              _c("template", { slot: "body" }, [
                _c("p", { staticStyle: { "margin-top": "0" } }, [
                  _c("strong", [
                    _vm._v(_vm._s(_vm.__("Reported for", "dokan")) + ":")
                  ]),
                  _vm._v(" "),
                  _c("a", { attrs: { href: _vm.report.product.admin_url } }, [
                    _vm._v(_vm._s(_vm.report.product.title))
                  ])
                ]),
                _vm._v(" "),
                _c("p", [
                  _c("strong", [
                    _vm._v(_vm._s(_vm.__("Reason", "dokan")) + ":")
                  ]),
                  _vm._v(" " + _vm._s(_vm.report.reason))
                ]),
                _vm._v(" "),
                _c("p", [
                  _c("strong", [
                    _vm._v(_vm._s(_vm.__("Description", "dokan")) + ":")
                  ]),
                  _vm._v(" " + _vm._s(_vm.report.description || "―"))
                ]),
                _vm._v(" "),
                _c(
                  "p",
                  [
                    _c("strong", [
                      _vm._v(_vm._s(_vm.__("Reported by", "dokan")) + ":")
                    ]),
                    _vm._v(" "),
                    _vm.report.reported_by.admin_url
                      ? _c("a", {
                          attrs: {
                            href: _vm.report.reported_by.admin_url,
                            target: "_blank"
                          },
                          domProps: {
                            textContent: _vm._s(_vm.report.reported_by.name)
                          }
                        })
                      : [
                          _vm._v(
                            "\n                    " +
                              _vm._s(_vm.report.reported_by.name) +
                              " <" +
                              _vm._s(_vm.report.reported_by.email) +
                              ">\n                "
                          )
                        ]
                  ],
                  2
                ),
                _vm._v(" "),
                _c("p", [
                  _c("strong", [
                    _vm._v(_vm._s(_vm.__("Reported At", "dokan")) + ":")
                  ]),
                  _vm._v(
                    " " +
                      _vm._s(
                        _vm
                          .moment(_vm.report.reported_at)
                          .format("MMM D, YYYY h:mm:ss a")
                      )
                  )
                ]),
                _vm._v(" "),
                _c(
                  "p",
                  [
                    _c("strong", [
                      _vm._v(_vm._s(_vm.__("Product Vendor", "dokan")) + ":")
                    ]),
                    _vm._v(" "),
                    _vm.report.reported_by.admin_url
                      ? _c("a", {
                          attrs: { href: _vm.report.reported_by.admin_url },
                          domProps: {
                            textContent: _vm._s(_vm.report.reported_by.name)
                          }
                        })
                      : [
                          _vm._v(
                            "\n                    " +
                              _vm._s(_vm.report.reported_by.name) +
                              " <" +
                              _vm._s(_vm.report.reported_by.email) +
                              ">\n                "
                          )
                        ]
                  ],
                  2
                )
              ])
            ],
            2
          )
        : _vm._e()
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
    require("vue-hot-reload-api")      .rerender("data-v-23efc86a", esExports)
  }
}

/***/ })

/******/ });