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

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Components_Elements_Checkbox__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Components/Elements/Checkbox */ \"./src/Components/Elements/Checkbox.js\");\n/* harmony import */ var Images_check_svg__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Images/check.svg */ \"./src/Media/Images/check.svg\");\n/* harmony import */ var Images_cross_svg__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Images/cross.svg */ \"./src/Media/Images/cross.svg\");\n\n\n\n\nfunction PageItem(_ref) {\n  let {\n    page,\n    assigned,\n    hidden\n  } = _ref;\n  const typeLabels = {\n    post: 'Příspěvek',\n    page: 'Stránka',\n    cpt: 'CPT'\n  };\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"tr\", {\n    className: \"page-item\",\n    key: page.id,\n    style: {\n      display: hidden ? 'none' : 'table-row'\n    }\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Checkbox__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page_' + page.id + '_selected',\n    className: \"page-selected\",\n    checked: assigned\n  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", {\n    className: \"clickable-option\",\n    htmlFor: 'page_' + page.id + '_selected'\n  }, page.title)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, page.url ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"a\", {\n    href: window.location.origin + page.url\n  }, page.url) : ''), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", null, typeLabels[page.type]), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"td\", {\n    style: {\n      textAlign: 'center'\n    }\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"img\", {\n    src: assigned ? Images_check_svg__WEBPACK_IMPORTED_MODULE_2__[\"default\"] : Images_cross_svg__WEBPACK_IMPORTED_MODULE_3__[\"default\"]\n  })));\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PageItem);\n\n//# sourceURL=webpack://app/./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js?");

/***/ }),

/***/ "./src/Components/Content/Levels/Levels/LevelsContent/Pages.js":
/*!*********************************************************************!*\
  !*** ./src/Components/Content/Levels/Levels/LevelsContent/Pages.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Clients_PageClient__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Clients/PageClient */ \"./src/Clients/PageClient.js\");\n/* harmony import */ var Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Components/Elements/Loading */ \"./src/Components/Elements/Loading.js\");\n/* harmony import */ var Components_Elements_SubmitButton__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Components/Elements/SubmitButton */ \"./src/Components/Elements/SubmitButton.js\");\n/* harmony import */ var Components_Content_Levels_Levels_LevelsContent_PageItem__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! Components/Content/Levels/Levels/LevelsContent/PageItem */ \"./src/Components/Content/Levels/Levels/LevelsContent/PageItem.js\");\n/* harmony import */ var Components_Content_Levels_Levels_LevelsContent_PagesFilter__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! Components/Content/Levels/Levels/LevelsContent/PagesFilter */ \"./src/Components/Content/Levels/Levels/LevelsContent/PagesFilter.js\");\n/* harmony import */ var Components_Elements_Paginator__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! Components/Elements/Paginator */ \"./src/Components/Elements/Paginator.js\");\n\n\n\n\n\n\n\nfunction Pages(_ref) {\n  let {\n    level\n  } = _ref;\n  const pageClient = new Clients_PageClient__WEBPACK_IMPORTED_MODULE_1__[\"default\"]();\n  const [levelPageIds, setLevelPageIds] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [pages, setPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [paginatorPage, setPaginatorPage] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1);\n  const [paginatorItemsPerPage, setPaginatorItemsPerPage] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(25);\n  const [filteredPages, setFilteredPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [displayedPageIds, setDisplayedPageIds] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [loadPages, setLoadPages] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(true);\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    setLevelPageIds(null);\n    setLoadPages(true);\n  }, [level.id]);\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    if (filteredPages === null) {\n      return;\n    }\n    setDisplayedPageIds(filteredPages.slice(paginatorPage * paginatorItemsPerPage - paginatorItemsPerPage, paginatorPage * paginatorItemsPerPage).map(page => page.id));\n  }, [paginatorPage, paginatorItemsPerPage, filteredPages]);\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    const reloadPages = async () => {\n      await pageClient.listWithCpts().then(data => {\n        setPages(data);\n        if (filteredPages === null) {\n          setFilteredPages(data);\n        }\n      });\n      await pageClient.getIdsByLevel(level.id).then(data => {\n        setLevelPageIds(data);\n      });\n      setLoadPages(false);\n    };\n    if (loadPages === true) {\n      reloadPages();\n    }\n  }, [loadPages]);\n  const handleUpdatePages = async event => {\n    event.preventDefault();\n    const form = event.target;\n    const selectedCheckboxes = form.querySelectorAll('.page-selected:checked');\n    const pageIds = Array.from(selectedCheckboxes).map(checkbox => {\n      var id = checkbox.id.split('_')[1];\n      if (isNaN(parseInt(id))) {\n        return id;\n      }\n      return parseInt(id);\n    });\n    await pageClient.updatePagesForLevel(level.id, pageIds);\n    setLoadPages(true);\n  };\n  if (pages === null || levelPageIds === null) {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Loading__WEBPACK_IMPORTED_MODULE_2__[\"default\"], null);\n  } else {\n    return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"form\", {\n      className: \"levels-content levels-pages\",\n      onSubmit: handleUpdatePages\n    }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Content_Levels_Levels_LevelsContent_PagesFilter__WEBPACK_IMPORTED_MODULE_5__[\"default\"], {\n      pages: pages,\n      setFilteredPages: setFilteredPages,\n      assignedPageIds: levelPageIds,\n      loadPages: loadPages\n    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"br\", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"table\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"thead\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"tr\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", null, \"N\\xE1zev\"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", null, \"Url\"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", null, \"Typ\"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"th\", null, \"P\\u0159i\\u0159azeno\"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"tbody\", null, filteredPages.map(page => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Content_Levels_Levels_LevelsContent_PageItem__WEBPACK_IMPORTED_MODULE_4__[\"default\"], {\n      key: page.id,\n      page: page,\n      assigned: levelPageIds.includes(page.id),\n      hidden: !displayedPageIds.includes(page.id)\n    })), pages.filter(item => !filteredPages.includes(item)).map(page => {\n      console.log(page);\n      return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Content_Levels_Levels_LevelsContent_PageItem__WEBPACK_IMPORTED_MODULE_4__[\"default\"], {\n        key: page.id,\n        page: page,\n        assigned: levelPageIds.includes(page.id),\n        hidden: true\n      });\n    }))), filteredPages.length === 0 ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"p\", {\n      style: {\n        textAlign: 'center'\n      }\n    }, \"Nebyly nalezeny \\u017E\\xE1dn\\xE9 v\\xFDsledky\") : null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"br\", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Paginator__WEBPACK_IMPORTED_MODULE_6__[\"default\"], {\n      page: paginatorPage,\n      setPage: setPaginatorPage,\n      itemsPerPage: paginatorItemsPerPage,\n      setItemsPerPage: setPaginatorItemsPerPage,\n      itemCount: filteredPages.length\n    }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"br\", null), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_SubmitButton__WEBPACK_IMPORTED_MODULE_3__[\"default\"], {\n      text: \"Ulo\\u017Eit\",\n      style: {\n        position: 'sticky'\n      },\n      show: !loadPages,\n      centered: true,\n      big: true\n    }));\n  }\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Pages);\n\n//# sourceURL=webpack://app/./src/Components/Content/Levels/Levels/LevelsContent/Pages.js?");

