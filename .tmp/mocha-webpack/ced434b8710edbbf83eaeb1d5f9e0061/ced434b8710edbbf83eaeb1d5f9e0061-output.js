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
/******/ 	return __webpack_require__(__webpack_require__.s = 4);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = require("react");

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = require("expect");

/***/ }),
/* 2 */
/***/ (function(module, exports) {

module.exports = require("expect-jsx");

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"./controls/checkbox/checkbox.spec.js": 6,
	"./controls/menu/menu.spec.js": 8
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
webpackContext.id = 3;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var testsContext = __webpack_require__(3);

var runnable = testsContext.keys();

runnable.forEach(testsContext);

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.default = Checkbox;

__webpack_require__(9);

var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _guid = __webpack_require__(11);

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
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _enzyme = __webpack_require__(10);

var _expect = __webpack_require__(1);

var _expect2 = _interopRequireDefault(_expect);

var _expectJsx = __webpack_require__(2);

var _expectJsx2 = _interopRequireDefault(_expectJsx);

var _Checkbox = __webpack_require__(5);

var _Checkbox2 = _interopRequireDefault(_Checkbox);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_expect2.default.extend(_expectJsx2.default);

describe('controls/Checkbox', function () {
  it('should render Checkbox control', function () {
    var renderer = new ShallowRenderer();
    renderer.render(_react2.default.createElement(_Checkbox2.default, null));
    var actual = renderer.getRenderOutput();

    (0, _expect2.default)(actual.props.children).toEqual([_react2.default.createElement(
      'span',
      { className: 'heading' },
      'Title'
    ), _react2.default.createElement(Subcomponent, { foo: 'bar' })]);
  });
});

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _redux = __webpack_require__(14);

var _reactRedux = __webpack_require__(12);

var _TopMenu = __webpack_require__(!(function webpackMissingModule() { var e = new Error("Cannot find module \"controls/top-menu/TopMenu\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()));

var _TopMenu2 = _interopRequireDefault(_TopMenu);

var _MainMenu = __webpack_require__(!(function webpackMissingModule() { var e = new Error("Cannot find module \"controls/main-menu/MainMenu\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()));

var _MainMenu2 = _interopRequireDefault(_MainMenu);

var _redirect = __webpack_require__(!(function webpackMissingModule() { var e = new Error("Cannot find module \"actions/redirect\""); e.code = 'MODULE_NOT_FOUND'; throw e; }()));

var _redirect2 = _interopRequireDefault(_redirect);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var Menu = function (_React$Component) {
    _inherits(Menu, _React$Component);

    function Menu(props) {
        _classCallCheck(this, Menu);

        var _this = _possibleConstructorReturn(this, (Menu.__proto__ || Object.getPrototypeOf(Menu)).call(this, props));

        _this.state = {
            showMenu: false
        };
        return _this;
    }

    _createClass(Menu, [{
        key: 'handleToggleMenu',
        value: function handleToggleMenu(target) {
            if (target.className.includes('main-menu-toggle') && !this.state.showMenu) {
                this.setState({ showMenu: true });
            } else {
                this.setState({ showMenu: false });
            }
        }
    }, {
        key: 'handleMenuItemClick',
        value: function handleMenuItemClick(url) {
            this.setState({ showMenu: false });
            this.props.redirect(url);
        }
    }, {
        key: 'render',
        value: function render() {
            return _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(_TopMenu2.default, {
                    toggleMenu: this.handleToggleMenu.bind(this)
                }),
                _react2.default.createElement(_MainMenu2.default, {
                    isShown: this.state.showMenu,
                    toggleMenu: this.handleToggleMenu.bind(this),
                    handleMenuItemClick: this.handleMenuItemClick.bind(this)
                })
            );
        }
    }]);

    return Menu;
}(_react2.default.Component);

function mapDispatchToProps(dispatch) {
    return {
        redirect: (0, _redux.bindActionCreators)(_redirect2.default, dispatch)
    };
}

exports.default = (0, _reactRedux.connect)(function () {
    return {};
}, mapDispatchToProps)(Menu);

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(0);

var _react2 = _interopRequireDefault(_react);

var _shallow = __webpack_require__(13);

var _shallow2 = _interopRequireDefault(_shallow);

var _expect = __webpack_require__(1);

var _expect2 = _interopRequireDefault(_expect);

var _expectJsx = __webpack_require__(2);

var _expectJsx2 = _interopRequireDefault(_expectJsx);

var _Menu = __webpack_require__(7);

var _Menu2 = _interopRequireDefault(_Menu);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_expect2.default.extend(_expectJsx2.default);

describe('controls/Checkbox', function () {
  it('should render Checkbox control', function () {
    var renderer = new _shallow2.default();
    renderer.render(_react2.default.createElement(Checkbox, null));
    var actual = renderer.getRenderOutput();

    (0, _expect2.default)(actual.props.children).toEqual([_react2.default.createElement(TopMenu, null), _react2.default.createElement(MainMenu, null)]);

    (0, _expect2.default)(actual).toIncludeJSX(_react2.default.createElement('input', null));
  });
});

/***/ }),
/* 9 */
/***/ (function(module, exports) {

// empty (null-loader)

/***/ }),
/* 10 */
/***/ (function(module, exports) {

module.exports = require("enzyme");

/***/ }),
/* 11 */
/***/ (function(module, exports) {

module.exports = require("guid");

/***/ }),
/* 12 */
/***/ (function(module, exports) {

module.exports = require("react-redux");

/***/ }),
/* 13 */
/***/ (function(module, exports) {

module.exports = require("react-test-renderer/shallow");

/***/ }),
/* 14 */
/***/ (function(module, exports) {

module.exports = require("redux");

/***/ })
/******/ ]);
//# sourceMappingURL=ced434b8710edbbf83eaeb1d5f9e0061-output.js.map