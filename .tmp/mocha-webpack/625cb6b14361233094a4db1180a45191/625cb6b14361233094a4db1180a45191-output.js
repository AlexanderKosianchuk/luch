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
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
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
/******/ 	return __webpack_require__(__webpack_require__.s = 5);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = require("expect");

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = require("react");

/***/ }),
/* 2 */
/***/ (function(module, exports) {

module.exports = require("react-redux-i18n");

/***/ }),
/* 3 */
/***/ (function(module, exports) {

module.exports = require("redux");

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"./controls/checkbox/checkbox.test.js": 7,
	"./store/settings.test.js": 26
};
function webpackContext(req) {
	return __webpack_require__(webpackContextResolve(req));
};
function webpackContextResolve(req) {
	var id = map[req];
	if(!(id + 1)) // check for number or string
		throw new Error("Cannot find module '" + req + "'.");
	return id;
};
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = 4;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var testsContext = __webpack_require__(4);

var runnable = testsContext.keys();

runnable.forEach(testsContext);

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = Checkbox;

__webpack_require__(27);

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _guid = __webpack_require__(30);

var _guid2 = _interopRequireDefault(_guid);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function Checkbox(props) {
    var uid = _guid2.default.create();
    return _react2.default.createElement(
        'section',
        { className: 'checkbox' },
        _react2.default.createElement(
            'div',
            { className: 'checkbox__container' },
            _react2.default.createElement('input', { id: 'checkbox__input-' + uid,
                type: 'checkbox',
                value: '',
                name: 'check',
                checked: props.checkstate || false,
                onChange: props.changeCheckState || function () {}
            }),
            _react2.default.createElement('label', { htmlFor: 'checkbox__input-' + uid })
        )
    );
}

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _enzyme = __webpack_require__(28);

var _expect = __webpack_require__(0);

var _expect2 = _interopRequireDefault(_expect);

var _expectJsx = __webpack_require__(29);

var _expectJsx2 = _interopRequireDefault(_expectJsx);

var _Checkbox = __webpack_require__(6);

var _Checkbox2 = _interopRequireDefault(_Checkbox);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_expect2.default.extend(_expectJsx2.default);

describe('controls/Checkbox', function () {
  it('should render Checkbox control', function () {
    var wrapper = (0, _enzyme.shallow)(_react2.default.createElement(_Checkbox2.default, null));
    (0, _expect2.default)(wrapper.find('input')).toHaveLength(1);;
  });
});

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = calibration;
var initialState = {
    pending: null,
    items: [],
    chosen: {}
};

function calibration() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_CALIBRATIONS_START':
            return _extends({}, state, { pending: true });
        case 'GET_CALIBRATION_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response,
                chosen: action.payload.response[0] || {}
            });
        case 'CHOOSE_CALIBRATION':
            return _extends({}, state, {
                chosen: action.payload
            });
        default:
            return state;
    }
}

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = fdrCyclo;
var initialState = {
    pending: null,
    analogParams: [],
    binaryParams: [],
    chosenAnalogParams: [],
    chosenBinaryParams: []
};

function fdrCyclo() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FDR_CYCLO_START':
            return _extends({}, state, { pending: true });
        case 'GET_FDR_CYCLO_COMPLETE':
            return _extends({}, state, {
                pending: false,
                analogParams: action.payload.response.analogParams,
                binaryParams: action.payload.response.binaryParams
            });
        case 'CHANGE_FLIGHT_PARAM_CHECKSTATE':
            var getIndexById = function getIndexById(id, array) {
                var itemIndex = null;
                array.forEach(function (item, index) {
                    if (item.id === id) itemIndex = index;
                });

                return itemIndex; // or undefined
            };

            var chosenParams = [];
            if (action.payload.paramType === 'ap') {
                chosenParams = state.chosenAnalogParams;
            } else if (action.payload.paramType === 'bp') {
                chosenParams = state.chosenBinaryParams;
            }

            var itemIndex = getIndexById(action.payload.id, chosenParams);

            if (action.payload.state === false && itemIndex !== null) {
                chosenParams.splice(itemIndex, 1);
            }

            if (action.payload.state === true && itemIndex === null) {
                chosenParams.push({
                    id: action.payload.id,
                    paramType: action.payload.paramType
                });
            }

            return _extends({}, state);
        case 'SET_CHECKED_FLIGHT_PARAMS':
            return _extends({}, state, {
                chosenAnalogParams: action.payload.ap,
                chosenBinaryParams: action.payload.bp
            });
        default:
            return state;
    }
}

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = fdrs;
var initialState = {
    pending: null,
    items: [],
    chosen: {}
};