/***/ }),

/***/ "./src/Components/Content/Levels/Levels/LevelsContent/PagesFilter.js":
/*!***************************************************************************!*\
  !*** ./src/Components/Content/Levels/Levels/LevelsContent/PagesFilter.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Components/Elements/Select */ \"./src/Components/Elements/Select.js\");\n\n\nfunction PagesFilter(_ref) {\n  let {\n    pages,\n    setFilteredPages,\n    assignedPageIds,\n    loadPages\n  } = _ref;\n  const [type, setType] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [assigned, setAssigned] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [search, setSearch] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null);\n  const [sortBy, setSortBy] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)('assigned');\n  const [order, setOrder] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)('asc');\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    var filteredPages = pages;\n    if (type !== null) {\n      filteredPages = filterType(type, filteredPages);\n    }\n    if (assigned !== null) {\n      filteredPages = filterAssigned(assigned, filteredPages);\n    }\n    if (search !== null && (search === null || search === void 0 ? void 0 : search.trim()) !== '') {\n      filteredPages = filterSearch(search, filteredPages);\n    }\n    filteredPages = sortPages(filteredPages, sortBy, order);\n    setFilteredPages(filteredPages);\n  }, [type, assigned, search, sortBy, order, loadPages]);\n  const filterType = (type, filteredPages) => {\n    return filteredPages.filter(page => page.type === type);\n  };\n  const filterAssigned = (assigned, filteredPages) => {\n    return filteredPages.filter(page => assignedPageIds.includes(page.id) === (parseInt(assigned) !== 0));\n  };\n  const filterSearch = (search, filteredPages) => {\n    return filteredPages.filter(page => {\n      var _page$url;\n      return page.title.toLowerCase().trim().includes(search.toLowerCase().trim()) || ((_page$url = page.url) === null || _page$url === void 0 || (_page$url = _page$url.toLowerCase()) === null || _page$url === void 0 || (_page$url = _page$url.trim()) === null || _page$url === void 0 ? void 0 : _page$url.includes(search.toLowerCase().trim()));\n    });\n  };\n  function sortPages(pages, sortBy, order) {\n    const orderValue = order === 'desc' ? -1 : 1;\n    return [...pages].sort((a, b) => {\n      if (sortBy === 'assigned') {\n        const sortA = assignedPageIds.includes(a.id) ? -1 * orderValue : orderValue;\n        const sortB = assignedPageIds.includes(b.id) ? -1 * orderValue : orderValue;\n        return sortA === sortB ? 0 : sortA;\n      }\n      if (['url', 'title'].includes(sortBy)) {\n        var _ref2, _a$sortBy;\n        return (_ref2 = ((_a$sortBy = a[sortBy]) === null || _a$sortBy === void 0 ? void 0 : _a$sortBy.localeCompare(b[sortBy])) * orderValue) !== null && _ref2 !== void 0 ? _ref2 : 0;\n      }\n      return 0;\n    });\n  }\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"pages-filter\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"input\", {\n    className: \"fm-input\",\n    style: {\n      width: '250px',\n      backgroundColor: 'white'\n    },\n    type: \"text\",\n    id: \"page-search\",\n    placeholder: 'Název/URL',\n    onInputCapture: e => {\n      setSearch(e.target.value);\n    }\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"filter-field\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", null, \"Typ: \"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page-type',\n    options: [{\n      text: 'Stránka',\n      value: 'page'\n    }, {\n      text: 'Příspěvek',\n      value: 'post'\n    }, {\n      text: 'CPT',\n      value: 'cpt'\n    }],\n    emptyText: 'Vše',\n    onChangeUpdateFunction: setType\n  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"filter-field\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", null, \"P\\u0159i\\u0159azen\\xED: \"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page-assigned',\n    options: [{\n      text: 'Přiřazeno',\n      value: 1\n    }, {\n      text: 'Nepřiřazeno',\n      value: 0\n    }],\n    emptyText: 'Vše',\n    onChangeUpdateFunction: setAssigned\n  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"filter-field\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"label\", null, \"Se\\u0159adit: \"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page-sort-by',\n    options: [{\n      text: 'Přiřazení',\n      value: 'assigned'\n    }, {\n      text: 'Název',\n      value: 'title'\n    }, {\n      text: 'URL',\n      value: 'url'\n    }],\n    defaultValue: 'assigned',\n    onChangeUpdateFunction: setSortBy,\n    includeEmptyOption: false\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'page-order',\n    options: [{\n      text: 'Vzestupně',\n      value: 'asc'\n    }, {\n      text: 'Sestupně',\n      value: 'desc'\n    }],\n    defaultValue: 'asc',\n    onChangeUpdateFunction: setOrder,\n    includeEmptyOption: false\n  })));\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PagesFilter);\n\n//# sourceURL=webpack://app/./src/Components/Content/Levels/Levels/LevelsContent/PagesFilter.js?");

