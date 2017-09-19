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
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = require("react");

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"./controls/checkbox/checkbox.spec.js": 4
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
webpackContext.id = 1;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var testsContext = __webpack_require__(1);

var runnable = testsContext.keys();

runnable.forEach(testsContext);

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = Checkbox;

__webpack_require__(!(function webpackMissingModule() { var e = new Error("Cannot find module \"./checkbox.sass\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()));

var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _guid = __webpack_require__(7);

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
                value: 'None',
                name: 'check',
                checked: props.checkstate || false,
                onChange: props.changeCheckState || function () {}
            }),
            _react2.default.createElement('label', { htmlFor: 'checkbox__input-' + uid })
        )
    );
}

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _shallow = __webpack_require__(8);

var _shallow2 = _interopRequireDefault(_shallow);

var _expect = __webpack_require__(5);

var _expect2 = _interopRequireDefault(_expect);

var _expectJsx = __webpack_require__(6);

var _expectJsx2 = _interopRequireDefault(_expectJsx);

var _Checkbox = __webpack_require__(3);

var _Checkbox2 = _interopRequireDefault(_Checkbox);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_expect2.default.extend(_expectJsx2.default);

describe('controls/Checkbox', function () {
  it('should render Checkbox control', function () {
    var renderer = new _shallow2.default();
    renderer.render(_react2.default.createElement(_Checkbox2.default, null));
    var actual = renderer.getRenderOutput();
    var expected = _react2.default.createElement(
      'div',
      null,
      _react2.default.createElement(
        'h1',
        null,
        'Greeting'
      ),
      _react2.default.createElement(
        'div',
        null,
        'hello world'
      )
    );

    (0, _expect2.default)(actual).toEqual(expected);
  });
});

/***/ }),
/* 5 */
/***/ (function(module, exports) {

module.exports = require("expect");

/***/ }),
/* 6 */
/***/ (function(module, exports) {

module.exports = require("expect-jsx");

/***/ }),
/* 7 */
/***/ (function(module, exports) {

module.exports = require("guid");

/***/ }),
/* 8 */
/***/ (function(module, exports) {

module.exports = require("react-test-renderer/shallow");

/***/ })
/******/ ]);
//# sourceMappingURL=914b690ff86151f4322fef7331a64b11-output.js.map