function fdrs() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FDRS_START':
            return _extends({}, state, { pending: true });
        case 'GET_FDRS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response,
                chosen: action.payload.response[0] || {}
            });
        case 'CHOOSE_FDR':
            return _extends({}, state, {
                chosen: action.payload
            });
        default:
            return state;
    }
}

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = flight;
var initialState = {
    pending: null,
    id: null,
    duration: null,
    stepLength: null,
    startFlightTime: null,
    selectedStartFrame: null,
    selectedEndFrame: null,
    data: {}
};

function flight() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FLIGHT_START':
            return _extends({}, state, {
                pending: true,
                id: action.payload.flightId
            });
        case 'GET_FLIGHT_COMPLETE':
            return _extends({}, state, {
                pending: false,
                duration: action.payload.response.duration,
                stepLength: action.payload.response.stepLength,
                startFlightTime: action.payload.response.startFlightTime,
                selectedStartFrame: 0,
                selectedEndFrame: action.payload.response.duration,
                data: action.payload.response.data
            });
        case 'GET_FLIGHT_FAIL':
            return _extends({}, initialState);
        case 'CHANGE_SELECTED_START_FRAME':
            return _extends({}, state, { selectedStartFrame: action.payload });
        case 'CHANGE_SELECTED_END_FRAME':
            return _extends({}, state, { selectedEndFrame: action.payload });
        default:
            return state;
    }
}

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = flightEvents;
var initialState = {
    pending: null,
    flightId: null,
    expandedSections: [],
    items: null,
    isProcessed: false
};

function flightEvents() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'TOGGLE_EVENTS_SECTION':
            return _extends({}, state, { expandedSections: action.payload.expandedSections });
        case 'GET_FLIGHT_EVENTS_START':
            return _extends({}, initialState, {
                pending: true,
                flightId: action.payload.flightId
            });
        case 'GET_FLIGHT_EVENTS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                isProcessed: action.payload.response.isProcessed,
                items: action.payload.response.items
            });
        case 'POST_CHANGE_EVENT_RELIABILITY_COMPLETE':
            var flatItems = [];

            Object.keys(state.items).forEach(function (key) {
                flatItems = flatItems.concat(state.items[key]);
            });

            flatItems.forEach(function (event) {
                if (event.id === action.payload.request.eventId) {
                    event.reliability = action.payload.request.reliability;
                }
            });

            return _extends({}, state, {
                items: state.items
            });
        default:
            return state;
    }
}

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = flightFilter;
var initialState = {
    "fdr-type": "",
    bort: "",
    flight: "",
    "departure-airport": "",
    "arrival-airport": "",
    "from-date": "",
    "to-date": ""
};

function flightFilter() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'CHANGE_FLIGHT_FILTER_ITEM':
            return _extends({}, state, action.payload);
        case 'APPLY_FLIGHT_FILTER':
            return state;
        default:
            return state;
    }
}

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = flightTemplates;
var initialState = {
    pending: null,
    chosenItems: [],
    items: {}
};

function flightTemplates() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FLIGHT_TEMPLATES_START':
            return _extends({}, state, { pending: true });
        case 'GET_FLIGHT_TEMPLATES_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response
            });
        case 'DELETE_TEMPLATE_START':
            return _extends({}, state, { pending: true });
        case 'DELETE_TEMPLATE_COMPLETE':
            var newItems = [];
            state.items.forEach(function (item) {
                if (item.name !== action.payload.request.templateName) {
                    newItems.push(item);
                }
            });
            return _extends({}, state, {
                pending: false,
                items: newItems
            });
        case 'TEMPLATE_CHOSEN':
            if (state.chosenItems.indexOf(action.payload.name) === -1) {
                state.chosenItems.push(action.payload.name);
                return _extends({}, state);
            }
            return state;
        case 'TEMPLATE_UNCHOSEN':
            var index = state.chosenItems.indexOf(action.payload.name);
            state.chosenItems.splice(index, 1);
            return _extends({}, state);
        default:
            return state;
    }
}

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = flightUploadingState;