/***/ }),

/***/ "./src/Components/Elements/Paginator.js":
/*!**********************************************!*\
  !*** ./src/Components/Elements/Paginator.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! Components/Elements/Select */ \"./src/Components/Elements/Select.js\");\n/* harmony import */ var Images_arrow_forward_svg__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! Images/arrow-forward.svg */ \"./src/Media/Images/arrow-forward.svg\");\n/* harmony import */ var Images_arrow_backward_svg__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! Images/arrow-backward.svg */ \"./src/Media/Images/arrow-backward.svg\");\n\n\n\n\nfunction Paginator(_ref) {\n  let {\n    page,\n    setPage,\n    itemsPerPage,\n    setItemsPerPage,\n    itemCount\n  } = _ref;\n  const changePage = direction => {\n    if (page + direction > 0 && (page + direction) * itemsPerPage - itemsPerPage < itemCount) {\n      setPage(page + direction);\n    }\n  };\n  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {\n    if (page * itemsPerPage > itemCount) {\n      setPage(1);\n    }\n  }, [itemCount, itemsPerPage]);\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    className: \"paginator\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"div\", {\n    style: {\n      width: '145px'\n    }\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", {\n    className: \"paginator-controls\"\n  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"img\", {\n    className: \"clickable-icon paginator-arrow\",\n    src: Images_arrow_backward_svg__WEBPACK_IMPORTED_MODULE_3__[\"default\"],\n    onClick: () => {\n      changePage(-1);\n    }\n  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", null, page * itemsPerPage - itemsPerPage + (itemCount > 0 ? 1 : 0), \" a\\u017E \", page * itemsPerPage < itemCount ? page * itemsPerPage : itemCount, \" z \", itemCount), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"img\", {\n    className: \"clickable-icon paginator-arrow\",\n    src: Images_arrow_forward_svg__WEBPACK_IMPORTED_MODULE_2__[\"default\"],\n    onClick: () => {\n      changePage(1);\n    }\n  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"span\", null, \"Str\\xE1nkov\\xE1n\\xED: \"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(Components_Elements_Select__WEBPACK_IMPORTED_MODULE_1__[\"default\"], {\n    id: 'paginator-items-per-page',\n    options: [{\n      value: 25,\n      text: '25'\n    }, {\n      value: 50,\n      text: '50'\n    }, {\n      value: 100,\n      text: '100'\n    }],\n    defaultValue: 5,\n    onChangeUpdateFunction: setItemsPerPage,\n    includeEmptyOption: false\n  })));\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Paginator);\n\n//# sourceURL=webpack://app/./src/Components/Elements/Paginator.js?");

