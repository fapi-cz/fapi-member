"use strict";
/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunkapp"] = self["webpackChunkapp"] || []).push([["src_Components_Content_Levels_Levels_LevelsContent_Pages_js"],{

/***/ "./src/Clients/PageClient.js":
/*!***********************************!*\
  !*** ./src/Clients/PageClient.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ PageClient)\n/* harmony export */ });\n/* harmony import */ var _Client__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Client */ \"./src/Clients/Client.js\");\n/* harmony import */ var Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Enums/RequestMethodType */ \"./src/Enums/RequestMethodType.js\");\n/* harmony import */ var Models_Page__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Models/Page */ \"./src/Models/Page.js\");\n/* harmony import */ var Enums_ServicePageType__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Enums/ServicePageType */ \"./src/Enums/ServicePageType.js\");\n/* harmony import */ var Services_AlertService__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! Services/AlertService */ \"./src/Services/AlertService.js\");\n/* harmony import */ var Enums_CommonPageType__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! Enums/CommonPageType */ \"./src/Enums/CommonPageType.js\");\n\n\n\n\n\n\nclass PageClient extends _Client__WEBPACK_IMPORTED_MODULE_0__[\"default\"] {\n  constructor() {\n    super('pages');\n  }\n  async list() {\n    var pages = [];\n    const pagesData = await this.sendRequest('list', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.GET, {});\n    if (pagesData) {\n      pagesData.forEach(pageData => {\n        pages.push(new Models_Page__WEBPACK_IMPORTED_MODULE_2__[\"default\"](pageData));\n      });\n    }\n    return pages;\n  }\n  async listWithCpts() {\n    var pages = [];\n    const pagesData = await this.sendRequest('listWithCpts', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.GET, {});\n    if (pagesData) {\n      pagesData.forEach(pageData => {\n        pages.push(new Models_Page__WEBPACK_IMPORTED_MODULE_2__[\"default\"](pageData));\n      });\n    }\n    return pages;\n  }\n  async getIdsByLevel(levelId) {\n    return await this.sendRequest('getIdsByLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {\n      level_id: levelId\n    });\n  }\n  async getIdsByAllLevels() {\n    return await this.sendRequest('getIdsByAllLevels', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.GET, {});\n  }\n  async updatePagesForLevel(levelId, pages) {\n    return await this.sendRequest('updatePagesForLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {\n      level_id: levelId,\n      pages: pages\n    });\n  }\n  async getServicePagesForLevel(levelId) {\n    return await this.sendRequest('getServicePagesByLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {\n      level_id: levelId\n    });\n  }\n  async updateServicePagesForLevel(levelId) {\n    let noAccessPageId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;\n    let loginPageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;\n    let afterLoginPageId = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;\n    var pages = {\n      [Enums_ServicePageType__WEBPACK_IMPORTED_MODULE_3__.ServicePageType.NO_ACCESS]: noAccessPageId,\n      [Enums_ServicePageType__WEBPACK_IMPORTED_MODULE_3__.ServicePageType.LOGIN]: loginPageId,\n      [Enums_ServicePageType__WEBPACK_IMPORTED_MODULE_3__.ServicePageType.AFTER_LOGIN]: afterLoginPageId\n    };\n    await this.sendRequest('updateServicePagesForLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {\n      level_id: levelId,\n      pages: pages\n    });\n  }\n  async getCommonPagesForLevel() {\n    return await this.sendRequest('getCommonPagesByLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {});\n  }\n  async updateCommonPagesForLevel() {\n    let loginPageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;\n    let dashboardPageId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;\n    let timeLockedPageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;\n    var pages = {\n      [Enums_CommonPageType__WEBPACK_IMPORTED_MODULE_5__.CommonPageType.LOGIN_PAGE]: loginPageId,\n      [Enums_CommonPageType__WEBPACK_IMPORTED_MODULE_5__.CommonPageType.DASHBOARD_PAGE]: dashboardPageId,\n      [Enums_CommonPageType__WEBPACK_IMPORTED_MODULE_5__.CommonPageType.TIME_LOCKED_PAGE]: timeLockedPageId\n    };\n    await this.sendRequest('updateCommonPagesForLevel', Enums_RequestMethodType__WEBPACK_IMPORTED_MODULE_1__.RequestMethodType.POST, {\n      pages: pages\n    });\n  }\n}\n\n//# sourceURL=webpack://app/./src/Clients/PageClient.js?");

/***/ }),

/***/ "./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js":
/*!************************************************************************!*\
  !*** ./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Components_Elements_Checkbox__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Components/Elements/Checkbox */ \"./src/Components/Elements/Checkbox.js\");\n\n\nfunction PageItem(_ref) {\n  let {\n    page,\n    checked\n  } = _ref;\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"page-item\",\n    key: page.id\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Checkbox__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page_' + page.id + '_selected',\n    className: \"page-selected\",\n    checked: checked\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", {\n    className: \"clickable-option\",\n    htmlFor: 'page_' + page.id + '_selected'\n  }, page.title), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"vertical-divider\"\n  }));\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PageItem);\n\n//# sourceURL=webpack://app/./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js?");

/***/ }),