var _lodash = __webpack_require__(31);

var _lodash2 = _interopRequireDefault(_lodash);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

var initialState = [];

function flightUploadingState() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    function findUploadIndex(uploads, uploadingUid) {
        var progressChangedItem = null;
        uploads.find(function (element, index, array) {
            if (element.uploadingUid === uploadingUid) {
                progressChangedItem = index;
            }
        });

        return progressChangedItem;
    }

    switch (action.type) {
        case 'START_FLIGHT_UPLOADING':
            {
                state.push({
                    uploadingUid: action.payload.uploadingUid,
                    progress: 0
                });
                return [].concat(_toConsumableArray(state));
            }
        case 'FLIGHT_UPLOADING_PROGRESS_CHANGE':
            {
                var progressChangedItem = findUploadIndex(state, action.payload.uploadingUid);

                if (progressChangedItem !== null) {
                    state[progressChangedItem].progress = action.payload.progress;
                } else {
                    state.push(action.payload);
                }

                return [].concat(_toConsumableArray(state));
            }
        case 'FLIGHT_UPLOADING_COMPLETE':
            {
                var uploadingIndex = findUploadIndex(state, action.payload.uploadingUid);

                if ((0, _lodash2.default)(uploadingIndex)) {
                    state.splice(uploadingIndex, 1);
                    return [].concat(_toConsumableArray(state));
                }

                return state;
            }
        default:
            return state;
    }
}

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = flights;
var initialState = {
    pending: null,
    items: [],
    chosenItems: []
};

function flights() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FLIGHTS_START':
            return _extends({}, state, { pending: true });
        case 'GET_FLIGHTS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response
            });
        case 'DELETE_FLIGHT_COMPLETE':
            {
                var deletedIndex = state.items.findIndex(function (item) {
                    return item.id === action.payload.request.id;
                });

                if (deletedIndex !== null) {
                    state.items.splice(deletedIndex, 1);
                }

                deletedIndex = state.chosenItems.findIndex(function (item) {
                    return item.id === action.payload.request.id;
                });

                if (deletedIndex !== null) {
                    state.chosenItems.splice(deletedIndex, 1);
                }

                return _extends({}, state);
            }
        case 'PUT_FLIGHT_PATH_COMPLETE':
            {
                var movedIndex = state.items.findIndex(function (item) {
                    return item.id === action.payload.request.id;
                });

                if (movedIndex !== null) {
                    state.items[movedIndex].parentId = action.payload.request.parentId;
                }

                return _extends({}, state);
            }
        case 'FLIGHT_UPLOADING_COMPLETE':
            if (_typeof(action.payload.item) === 'object') {
                state.items.push(action.payload.item);
                return _extends({}, state);
            }

            return state;
        case 'FLIGHTS_CHOISE_TOGGLE':
            var chosenIndex = state.items.findIndex(function (item) {
                return item.id === action.payload.id;
            });

            var chosenItemsIndex = state.chosenItems.findIndex(function (item) {
                return item.id === action.payload.id;
            });

            if (chosenItemsIndex === -1 && action.payload.checkstate === false) {
                return state;
            }

            if (chosenItemsIndex >= 0 && action.payload.checkstate === true) {
                return state;
            }

            if (chosenItemsIndex === -1 && action.payload.checkstate === true) {
                state.chosenItems.push(state.items[chosenIndex]);
                return _extends({}, state);
            }

            if (chosenItemsIndex >= 0 && action.payload.checkstate === false) {
                state.chosenItems.splice(chosenItemsIndex, 1);
                return _extends({}, state);
            }

            return state;
        case 'FLIGHTS_UNCHOOSE_ALL':
            state.chosenItems = [];
            return _extends({}, state);
        default:
            return state;
    }
}

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = folders;
var initialState = {
    pending: null,
    items: [],
    expanded: null
};