/***/ }),

/***/ "./src/Components/Elements/Select.js":
/*!*******************************************!*\
  !*** ./src/Components/Elements/Select.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ \"./node_modules/react/index.js\");\n/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction Select(_ref) {\n  let {\n    id,\n    options,\n    emptyText = '-- nevybráno --',\n    defaultValue = null,\n    big = false,\n    includeEmptyOption = true,\n    onChangeUpdateFunction = value => {}\n  } = _ref;\n  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"select\", {\n    className: 'fm-select ' + (big ? 'big' : ''),\n    id: id,\n    name: id,\n    defaultValue: defaultValue,\n    onChange: e => {\n      onChangeUpdateFunction(e.target.value === 'null' ? null : e.target.value);\n    }\n  }, includeEmptyOption ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"option\", {\n    className: \"fm-option\",\n    value: 'null'\n  }, emptyText) : null, options.map(option => /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0___default().createElement(\"option\", {\n    key: option.value,\n    className: \"fm-option\",\n    value: option.value\n  }, option.text)));\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Select);\n\n//# sourceURL=webpack://app/./src/Components/Elements/Select.js?");

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

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ Page)\n/* harmony export */ });\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == typeof i ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != typeof t || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != typeof i) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nclass Page {\n  constructor(data) {\n    var _data$id, _data$title, _data$type, _data$url;\n    _defineProperty(this, \"id\", void 0);\n    _defineProperty(this, \"title\", void 0);\n    _defineProperty(this, \"type\", void 0);\n    _defineProperty(this, \"url\", void 0);\n    this.id = (_data$id = data === null || data === void 0 ? void 0 : data.id) !== null && _data$id !== void 0 ? _data$id : null;\n    this.title = (_data$title = data === null || data === void 0 ? void 0 : data.title) !== null && _data$title !== void 0 ? _data$title : null;\n    this.type = (_data$type = data === null || data === void 0 ? void 0 : data.type) !== null && _data$type !== void 0 ? _data$type : null;\n    this.url = (_data$url = data === null || data === void 0 ? void 0 : data.url) !== null && _data$url !== void 0 ? _data$url : null;\n  }\n}\n\n//# sourceURL=webpack://app/./src/Models/Page.js?");

/***/ }),

/***/ "./src/Media/Images/arrow-backward.svg":
/*!*********************************************!*\
  !*** ./src/Media/Images/arrow-backward.svg ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + \"/images/arrow-backward.svg\");\n\n//# sourceURL=webpack://app/./src/Media/Images/arrow-backward.svg?");

/***/ }),

/***/ "./src/Media/Images/arrow-forward.svg":
/*!********************************************!*\
  !*** ./src/Media/Images/arrow-forward.svg ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + \"/images/arrow-forward.svg\");\n\n//# sourceURL=webpack://app/./src/Media/Images/arrow-forward.svg?");

/***/ }),

/***/ "./src/Media/Images/check.svg":
/*!************************************!*\
  !*** ./src/Media/Images/check.svg ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + \"/images/check.svg\");\n\n//# sourceURL=webpack://app/./src/Media/Images/check.svg?");

/***/ }),

/***/ "./src/Media/Images/cross.svg":
/*!************************************!*\
  !*** ./src/Media/Images/cross.svg ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__webpack_require__.p + \"/images/cross.svg\");\n\n//# sourceURL=webpack://app/./src/Media/Images/cross.svg?");

/***/ })

}]);