/***/ "./src/Components/Content/Levels/Levels/LevelsContent/Pages.js":
/*!*********************************************************************!*\
  !*** ./src/Components/Content/Levels/Levels/LevelsContent/Pages.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Clients_PageClient__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Clients/PageClient */ \"./src/Clients/PageClient.js\");\n/* harmony import */ var Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Components/Elements/Loading */ \"./src/Components/Elements/Loading.js\");\n/* harmony import */ var Components_Elements_SubmitButton__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Components/Elements/SubmitButton */ \"./src/Components/Elements/SubmitButton.js\");\n/* harmony import */ var Components_Content_Levels_Levels_LevelsContent_PageItem__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! Components/Content/Levels/Levels/LevelsContent/PageItem */ \"./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js\");\n\n\n\n\n\nfunction Pages(_ref) {\n  let {\n    level\n  } = _ref;\n  //TODO: add CPT\n  const pageClient = new Clients_PageClient__WEBPACK_IMPORTED_MODULE_1__[\"default\"]();\n  const [levelPageIds, setLevelPageIds] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [pages, setPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [loadPages, setLoadPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(true);\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    setLevelPageIds(null);\n    setLoadPages(true);\n  }, [level.id]);\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    const reloadPages = async () => {\n      await pageClient.listWithCpts().then(data => {\n        setPages(data);\n      });\n      await pageClient.getIdsByLevel(level.id).then(data => {\n        setLevelPageIds(data);\n      });\n      setLoadPages(false);\n    };\n    if (loadPages === true) {\n      reloadPages();\n    }\n  }, [loadPages]);\n  const handleUpdatePages = async event => {\n    event.preventDefault();\n    const form = event.target;\n    const selectedCheckboxes = form.querySelectorAll('.page-selected:checked');\n    const pageIds = Array.from(selectedCheckboxes).map(checkbox => {\n      var id = checkbox.id.split('_')[1];\n      if (isNaN(parseInt(id))) {\n        return id;\n      }\n      return parseInt(id);\n    });\n    await pageClient.updatePagesForLevel(level.id, pageIds);\n    setLoadPages(true);\n  };\n  if (pages === null || levelPageIds === null) {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__[\"default\"], null);\n  } else {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"form\", {\n      className: \"levels-content levels-pages\",\n      onSubmit: handleUpdatePages\n    }, [true, false].map(assigned => {\n      const assignedPages = pages.filter(page => levelPageIds.includes(page.id) === assigned);\n      if (assignedPages.length === 0) {\n        return null;\n      }\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n        key: assigned ? 'assigned' : 'unassigned'\n      }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"h2\", {\n        className: \"text-center\"\n      }, assigned ? 'Přiřazené' : 'Nepřiřazené'), [{\n        value: 'post',\n        title: 'Příspěvky'\n      }, {\n        value: 'page',\n        title: 'Stránky'\n      }, {\n        value: 'cpt',\n        title: 'CPT'\n      }].map(type => {\n        const typePages = assignedPages.filter(page => page.type === type.value);\n        if (typePages.length === 0) {\n          return null;\n        }\n        return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n          key: type.value\n        }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"h4\", null, type.title), typePages.map(page => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Content_Levels_Levels_LevelsContent_PageItem__WEBPACK_IMPORTED_MODULE_4__[\"default\"], {\n          key: page.id,\n          page: page,\n          checked: assigned\n        })));\n      }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"br\", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"br\", null));\n    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_SubmitButton__WEBPACK_IMPORTED_MODULE_3__[\"default\"], {\n      text: \"Ulo\\u017Eit\",\n      style: {\n        position: 'sticky'\n      },\n      show: !loadPages,\n      centered: true,\n      big: true\n    }));\n  }\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Pages);\n\n//# sourceURL=webpack://app/./src/Components/Content/Levels/Levels/LevelsContent/Pages.js?");

/***/ }),

/***/ "./src/Enums/CommonPageType.js":
/*!*************************************!*\
  !*** ./src/Enums/CommonPageType.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   CommonPageType: () => (/* binding */ CommonPageType)\n/* harmony export */ });\nconst CommonPageType = Object.freeze({\n  TIME_LOCKED_PAGE: 'time_locked_page_id',\n  LOGIN_PAGE: 'login_page_id',\n  DASHBOARD_PAGE: 'dashboard_page_id'\n});\n\n//# sourceURL=webpack://app/./src/Enums/CommonPageType.js?");

/***/ }),

/***/ "./src/Enums/ServicePageType.js":
/*!**************************************!*\
  !*** ./src/Enums/ServicePageType.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   ServicePageType: () => (/* binding */ ServicePageType)\n/* harmony export */ });\nconst ServicePageType = Object.freeze({\n  LOGIN: 'login',\n  AFTER_LOGIN: 'afterLogin',\n  NO_ACCESS: 'noAccess'\n});\n\n//# sourceURL=webpack://app/./src/Enums/ServicePageType.js?");

/***/ }),

/***/ "./src/Models/Page.js":
/*!****************************!*\
  !*** ./src/Models/Page.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ Page)\n/* harmony export */ });\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == typeof i ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != typeof i) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nclass Page {\n  constructor(data) {\n    var _data$id, _data$title, _data$type;\n    _defineProperty(this, \"id\", void 0);\n    _defineProperty(this, \"title\", void 0);\n    this.id = (_data$id = data === null || data === void 0 ? void 0 : data.id) !== null && _data$id !== void 0 ? _data$id : null;\n    this.title = (_data$title = data === null || data === void 0 ? void 0 : data.title) !== null && _data$title !== void 0 ? _data$title : null;\n    this.type = (_data$type = data === null || data === void 0 ? void 0 : data.type) !== null && _data$type !== void 0 ? _data$type : null;\n  }\n}\n\n//# sourceURL=webpack://app/./src/Models/Page.js?");

/***/ })

}]);