function folders() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_FOLDERS_START':
            return _extends({}, state, { pending: true });
        case 'GET_FOLDERS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response
            });
        case 'DELETE_FOLDER_COMPLETE':
            {
                var deletedIndex = state.items.findIndex(function (item) {
                    return item.id === action.payload.request.id;
                });

                if (deletedIndex !== null) {
                    state.items.splice(deletedIndex, 1);
                }

                return _extends({}, state);
            }
        case 'POST_FOLDER_COMPLETE':
            state.items.push(action.payload.response);
            return _extends({}, state);
        case 'PUT_FOLDER_PATH_COMPLETE':
            {
                var movedIndex = state.items.findIndex(function (item) {
                    return item.id === action.payload.request.id;
                });

                if (movedIndex !== null) {
                    state.items[movedIndex].parentId = action.payload.request.parentId;
                }

                return _extends({}, state);
            }
        case 'PUT_FOLDER_EXPANDING_COMPLETE':
            var toggledExpandingItem = state.items.findIndex(function (item) {
                return item.id === action.payload.request.id;
            });

            if (toggledExpandingItem !== null) {
                state.items[toggledExpandingItem].expanded = action.payload.request.expanded === true;
            }

            return _extends({}, state);
        case 'PUT_FOLDER_RENAME_COMPLETE':
            var renamingItem = state.items.findIndex(function (item) {
                return item.id === action.payload.request.id;
            });

            if (renamingItem !== null) {
                state.items[renamingItem].name = action.payload.request.name;
            }

            return _extends({}, state);
        case 'FOLDER_LIST_EXPANDING_TOGGLE':
            if (typeof action.payload.expanded === 'boolean') {

                state.items.forEach(function (item, index) {
                    item.expanded = action.payload.expanded;
                });

                state.expanded = action.payload.expanded;
                return _extends({}, state);
            }

            return state;
        default:
            return state;
    }
}

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _redux = __webpack_require__(3);

var _reactRouterRedux = __webpack_require__(32);

var _reactReduxI18n = __webpack_require__(2);

var _flightFilter = __webpack_require__(13);

var _flightFilter2 = _interopRequireDefault(_flightFilter);

var _settlementFilter = __webpack_require__(20);

var _settlementFilter2 = _interopRequireDefault(_settlementFilter);

var _settlementsReport = __webpack_require__(21);

var _settlementsReport2 = _interopRequireDefault(_settlementsReport);

var _fdrs = __webpack_require__(10);

var _fdrs2 = _interopRequireDefault(_fdrs);

var _flight = __webpack_require__(11);

var _flight2 = _interopRequireDefault(_flight);

var _flights = __webpack_require__(16);

var _flights2 = _interopRequireDefault(_flights);

var _folders = __webpack_require__(17);

var _folders2 = _interopRequireDefault(_folders);

var _fdrCyclo = __webpack_require__(9);

var _fdrCyclo2 = _interopRequireDefault(_fdrCyclo);

var _calibrations = __webpack_require__(8);

var _calibrations2 = _interopRequireDefault(_calibrations);

var _flightUploadingState = __webpack_require__(15);

var _flightUploadingState2 = _interopRequireDefault(_flightUploadingState);

var _settings = __webpack_require__(19);

var _settings2 = _interopRequireDefault(_settings);

var _template = __webpack_require__(22);

var _template2 = _interopRequireDefault(_template);

var _flightTemplates = __webpack_require__(14);

var _flightTemplates2 = _interopRequireDefault(_flightTemplates);

var _flightEvents = __webpack_require__(12);

var _flightEvents2 = _interopRequireDefault(_flightEvents);

var _userReducer = __webpack_require__(23);

var _userReducer2 = _interopRequireDefault(_userReducer);

var _users = __webpack_require__(24);

var _users2 = _interopRequireDefault(_users);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = (0, _redux.combineReducers)({
    fdrs: _fdrs2.default,
    flight: _flight2.default,
    flights: _flights2.default,
    folders: _folders2.default,
    fdrCyclo: _fdrCyclo2.default,
    calibrations: _calibrations2.default,
    flightUploadingState: _flightUploadingState2.default,
    flightFilter: _flightFilter2.default,
    template: _template2.default,
    flightTemplates: _flightTemplates2.default,
    flightEvents: _flightEvents2.default,
    settlementFilter: _settlementFilter2.default,
    settlementsReport: _settlementsReport2.default,
    settings: _settings2.default,
    router: _reactRouterRedux.routerReducer,
    i18n: _reactReduxI18n.i18nReducer,
    user: _userReducer2.default,
    users: _users2.default
});

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = settings;
var initialState = {
    pending: null,
    items: {}
};

