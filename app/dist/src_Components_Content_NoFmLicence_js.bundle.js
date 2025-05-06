"use strict";
/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunkapp"] = self["webpackChunkapp"] || []).push([["src_Components_Content_NoFmLicence_js"],{

/***/ "./src/Components/Content/NoFmLicence.js":
/*!***********************************************!*\
  !*** ./src/Components/Content/NoFmLicence.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Images_stats_example_png__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Images/stats-example.png */ \"./src/Media/Images/stats-example.png\");\n/* harmony import */ var Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Components/Elements/Loading */ \"./src/Components/Elements/Loading.js\");\n/* harmony import */ var Clients_ApiConnectionClient__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Clients/ApiConnectionClient */ \"./src/Clients/ApiConnectionClient.js\");\n\n\n\n\nconst NoFmLicence = () => {\n  const [loading, setLoading] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(true);\n  const [urlObject, setUrlObject] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const connectionClient = new Clients_ApiConnectionClient__WEBPACK_IMPORTED_MODULE_3__[\"default\"]();\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    const fetchInitialData = async () => {\n      const connections = await connectionClient.list();\n      const connection = connections[0] || {};\n      const urlObject = new URL('https://page.fapi.cz/10559/fapi-member-pro');\n      const data = {\n        \"fapi-form-email\": connection.billing?.email,\n        \"fapi-form-mobil\": connection.billing?.phone,\n        \"fapi-form-company\": connection.billing?.name,\n        \"fapi-form-ic\": connection.billing?.ic,\n        \"fapi-form-dic\": connection.billing?.dic,\n        \"fapi-form-ic-dph\": connection.billing?.['ic_dph'],\n        \"fapi-form-street\": connection.billing?.address?.street,\n        \"fapi-form-city\": connection.billing?.address?.city,\n        \"fapi-form-postcode\": connection.billing?.address?.zip,\n        \"fapi-form-state\": connection.billing?.address?.country\n      };\n      const jsonData = JSON.stringify(data);\n      const base64EncodedData = btoa(encodeURIComponent(jsonData));\n      urlObject.search += `fapi-form-data=${base64EncodedData}`;\n      setUrlObject(urlObject);\n      setLoading(false);\n    };\n    if (loading) {\n      fetchInitialData();\n    }\n  }, [loading]);\n  if (loading === null) {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__[\"default\"], null);\n  }\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"fm-no-licence\",\n    style: {\n      backgroundImage: `url(${Images_stats_example_png__WEBPACK_IMPORTED_MODULE_1__[\"default\"]})`\n    }\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"blur-filter\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"a\", {\n    className: \"fm-link-button\",\n    target: \"_blank\",\n    href: urlObject\n  }, \"Z\\xEDskat FAPI Member Pro\")));\n};\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (NoFmLicence);\n\n//# sourceURL=webpack://app/./src/Components/Content/NoFmLicence.js?");

/***/ }),

/***/ "./src/Media/Images/stats-example.png":
/*!********************************************!*\
  !*** ./src/Media/Images/stats-example.png ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + \"src/Media/Images/stats-example.png\");\n\n//# sourceURL=webpack://app/./src/Media/Images/stats-example.png?");

/***/ })

}]);