function settings() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_USER_SETTINGS_START':
            return _extends({}, state, { pending: true });
        case 'GET_USER_SETTINGS_COMPLETE':
            return {
                pending: false,
                items: action.payload.response
            };
        case 'CHANGE_SETTINGS_ITEM':
            var items = state.items;
            var key = action.payload.key;
            var value = action.payload.value;

            if (items && items[key] && items[key] !== value) {
                items[key] = value;
            }

            return _extends({}, state);
        default:
            return state;
    }
}

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = settlementFilter;
var initialState = {
    pending: null,
    avaliableSettlements: [],
    chosenSettlements: []
};

function settlementFilter() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_SETTLEMENTS_START':
            return _extends({}, state, { pending: true });
        case 'GET_SETTLEMENTS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                avaliableSettlements: action.payload.response.slice(), // copy array
                chosenSettlements: action.payload.response.slice()
            });
        case 'CHANGE_SETTLEMENT_ITEM_CHECKSTATE':
            var getIndexById = function getIndexById(id, array) {
                var itemIndex = null;
                array.forEach(function (item, index) {
                    if (item.id === id) itemIndex = index;
                });

                return itemIndex; // or undefined
            };

            var itemIndex = getIndexById(action.payload.id, state.chosenSettlements);

            if (action.payload.state === false && itemIndex !== null) {
                state.chosenSettlements.splice(itemIndex, 1);
            }

            if (action.payload.state === true && itemIndex === null) {
                var getItemById = function getItemById(id, array) {
                    var result = array.filter(function (o) {
                        return o.id == id;
                    });

                    return result ? result[0] : null; // or undefined
                };

                var item = getItemById(action.payload.id, state.avaliableSettlements);
                state.chosenSettlements.push(item);
            }

            return _extends({}, state);
        default:
            return state;
    }
}

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = settlementsReport;
var initialState = {
    pending: null,
    report: []
};

function settlementsReport() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_SETTLEMENTS_REPORT_START':
            state.pending = true;
            return _extends({}, state);
        case 'GET_SETTLEMENTS_REPORT_COMPLETE':
            return _extends({}, Object.assign(state, {
                pending: false,
                report: action.payload.response
            }));
        default:
            return state;
    }
}

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = template;
var initialState = {
    pending: null,
    name: {},
    servisePurpose: {},
    ap: {},
    bp: {}
};

function template() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    switch (action.type) {
        case 'GET_TEMPLATE_START':
            return _extends({}, state, { pending: true });
        case 'GET_TEMPLATE_COMPLETE':
            return {
                pending: false,
                name: action.payload.response.name,
                servisePurpose: action.payload.response.servisePurpose,
                ap: action.payload.response.ap,
                bp: action.payload.response.bp
            };
        default:
            return state;
    }
}

/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = userReducer;
function userReducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var action = arguments[1];

    switch (action.type) {
        case 'USER_LOGGED_IN':
            {
                var pl = action.payload;
                if (pl && pl.login && pl.login.length > 3 && pl.lang) {
                    return {
                        login: pl.login,
                        lang: pl.lang
                    };
                } else {
                    return {};
                }
            }
        case 'PUT_LANGUAGE_COMPLETE':
            {
                var _pl = action.payload;
                if (_pl.lang && state.lang !== _pl.lang) {
                    return _extends({}, state, {
                        lang: _pl.lang
                    });
                }
            }
        case 'USER_LOGGED_OUT':
            return {};
        default:
            return state;
    }
}

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = user;
var initialState = {
    pending: null,
    items: [],
    chosenItems: []
};

function user() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
    var action = arguments[1];

    var id = -1;
    var index = -1;
    var items = [];
    switch (action.type) {
        case 'GET_USERS_START':
            return _extends({}, state, { pending: true });
        case 'GET_USERS_COMPLETE':
            return _extends({}, state, {
                pending: false,
                items: action.payload.response
            });
        case 'USERS_CHOISE_TOGGLE':
            var chosenIndex = state.items.findIndex(function (item) {
                return item.id === action.payload.id;
            });

            var chosenItemsIndex = state.chosenItems.findIndex(function (item) {
                return item.id === action.payload.id;
            });

            if (typeof chosenItemsIndex === 'number' && action.payload.checkstate === true) {
                return state;
            }

            if (typeof chosenItemsIndex !== 'number' && action.payload.checkstate === false) {
                return state;
            }

            if (typeof chosenItemsIndex !== 'number' && action.payload.checkstate === true) {
                state.chosenItems.push(state.items[chosenIndex]);
                return _extends({}, state);
            }

            if (typeof chosenItemsIndex === 'number' && action.payload.checkstate === false) {
                state.chosenItems.splice(chosenItemsIndex, 1);
                return _extends({}, state);
            }

            return state;
        case 'POST_CREATE_USER_COMPLETE':
            items = state.items.slice();
            items.push(action.payload.response);
            return _extends({}, state, {
                items: items
            });
        case 'POST_DELETE_USER_COMPLETE':
            id = action.payload.request.userId;
            items = state.items.slice();
            index = items.findIndex(function (element) {
                return element.id === id;
            });

            if (index !== -1) items.splice(index, 1);

            return _extends({}, state, {
                items: items
            });
        case 'POST_EDIT_USER_COMPLETE':
            id = action.payload.response.id;
            items = state.items.slice();
            index = items.findIndex(function (element) {
                return element.id === id;
            });

            if (index !== -1) items.splice(index, 1);

            items.push(action.payload.response);

            return _extends({}, state, {
                items: items
            });
        default:
            return state;
    }
}

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = configureStore;

var _redux = __webpack_require__(3);

var _reduxThunk = __webpack_require__(34);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _reduxDevtoolsExtension = __webpack_require__(33);

var _reactReduxI18n = __webpack_require__(2);

var _rootReducer = __webpack_require__(18);

var _rootReducer2 = _interopRequireDefault(_rootReducer);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function configureStore(initialState, routerMiddleware) {

    var enhancer = (0, _redux.applyMiddleware)(_reduxThunk2.default, routerMiddleware);

    if (false) {
        enhancer = (0, _reduxDevtoolsExtension.composeWithDevTools)((0, _redux.applyMiddleware)(_reduxThunk2.default, routerMiddleware));
    }

    var store = (0, _redux.createStore)(_rootReducer2.default, initialState, enhancer);

    if (false) {
        (0, _reactReduxI18n.syncTranslationWithStore)(store);
    }

    if (false) {
        module.hot.accept('components/App', function () {
            render();
        });
    }

    return store;
}

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _expect = __webpack_require__(0);

var _expect2 = _interopRequireDefault(_expect);

var _configureStore = __webpack_require__(25);

var _configureStore2 = _interopRequireDefault(_configureStore);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var store = (0, _configureStore2.default)({}, function () {
  for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
    args[_key] = arguments[_key];
  }

  return function (args) {
    return args;
  };
});

describe('store settings reducer', function () {
  it('set settings start should set pending true', function () {
    store.dispatch({ type: 'GET_USER_SETTINGS_START' });

    var state = store.getState();
    var settings = state.settings;

    (0, _expect2.default)(settings.pending).toEqual(true);
  });
});

/***/ }),
/* 27 */
/***/ (function(module, exports) {

// empty (null-loader)

/***/ }),
/* 28 */
/***/ (function(module, exports) {

module.exports = require("enzyme");

/***/ }),
/* 29 */
/***/ (function(module, exports) {

module.exports = require("expect-jsx");

/***/ }),
/* 30 */
/***/ (function(module, exports) {

module.exports = require("guid");

/***/ }),
/* 31 */
/***/ (function(module, exports) {

module.exports = require("lodash.isinteger");

/***/ }),
/* 32 */
/***/ (function(module, exports) {

module.exports = require("react-router-redux");

/***/ }),
/* 33 */
/***/ (function(module, exports) {

module.exports = require("redux-devtools-extension");

/***/ }),
/* 34 */
/***/ (function(module, exports) {

module.exports = require("redux-thunk");

/***/ })
/******/ ]);
//# sourceMappingURL=625cb6b14361233094a4db1180a45191-output.js.map