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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./blocks/src/blocks.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./blocks/src/blocks.js":
/*!******************************!*\
  !*** ./blocks/src/blocks.js ***!
  \******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./style.scss */ "./blocks/src/style.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_style_scss__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./editor.scss */ "./blocks/src/editor.scss");
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_editor_scss__WEBPACK_IMPORTED_MODULE_1__);
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

var InspectorControls = wp.editor.InspectorControls;
var registerBlockType = wp.blocks.registerBlockType;
var __ = wp.i18n.__;
var _wp$element = wp.element,
    Component = _wp$element.Component,
    Fragment = _wp$element.Fragment;
var _wp$components = wp.components,
    PanelBody = _wp$components.PanelBody,
    TextControl = _wp$components.TextControl,
    RangeControl = _wp$components.RangeControl,
    CheckboxControl = _wp$components.CheckboxControl;

var blockIcon = function blockIcon() {
  return wp.element.createElement("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    height: "40",
    viewBox: "0 0 49 28",
    fill: "none"
  }, wp.element.createElement("path", {
    "fill-rule": "evenodd",
    "clip-rule": "evenodd",
    d: "M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z",
    fill: "#407BFF"
  }));
};




var EditBlock = /*#__PURE__*/function (_Component) {
  _inherits(EditBlock, _Component);

  var _super = _createSuper(EditBlock);

  function EditBlock(props) {
    _classCallCheck(this, EditBlock);

    return _super.call(this, props);
  }

  _createClass(EditBlock, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this$props = this.props,
          setAttributes = _this$props.setAttributes,
          _this$props$attribute = _this$props.attributes,
          name = _this$props$attribute.name,
          domain = _this$props$attribute.domain,
          fromGlobal = _this$props$attribute.fromGlobal;

      var _newName = Math.random().toString(36).substring(2, 15);

      if (!name) {
        setAttributes({
          name: _newName
        });
      }

      if (!domain) {
        setAttributes({
          domain: jitsi.domain
        });
      }
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
          attributes = _this$props2.attributes,
          setAttributes = _this$props2.setAttributes;
      var name = attributes.name,
          domain = attributes.domain,
          fromGlobal = attributes.fromGlobal,
          width = attributes.width,
          height = attributes.height,
          audioMuted = attributes.audioMuted,
          videoMuted = attributes.videoMuted,
          screenSharing = attributes.screenSharing,
          invite = attributes.invite;
      return wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
        title: __('Settings'),
        initialOpen: true
      }, wp.element.createElement(TextControl, {
        label: __('Name'),
        value: name,
        onChange: function onChange(val) {
          return setAttributes({
            name: val
          });
        }
      }), wp.element.createElement(TextControl, {
        label: __('Domain'),
        value: domain,
        onChange: function onChange(val) {
          return setAttributes({
            domain: val
          });
        },
        disabled: true
      }), wp.element.createElement(CheckboxControl, {
        label: __('Config from global'),
        checked: fromGlobal,
        onChange: function onChange(val) {
          setAttributes({
            fromGlobal: val
          });

          if (!fromGlobal) {
            setAttributes({
              width: parseInt(jitsi.meeting_width),
              height: parseInt(jitsi.meeting_height),
              audioMuted: parseInt(jitsi.startwithaudiomuted) ? true : false,
              videoMuted: parseInt(jitsi.startwithvideomuted) ? true : false,
              screenSharing: parseInt(jitsi.startscreensharing) ? true : false,
              invite: parseInt(jitsi.invite) ? true : false
            });
          }
        }
      }), !fromGlobal && wp.element.createElement("div", null, wp.element.createElement(RangeControl, {
        label: __('Width'),
        value: width,
        onChange: function onChange(val) {
          return setAttributes({
            width: val
          });
        },
        min: 100,
        max: 2000,
        step: 10
      }), wp.element.createElement(RangeControl, {
        label: __('Height'),
        value: height,
        onChange: function onChange(val) {
          return setAttributes({
            height: val
          });
        },
        min: 100,
        max: 2000,
        step: 10
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start with audio muted'),
        checked: audioMuted,
        onChange: function onChange(val) {
          return setAttributes({
            audioMuted: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start with video muted'),
        checked: videoMuted,
        onChange: function onChange(val) {
          return setAttributes({
            videoMuted: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start with screen sharing'),
        checked: screenSharing,
        onChange: function onChange(val) {
          return setAttributes({
            screenSharing: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Enable Inviting'),
        checked: invite,
        onChange: function onChange(val) {
          return setAttributes({
            invite: val
          });
        }
      })))), wp.element.createElement("div", {
        className: "jitsi-wrapper",
        "data-name": name,
        "data-domain": domain,
        "data-width": width,
        "data-height": height,
        "data-mute": audioMuted,
        "data-videomute": videoMuted,
        "data-screen": screenSharing,
        "data-invite": invite
      }, "https://".concat(domain, "/").concat(name)));
    }
  }]);

  return EditBlock;
}(Component);

registerBlockType('jitsi-meet-wp/jitsi-meet', {
  title: __('Jitsi Meet', 'jitsi-meet-wp'),
  icon: blockIcon,
  category: 'embed',
  keywords: [__('jitsi', 'jitsi-meet-wp'), __('meeting', 'jitsi-meet-wp'), __('video', 'jitsi-meet-wp'), __('conference', 'jitsi-meet-wp'), __('zoom', 'jitsi-meet-wp')],
  attributes: {
    name: {
      type: 'string',
      "default": ''
    },
    domain: {
      type: 'string',
      "default": ''
    },
    width: {
      type: 'number',
      "default": 1080
    },
    height: {
      type: 'number',
      "default": 720
    },
    fromGlobal: {
      type: 'boolean',
      "default": false
    },
    audioMuted: {
      type: 'boolean',
      "default": false
    },
    videoMuted: {
      type: 'boolean',
      "default": true
    },
    screenSharing: {
      type: 'boolean',
      "default": false
    },
    invite: {
      type: 'boolean',
      "default": true
    }
  },
  edit: EditBlock,
  save: function save(props) {
    var _props$attributes = props.attributes,
        name = _props$attributes.name,
        domain = _props$attributes.domain,
        width = _props$attributes.width,
        height = _props$attributes.height,
        audioMuted = _props$attributes.audioMuted,
        videoMuted = _props$attributes.videoMuted,
        screenSharing = _props$attributes.screenSharing,
        invite = _props$attributes.invite;
    return wp.element.createElement("div", {
      className: "jitsi-wrapper",
      "data-name": name,
      "data-domain": domain,
      "data-width": width,
      "data-height": height,
      "data-mute": audioMuted,
      "data-videomute": videoMuted,
      "data-screen": screenSharing,
      "data-invite": invite,
      style: {
        width: "".concat(width, "px")
      }
    });
  }
});

/***/ }),

/***/ "./blocks/src/editor.scss":
/*!********************************!*\
  !*** ./blocks/src/editor.scss ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./blocks/src/style.scss":
/*!*******************************!*\
  !*** ./blocks/src/style.scss ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vYmxvY2tzL3NyYy9ibG9ja3MuanMiLCJ3ZWJwYWNrOi8vLy4vYmxvY2tzL3NyYy9lZGl0b3Iuc2NzcyIsIndlYnBhY2s6Ly8vLi9ibG9ja3Mvc3JjL3N0eWxlLnNjc3MiXSwibmFtZXMiOlsiSW5zcGVjdG9yQ29udHJvbHMiLCJ3cCIsImVkaXRvciIsInJlZ2lzdGVyQmxvY2tUeXBlIiwiYmxvY2tzIiwiX18iLCJpMThuIiwiZWxlbWVudCIsIkNvbXBvbmVudCIsIkZyYWdtZW50IiwiY29tcG9uZW50cyIsIlBhbmVsQm9keSIsIlRleHRDb250cm9sIiwiUmFuZ2VDb250cm9sIiwiQ2hlY2tib3hDb250cm9sIiwiYmxvY2tJY29uIiwiRWRpdEJsb2NrIiwicHJvcHMiLCJzZXRBdHRyaWJ1dGVzIiwiYXR0cmlidXRlcyIsIm5hbWUiLCJkb21haW4iLCJmcm9tR2xvYmFsIiwiX25ld05hbWUiLCJNYXRoIiwicmFuZG9tIiwidG9TdHJpbmciLCJzdWJzdHJpbmciLCJqaXRzaSIsIndpZHRoIiwiaGVpZ2h0IiwiYXVkaW9NdXRlZCIsInZpZGVvTXV0ZWQiLCJzY3JlZW5TaGFyaW5nIiwiaW52aXRlIiwidmFsIiwicGFyc2VJbnQiLCJtZWV0aW5nX3dpZHRoIiwibWVldGluZ19oZWlnaHQiLCJzdGFydHdpdGhhdWRpb211dGVkIiwic3RhcnR3aXRodmlkZW9tdXRlZCIsInN0YXJ0c2NyZWVuc2hhcmluZyIsInRpdGxlIiwiaWNvbiIsImNhdGVnb3J5Iiwia2V5d29yZHMiLCJ0eXBlIiwiZWRpdCIsInNhdmUiXSwibWFwcGluZ3MiOiI7UUFBQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTs7O1FBR0E7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLDBDQUEwQyxnQ0FBZ0M7UUFDMUU7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQSx3REFBd0Qsa0JBQWtCO1FBQzFFO1FBQ0EsaURBQWlELGNBQWM7UUFDL0Q7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBLHlDQUF5QyxpQ0FBaUM7UUFDMUUsZ0hBQWdILG1CQUFtQixFQUFFO1FBQ3JJO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0EsMkJBQTJCLDBCQUEwQixFQUFFO1FBQ3ZELGlDQUFpQyxlQUFlO1FBQ2hEO1FBQ0E7UUFDQTs7UUFFQTtRQUNBLHNEQUFzRCwrREFBK0Q7O1FBRXJIO1FBQ0E7OztRQUdBO1FBQ0E7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNsRkEsSUFBUUEsaUJBQVIsR0FBOEJDLEVBQUUsQ0FBQ0MsTUFBakMsQ0FBUUYsaUJBQVI7QUFDQSxJQUFRRyxpQkFBUixHQUE4QkYsRUFBRSxDQUFDRyxNQUFqQyxDQUFRRCxpQkFBUjtBQUNBLElBQVFFLEVBQVIsR0FBZUosRUFBRSxDQUFDSyxJQUFsQixDQUFRRCxFQUFSO0FBQ0Esa0JBQWdDSixFQUFFLENBQUNNLE9BQW5DO0FBQUEsSUFBUUMsU0FBUixlQUFRQSxTQUFSO0FBQUEsSUFBbUJDLFFBQW5CLGVBQW1CQSxRQUFuQjtBQUNBLHFCQUFrRVIsRUFBRSxDQUFDUyxVQUFyRTtBQUFBLElBQVFDLFNBQVIsa0JBQVFBLFNBQVI7QUFBQSxJQUFtQkMsV0FBbkIsa0JBQW1CQSxXQUFuQjtBQUFBLElBQWdDQyxZQUFoQyxrQkFBZ0NBLFlBQWhDO0FBQUEsSUFBOENDLGVBQTlDLGtCQUE4Q0EsZUFBOUM7O0FBRUEsSUFBTUMsU0FBUyxHQUFHLFNBQVpBLFNBQVksR0FBTTtFQUN0QixPQUNFO0lBQ0UsS0FBSyxFQUFDLDRCQURSO0lBRUUsTUFBTSxFQUFDLElBRlQ7SUFHRSxPQUFPLEVBQUMsV0FIVjtJQUlFLElBQUksRUFBQztFQUpQLEdBTUU7SUFDRSxhQUFVLFNBRFo7SUFFRSxhQUFVLFNBRlo7SUFHRSxDQUFDLEVBQUMsMG1DQUhKO0lBSUUsSUFBSSxFQUFDO0VBSlAsRUFORixDQURGO0FBZUQsQ0FoQkQ7O0FBa0JBO0FBQ0E7O0lBRU1DLFM7Ozs7O0VBQ0osbUJBQVlDLEtBQVosRUFBbUI7SUFBQTs7SUFBQSx5QkFDWEEsS0FEVztFQUVsQjs7OztXQUVELDZCQUFvQjtNQUNsQixrQkFHSSxLQUFLQSxLQUhUO01BQUEsSUFDRUMsYUFERixlQUNFQSxhQURGO01BQUEsd0NBRUVDLFVBRkY7TUFBQSxJQUVnQkMsSUFGaEIseUJBRWdCQSxJQUZoQjtNQUFBLElBRXNCQyxNQUZ0Qix5QkFFc0JBLE1BRnRCO01BQUEsSUFFOEJDLFVBRjlCLHlCQUU4QkEsVUFGOUI7O01BSUEsSUFBTUMsUUFBUSxHQUFHQyxJQUFJLENBQUNDLE1BQUwsR0FBY0MsUUFBZCxDQUF1QixFQUF2QixFQUEyQkMsU0FBM0IsQ0FBcUMsQ0FBckMsRUFBd0MsRUFBeEMsQ0FBakI7O01BQ0EsSUFBSSxDQUFDUCxJQUFMLEVBQVc7UUFDVEYsYUFBYSxDQUFDO1VBQUVFLElBQUksRUFBRUc7UUFBUixDQUFELENBQWI7TUFDRDs7TUFFRCxJQUFJLENBQUNGLE1BQUwsRUFBYTtRQUNYSCxhQUFhLENBQUM7VUFBRUcsTUFBTSxFQUFFTyxLQUFLLENBQUNQO1FBQWhCLENBQUQsQ0FBYjtNQUNEO0lBRUY7OztXQUVELGtCQUFTO01BQ1AsbUJBQXNDLEtBQUtKLEtBQTNDO01BQUEsSUFBUUUsVUFBUixnQkFBUUEsVUFBUjtNQUFBLElBQW9CRCxhQUFwQixnQkFBb0JBLGFBQXBCO01BRUEsSUFDRUUsSUFERixHQVVJRCxVQVZKLENBQ0VDLElBREY7TUFBQSxJQUVFQyxNQUZGLEdBVUlGLFVBVkosQ0FFRUUsTUFGRjtNQUFBLElBR0VDLFVBSEYsR0FVSUgsVUFWSixDQUdFRyxVQUhGO01BQUEsSUFJRU8sS0FKRixHQVVJVixVQVZKLENBSUVVLEtBSkY7TUFBQSxJQUtFQyxNQUxGLEdBVUlYLFVBVkosQ0FLRVcsTUFMRjtNQUFBLElBTUVDLFVBTkYsR0FVSVosVUFWSixDQU1FWSxVQU5GO01BQUEsSUFPRUMsVUFQRixHQVVJYixVQVZKLENBT0VhLFVBUEY7TUFBQSxJQVFFQyxhQVJGLEdBVUlkLFVBVkosQ0FRRWMsYUFSRjtNQUFBLElBU0VDLE1BVEYsR0FVSWYsVUFWSixDQVNFZSxNQVRGO01BWUEsT0FDRSx5QkFBQyxRQUFELFFBQ0UseUJBQUMsaUJBQUQsUUFDRSx5QkFBQyxTQUFEO1FBQVcsS0FBSyxFQUFFN0IsRUFBRSxDQUFDLFVBQUQsQ0FBcEI7UUFBa0MsV0FBVyxFQUFFO01BQS9DLEdBQ0UseUJBQUMsV0FBRDtRQUNFLEtBQUssRUFBRUEsRUFBRSxDQUFDLE1BQUQsQ0FEWDtRQUVFLEtBQUssRUFBRWUsSUFGVDtRQUdFLFFBQVEsRUFBRSxrQkFBQ2UsR0FBRDtVQUFBLE9BQVNqQixhQUFhLENBQUM7WUFBRUUsSUFBSSxFQUFFZTtVQUFSLENBQUQsQ0FBdEI7UUFBQTtNQUhaLEVBREYsRUFPRSx5QkFBQyxXQUFEO1FBQ0UsS0FBSyxFQUFFOUIsRUFBRSxDQUFDLFFBQUQsQ0FEWDtRQUVFLEtBQUssRUFBRWdCLE1BRlQ7UUFHRSxRQUFRLEVBQUUsa0JBQUNjLEdBQUQ7VUFBQSxPQUFTakIsYUFBYSxDQUFDO1lBQUVHLE1BQU0sRUFBRWM7VUFBVixDQUFELENBQXRCO1FBQUEsQ0FIWjtRQUlFLFFBQVEsRUFBRTtNQUpaLEVBUEYsRUFjRSx5QkFBQyxlQUFEO1FBQ0UsS0FBSyxFQUFFOUIsRUFBRSxDQUFDLG9CQUFELENBRFg7UUFFRSxPQUFPLEVBQUVpQixVQUZYO1FBR0UsUUFBUSxFQUFFLGtCQUFDYSxHQUFELEVBQVM7VUFDakJqQixhQUFhLENBQUM7WUFBRUksVUFBVSxFQUFFYTtVQUFkLENBQUQsQ0FBYjs7VUFDQSxJQUFJLENBQUNiLFVBQUwsRUFBaUI7WUFDZkosYUFBYSxDQUFDO2NBQ1pXLEtBQUssRUFBRU8sUUFBUSxDQUFDUixLQUFLLENBQUNTLGFBQVAsQ0FESDtjQUVaUCxNQUFNLEVBQUVNLFFBQVEsQ0FBQ1IsS0FBSyxDQUFDVSxjQUFQLENBRko7Y0FHWlAsVUFBVSxFQUFFSyxRQUFRLENBQUNSLEtBQUssQ0FBQ1csbUJBQVAsQ0FBUixHQUNSLElBRFEsR0FFUixLQUxRO2NBTVpQLFVBQVUsRUFBRUksUUFBUSxDQUFDUixLQUFLLENBQUNZLG1CQUFQLENBQVIsR0FDUixJQURRLEdBRVIsS0FSUTtjQVNaUCxhQUFhLEVBQUVHLFFBQVEsQ0FBQ1IsS0FBSyxDQUFDYSxrQkFBUCxDQUFSLEdBQ1gsSUFEVyxHQUVYLEtBWFE7Y0FZWlAsTUFBTSxFQUFFRSxRQUFRLENBQUNSLEtBQUssQ0FBQ00sTUFBUCxDQUFSLEdBQXlCLElBQXpCLEdBQWdDO1lBWjVCLENBQUQsQ0FBYjtVQWNEO1FBQ0Y7TUFyQkgsRUFkRixFQXFDRyxDQUFDWixVQUFELElBQ0Msc0NBQ0UseUJBQUMsWUFBRDtRQUNFLEtBQUssRUFBRWpCLEVBQUUsQ0FBQyxPQUFELENBRFg7UUFFRSxLQUFLLEVBQUV3QixLQUZUO1FBR0UsUUFBUSxFQUFFLGtCQUFDTSxHQUFEO1VBQUEsT0FBU2pCLGFBQWEsQ0FBQztZQUFFVyxLQUFLLEVBQUVNO1VBQVQsQ0FBRCxDQUF0QjtRQUFBLENBSFo7UUFJRSxHQUFHLEVBQUUsR0FKUDtRQUtFLEdBQUcsRUFBRSxJQUxQO1FBTUUsSUFBSSxFQUFFO01BTlIsRUFERixFQVNFLHlCQUFDLFlBQUQ7UUFDRSxLQUFLLEVBQUU5QixFQUFFLENBQUMsUUFBRCxDQURYO1FBRUUsS0FBSyxFQUFFeUIsTUFGVDtRQUdFLFFBQVEsRUFBRSxrQkFBQ0ssR0FBRDtVQUFBLE9BQVNqQixhQUFhLENBQUM7WUFBRVksTUFBTSxFQUFFSztVQUFWLENBQUQsQ0FBdEI7UUFBQSxDQUhaO1FBSUUsR0FBRyxFQUFFLEdBSlA7UUFLRSxHQUFHLEVBQUUsSUFMUDtRQU1FLElBQUksRUFBRTtNQU5SLEVBVEYsRUFpQkUseUJBQUMsZUFBRDtRQUNFLEtBQUssRUFBRTlCLEVBQUUsQ0FBQyx3QkFBRCxDQURYO1FBRUUsT0FBTyxFQUFFMEIsVUFGWDtRQUdFLFFBQVEsRUFBRSxrQkFBQ0ksR0FBRDtVQUFBLE9BQVNqQixhQUFhLENBQUM7WUFBRWEsVUFBVSxFQUFFSTtVQUFkLENBQUQsQ0FBdEI7UUFBQTtNQUhaLEVBakJGLEVBc0JFLHlCQUFDLGVBQUQ7UUFDRSxLQUFLLEVBQUU5QixFQUFFLENBQUMsd0JBQUQsQ0FEWDtRQUVFLE9BQU8sRUFBRTJCLFVBRlg7UUFHRSxRQUFRLEVBQUUsa0JBQUNHLEdBQUQ7VUFBQSxPQUFTakIsYUFBYSxDQUFDO1lBQUVjLFVBQVUsRUFBRUc7VUFBZCxDQUFELENBQXRCO1FBQUE7TUFIWixFQXRCRixFQTJCRSx5QkFBQyxlQUFEO1FBQ0UsS0FBSyxFQUFFOUIsRUFBRSxDQUFDLDJCQUFELENBRFg7UUFFRSxPQUFPLEVBQUU0QixhQUZYO1FBR0UsUUFBUSxFQUFFLGtCQUFDRSxHQUFEO1VBQUEsT0FBU2pCLGFBQWEsQ0FBQztZQUFFZSxhQUFhLEVBQUVFO1VBQWpCLENBQUQsQ0FBdEI7UUFBQTtNQUhaLEVBM0JGLEVBZ0NFLHlCQUFDLGVBQUQ7UUFDRSxLQUFLLEVBQUU5QixFQUFFLENBQUMsaUJBQUQsQ0FEWDtRQUVFLE9BQU8sRUFBRTZCLE1BRlg7UUFHRSxRQUFRLEVBQUUsa0JBQUNDLEdBQUQ7VUFBQSxPQUFTakIsYUFBYSxDQUFDO1lBQUVnQixNQUFNLEVBQUVDO1VBQVYsQ0FBRCxDQUF0QjtRQUFBO01BSFosRUFoQ0YsQ0F0Q0osQ0FERixDQURGLEVBaUZFO1FBQ0UsU0FBUyxFQUFDLGVBRFo7UUFFRSxhQUFXZixJQUZiO1FBR0UsZUFBYUMsTUFIZjtRQUlFLGNBQVlRLEtBSmQ7UUFLRSxlQUFhQyxNQUxmO1FBTUUsYUFBV0MsVUFOYjtRQU9FLGtCQUFnQkMsVUFQbEI7UUFRRSxlQUFhQyxhQVJmO1FBU0UsZUFBYUM7TUFUZixxQkFVYWIsTUFWYixjQVV1QkQsSUFWdkIsRUFqRkYsQ0FERjtJQStGRDs7OztFQW5JcUJaLFM7O0FBc0l4QkwsaUJBQWlCLENBQUMsMEJBQUQsRUFBNkI7RUFDNUN1QyxLQUFLLEVBQUVyQyxFQUFFLENBQUMsWUFBRCxFQUFlLGVBQWYsQ0FEbUM7RUFFNUNzQyxJQUFJLEVBQUU1QixTQUZzQztFQUc1QzZCLFFBQVEsRUFBRSxPQUhrQztFQUk1Q0MsUUFBUSxFQUFFLENBQ1J4QyxFQUFFLENBQUMsT0FBRCxFQUFVLGVBQVYsQ0FETSxFQUVSQSxFQUFFLENBQUMsU0FBRCxFQUFZLGVBQVosQ0FGTSxFQUdSQSxFQUFFLENBQUMsT0FBRCxFQUFVLGVBQVYsQ0FITSxFQUlSQSxFQUFFLENBQUMsWUFBRCxFQUFlLGVBQWYsQ0FKTSxFQUtSQSxFQUFFLENBQUMsTUFBRCxFQUFTLGVBQVQsQ0FMTSxDQUprQztFQVc1Q2MsVUFBVSxFQUFFO0lBQ1ZDLElBQUksRUFBRTtNQUNKMEIsSUFBSSxFQUFFLFFBREY7TUFFSixXQUFTO0lBRkwsQ0FESTtJQUtWekIsTUFBTSxFQUFFO01BQ055QixJQUFJLEVBQUUsUUFEQTtNQUVOLFdBQVM7SUFGSCxDQUxFO0lBU1ZqQixLQUFLLEVBQUU7TUFDTGlCLElBQUksRUFBRSxRQUREO01BRUwsV0FBUztJQUZKLENBVEc7SUFhVmhCLE1BQU0sRUFBRTtNQUNOZ0IsSUFBSSxFQUFFLFFBREE7TUFFTixXQUFTO0lBRkgsQ0FiRTtJQWlCVnhCLFVBQVUsRUFBRTtNQUNWd0IsSUFBSSxFQUFFLFNBREk7TUFFVixXQUFTO0lBRkMsQ0FqQkY7SUFxQlZmLFVBQVUsRUFBRTtNQUNWZSxJQUFJLEVBQUUsU0FESTtNQUVWLFdBQVM7SUFGQyxDQXJCRjtJQXlCVmQsVUFBVSxFQUFFO01BQ1ZjLElBQUksRUFBRSxTQURJO01BRVYsV0FBUztJQUZDLENBekJGO0lBNkJWYixhQUFhLEVBQUU7TUFDYmEsSUFBSSxFQUFFLFNBRE87TUFFYixXQUFTO0lBRkksQ0E3Qkw7SUFpQ1ZaLE1BQU0sRUFBRTtNQUNOWSxJQUFJLEVBQUUsU0FEQTtNQUVOLFdBQVM7SUFGSDtFQWpDRSxDQVhnQztFQWlENUNDLElBQUksRUFBRS9CLFNBakRzQztFQWtENUNnQyxJQUFJLEVBQUUsY0FBVS9CLEtBQVYsRUFBaUI7SUFDckIsd0JBU0lBLEtBQUssQ0FBQ0UsVUFUVjtJQUFBLElBQ0VDLElBREYscUJBQ0VBLElBREY7SUFBQSxJQUVFQyxNQUZGLHFCQUVFQSxNQUZGO0lBQUEsSUFHRVEsS0FIRixxQkFHRUEsS0FIRjtJQUFBLElBSUVDLE1BSkYscUJBSUVBLE1BSkY7SUFBQSxJQUtFQyxVQUxGLHFCQUtFQSxVQUxGO0lBQUEsSUFNRUMsVUFORixxQkFNRUEsVUFORjtJQUFBLElBT0VDLGFBUEYscUJBT0VBLGFBUEY7SUFBQSxJQVFFQyxNQVJGLHFCQVFFQSxNQVJGO0lBV0EsT0FDRTtNQUNFLFNBQVMsRUFBQyxlQURaO01BRUUsYUFBV2QsSUFGYjtNQUdFLGVBQWFDLE1BSGY7TUFJRSxjQUFZUSxLQUpkO01BS0UsZUFBYUMsTUFMZjtNQU1FLGFBQVdDLFVBTmI7TUFPRSxrQkFBZ0JDLFVBUGxCO01BUUUsZUFBYUMsYUFSZjtNQVNFLGVBQWFDLE1BVGY7TUFVRSxLQUFLLEVBQUU7UUFDTEwsS0FBSyxZQUFLQSxLQUFMO01BREE7SUFWVCxFQURGO0VBZ0JEO0FBOUUyQyxDQUE3QixDQUFqQixDOzs7Ozs7Ozs7OztBQ2pLQSx5Qzs7Ozs7Ozs7Ozs7QUNBQSx5QyIsImZpbGUiOiJibG9ja3MuYnVpbGQuanMiLCJzb3VyY2VzQ29udGVudCI6WyIgXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuIFx0ZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXG4gXHRcdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuIFx0XHRpZihpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSkge1xuIFx0XHRcdHJldHVybiBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXS5leHBvcnRzO1xuIFx0XHR9XG4gXHRcdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG4gXHRcdHZhciBtb2R1bGUgPSBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSA9IHtcbiBcdFx0XHRpOiBtb2R1bGVJZCxcbiBcdFx0XHRsOiBmYWxzZSxcbiBcdFx0XHRleHBvcnRzOiB7fVxuIFx0XHR9O1xuXG4gXHRcdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuIFx0XHRtb2R1bGVzW21vZHVsZUlkXS5jYWxsKG1vZHVsZS5leHBvcnRzLCBtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuIFx0XHQvLyBGbGFnIHRoZSBtb2R1bGUgYXMgbG9hZGVkXG4gXHRcdG1vZHVsZS5sID0gdHJ1ZTtcblxuIFx0XHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuIFx0XHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG4gXHR9XG5cblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBnZXR0ZXIgfSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uciA9IGZ1bmN0aW9uKGV4cG9ydHMpIHtcbiBcdFx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG4gXHRcdH1cbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbiBcdH07XG5cbiBcdC8vIGNyZWF0ZSBhIGZha2UgbmFtZXNwYWNlIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDE6IHZhbHVlIGlzIGEgbW9kdWxlIGlkLCByZXF1aXJlIGl0XG4gXHQvLyBtb2RlICYgMjogbWVyZ2UgYWxsIHByb3BlcnRpZXMgb2YgdmFsdWUgaW50byB0aGUgbnNcbiBcdC8vIG1vZGUgJiA0OiByZXR1cm4gdmFsdWUgd2hlbiBhbHJlYWR5IG5zIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDh8MTogYmVoYXZlIGxpa2UgcmVxdWlyZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy50ID0gZnVuY3Rpb24odmFsdWUsIG1vZGUpIHtcbiBcdFx0aWYobW9kZSAmIDEpIHZhbHVlID0gX193ZWJwYWNrX3JlcXVpcmVfXyh2YWx1ZSk7XG4gXHRcdGlmKG1vZGUgJiA4KSByZXR1cm4gdmFsdWU7XG4gXHRcdGlmKChtb2RlICYgNCkgJiYgdHlwZW9mIHZhbHVlID09PSAnb2JqZWN0JyAmJiB2YWx1ZSAmJiB2YWx1ZS5fX2VzTW9kdWxlKSByZXR1cm4gdmFsdWU7XG4gXHRcdHZhciBucyA9IE9iamVjdC5jcmVhdGUobnVsbCk7XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18ucihucyk7XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShucywgJ2RlZmF1bHQnLCB7IGVudW1lcmFibGU6IHRydWUsIHZhbHVlOiB2YWx1ZSB9KTtcbiBcdFx0aWYobW9kZSAmIDIgJiYgdHlwZW9mIHZhbHVlICE9ICdzdHJpbmcnKSBmb3IodmFyIGtleSBpbiB2YWx1ZSkgX193ZWJwYWNrX3JlcXVpcmVfXy5kKG5zLCBrZXksIGZ1bmN0aW9uKGtleSkgeyByZXR1cm4gdmFsdWVba2V5XTsgfS5iaW5kKG51bGwsIGtleSkpO1xuIFx0XHRyZXR1cm4gbnM7XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIlwiO1xuXG5cbiBcdC8vIExvYWQgZW50cnkgbW9kdWxlIGFuZCByZXR1cm4gZXhwb3J0c1xuIFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18oX193ZWJwYWNrX3JlcXVpcmVfXy5zID0gXCIuL2Jsb2Nrcy9zcmMvYmxvY2tzLmpzXCIpO1xuIiwiY29uc3QgeyBJbnNwZWN0b3JDb250cm9scyB9ID0gd3AuZWRpdG9yO1xyXG5jb25zdCB7IHJlZ2lzdGVyQmxvY2tUeXBlIH0gPSB3cC5ibG9ja3M7XHJcbmNvbnN0IHsgX18gfSA9IHdwLmkxOG47XHJcbmNvbnN0IHsgQ29tcG9uZW50LCBGcmFnbWVudCB9ID0gd3AuZWxlbWVudDtcclxuY29uc3QgeyBQYW5lbEJvZHksIFRleHRDb250cm9sLCBSYW5nZUNvbnRyb2wsIENoZWNrYm94Q29udHJvbCB9ID0gd3AuY29tcG9uZW50cztcclxuXHJcbmNvbnN0IGJsb2NrSWNvbiA9ICgpID0+IHtcclxuICByZXR1cm4gKFxyXG4gICAgPHN2Z1xyXG4gICAgICB4bWxucz1cImh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnXCJcclxuICAgICAgaGVpZ2h0PVwiNDBcIlxyXG4gICAgICB2aWV3Qm94PVwiMCAwIDQ5IDI4XCJcclxuICAgICAgZmlsbD1cIm5vbmVcIlxyXG4gICAgPlxyXG4gICAgICA8cGF0aFxyXG4gICAgICAgIGZpbGwtcnVsZT1cImV2ZW5vZGRcIlxyXG4gICAgICAgIGNsaXAtcnVsZT1cImV2ZW5vZGRcIlxyXG4gICAgICAgIGQ9XCJNMzQuNDc1NyAyMi4wNjE0VjE3LjI5NDFMNDMuMDMyMyAyMy40MDYxQzQzLjUzNjEgMjMuNzY1OSA0NC4xOTg3IDIzLjgxNCA0NC43NDkxIDIzLjUzMDdDNDUuMjk5NiAyMy4yNDc0IDQ1LjY0NTUgMjIuNjgwMyA0NS42NDU1IDIyLjA2MTJWNS41MzQ5MkM0NS42NDU1IDQuOTE1ODcgNDUuMjk5NiA0LjM0ODczIDQ0Ljc0OTEgNC4wNjU0NUM0NC4xOTg3IDMuNzgyMTkgNDMuNTM2MSAzLjgzMDMgNDMuMDMyMyA0LjE5MDEyTDM0LjQ3NTcgMTAuMzAyMVY1LjUzNTA0QzM0LjQ3NTcgMi42MTc0MSAzMS44Nzg0IDAuNTc3MTQ4IDI5LjA5OTggMC41NzcxNDhIOC42MjIzOUM1Ljg0Mzg3IDAuNTc3MTQ4IDMuMjQ2NTggMi42MTc0MSAzLjI0NjU4IDUuNTM1MDRWMjIuMDYxNEMzLjI0NjU4IDI0Ljk3OSA1Ljg0Mzg3IDI3LjAxOTMgOC42MjIzOSAyNy4wMTkzSDI5LjA5OThDMzEuODc4NCAyNy4wMTkzIDM0LjQ3NTcgMjQuOTc5IDM0LjQ3NTcgMjIuMDYxNFpNMjAuMzMxNiAxOC4xNzU5QzE3LjgyMzIgMTYuODkwNiAxNS43NjY4IDE0Ljg0MzEgMTQuNDkwNCAxMi4zMzQ3TDE2LjQ0MDQgMTAuMzg0N0MxNi42ODg2IDEwLjEzNjUgMTYuNzU5NiA5Ljc5MDgxIDE2LjY2MjEgOS40ODA1OUMxNi4zMzQxIDguNDg3ODQgMTYuMTU2OCA3LjQyNDIxIDE2LjE1NjggNi4zMTYyN0MxNi4xNTY4IDUuODI4NzYgMTUuNzU4IDUuNDI5OSAxNS4yNzA0IDUuNDI5OUgxMi4xNjgxQzExLjY4MDcgNS40Mjk5IDExLjI4MTggNS44Mjg3NiAxMS4yODE4IDYuMzE2MjdDMTEuMjgxOCAxNC42MzkzIDE4LjAyNyAyMS4zODQ1IDI2LjM1IDIxLjM4NDVDMjYuODM3NSAyMS4zODQ1IDI3LjIzNjQgMjAuOTg1NiAyNy4yMzY0IDIwLjQ5ODFWMTcuNDA0N0MyNy4yMzY0IDE2LjkxNzIgMjYuODM3NSAxNi41MTgzIDI2LjM1IDE2LjUxODNDMjUuMjUwOSAxNi41MTgzIDI0LjE3ODQgMTYuMzQxIDIzLjE4NTcgMTYuMDEzMUMyMi44NzU1IDE1LjkwNjggMjIuNTIwOSAxNS45ODY1IDIyLjI4MTYgMTYuMjI1OEwyMC4zMzE2IDE4LjE3NTlaTTI1Ljg2MjUgNS40MjEwM0wyNi40OTE4IDYuMDQxNDlMMjAuODk4OSAxMS42MzQ1SDI0LjU3NzNWMTIuNTIwOUgxOS4yNTkxVjcuMjAyNjRIMjAuMTQ1NVYxMS4wMDUxTDI1Ljg2MjUgNS40MjEwM1pcIlxyXG4gICAgICAgIGZpbGw9XCIjNDA3QkZGXCJcclxuICAgICAgLz5cclxuICAgIDwvc3ZnPlxyXG4gICk7XHJcbn07XHJcblxyXG5pbXBvcnQgJy4vc3R5bGUuc2Nzcyc7XHJcbmltcG9ydCAnLi9lZGl0b3Iuc2Nzcyc7XHJcblxyXG5jbGFzcyBFZGl0QmxvY2sgZXh0ZW5kcyBDb21wb25lbnQge1xyXG4gIGNvbnN0cnVjdG9yKHByb3BzKSB7XHJcbiAgICBzdXBlcihwcm9wcyk7XHJcbiAgfVxyXG5cclxuICBjb21wb25lbnREaWRNb3VudCgpIHtcclxuICAgIGNvbnN0IHtcclxuICAgICAgc2V0QXR0cmlidXRlcyxcclxuICAgICAgYXR0cmlidXRlczogeyBuYW1lLCBkb21haW4sIGZyb21HbG9iYWwgfSxcclxuICAgIH0gPSB0aGlzLnByb3BzO1xyXG4gICAgY29uc3QgX25ld05hbWUgPSBNYXRoLnJhbmRvbSgpLnRvU3RyaW5nKDM2KS5zdWJzdHJpbmcoMiwgMTUpO1xyXG4gICAgaWYgKCFuYW1lKSB7XHJcbiAgICAgIHNldEF0dHJpYnV0ZXMoeyBuYW1lOiBfbmV3TmFtZSB9KTtcclxuICAgIH1cclxuXHJcbiAgICBpZiggIWRvbWFpbiApe1xyXG4gICAgICBzZXRBdHRyaWJ1dGVzKHsgZG9tYWluOiBqaXRzaS5kb21haW4gfSk7XHJcbiAgICB9XHJcblxyXG4gIH1cclxuXHJcbiAgcmVuZGVyKCkge1xyXG4gICAgY29uc3QgeyBhdHRyaWJ1dGVzLCBzZXRBdHRyaWJ1dGVzIH0gPSB0aGlzLnByb3BzO1xyXG5cclxuICAgIGNvbnN0IHtcclxuICAgICAgbmFtZSxcclxuICAgICAgZG9tYWluLFxyXG4gICAgICBmcm9tR2xvYmFsLFxyXG4gICAgICB3aWR0aCxcclxuICAgICAgaGVpZ2h0LFxyXG4gICAgICBhdWRpb011dGVkLFxyXG4gICAgICB2aWRlb011dGVkLFxyXG4gICAgICBzY3JlZW5TaGFyaW5nLFxyXG4gICAgICBpbnZpdGUsXHJcbiAgICB9ID0gYXR0cmlidXRlcztcclxuXHJcbiAgICByZXR1cm4gKFxyXG4gICAgICA8RnJhZ21lbnQ+XHJcbiAgICAgICAgPEluc3BlY3RvckNvbnRyb2xzPlxyXG4gICAgICAgICAgPFBhbmVsQm9keSB0aXRsZT17X18oJ1NldHRpbmdzJyl9IGluaXRpYWxPcGVuPXt0cnVlfT5cclxuICAgICAgICAgICAgPFRleHRDb250cm9sXHJcbiAgICAgICAgICAgICAgbGFiZWw9e19fKCdOYW1lJyl9XHJcbiAgICAgICAgICAgICAgdmFsdWU9e25hbWV9XHJcbiAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyBuYW1lOiB2YWwgfSl9XHJcbiAgICAgICAgICAgIC8+XHJcblxyXG4gICAgICAgICAgICA8VGV4dENvbnRyb2xcclxuICAgICAgICAgICAgICBsYWJlbD17X18oJ0RvbWFpbicpfVxyXG4gICAgICAgICAgICAgIHZhbHVlPXtkb21haW59XHJcbiAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyBkb21haW46IHZhbCB9KX1cclxuICAgICAgICAgICAgICBkaXNhYmxlZD17dHJ1ZX1cclxuICAgICAgICAgICAgLz5cclxuXHJcbiAgICAgICAgICAgIDxDaGVja2JveENvbnRyb2xcclxuICAgICAgICAgICAgICBsYWJlbD17X18oJ0NvbmZpZyBmcm9tIGdsb2JhbCcpfVxyXG4gICAgICAgICAgICAgIGNoZWNrZWQ9e2Zyb21HbG9iYWx9XHJcbiAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHtcclxuICAgICAgICAgICAgICAgIHNldEF0dHJpYnV0ZXMoeyBmcm9tR2xvYmFsOiB2YWwgfSk7XHJcbiAgICAgICAgICAgICAgICBpZiAoIWZyb21HbG9iYWwpIHtcclxuICAgICAgICAgICAgICAgICAgc2V0QXR0cmlidXRlcyh7XHJcbiAgICAgICAgICAgICAgICAgICAgd2lkdGg6IHBhcnNlSW50KGppdHNpLm1lZXRpbmdfd2lkdGgpLFxyXG4gICAgICAgICAgICAgICAgICAgIGhlaWdodDogcGFyc2VJbnQoaml0c2kubWVldGluZ19oZWlnaHQpLFxyXG4gICAgICAgICAgICAgICAgICAgIGF1ZGlvTXV0ZWQ6IHBhcnNlSW50KGppdHNpLnN0YXJ0d2l0aGF1ZGlvbXV0ZWQpXHJcbiAgICAgICAgICAgICAgICAgICAgICA/IHRydWVcclxuICAgICAgICAgICAgICAgICAgICAgIDogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgdmlkZW9NdXRlZDogcGFyc2VJbnQoaml0c2kuc3RhcnR3aXRodmlkZW9tdXRlZClcclxuICAgICAgICAgICAgICAgICAgICAgID8gdHJ1ZVxyXG4gICAgICAgICAgICAgICAgICAgICAgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICBzY3JlZW5TaGFyaW5nOiBwYXJzZUludChqaXRzaS5zdGFydHNjcmVlbnNoYXJpbmcpXHJcbiAgICAgICAgICAgICAgICAgICAgICA/IHRydWVcclxuICAgICAgICAgICAgICAgICAgICAgIDogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgaW52aXRlOiBwYXJzZUludChqaXRzaS5pbnZpdGUpID8gdHJ1ZSA6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICB9fVxyXG4gICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICB7IWZyb21HbG9iYWwgJiYgKFxyXG4gICAgICAgICAgICAgIDxkaXY+XHJcbiAgICAgICAgICAgICAgICA8UmFuZ2VDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnV2lkdGgnKX1cclxuICAgICAgICAgICAgICAgICAgdmFsdWU9e3dpZHRofVxyXG4gICAgICAgICAgICAgICAgICBvbkNoYW5nZT17KHZhbCkgPT4gc2V0QXR0cmlidXRlcyh7IHdpZHRoOiB2YWwgfSl9XHJcbiAgICAgICAgICAgICAgICAgIG1pbj17MTAwfVxyXG4gICAgICAgICAgICAgICAgICBtYXg9ezIwMDB9XHJcbiAgICAgICAgICAgICAgICAgIHN0ZXA9ezEwfVxyXG4gICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgIDxSYW5nZUNvbnRyb2xcclxuICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKCdIZWlnaHQnKX1cclxuICAgICAgICAgICAgICAgICAgdmFsdWU9e2hlaWdodH1cclxuICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyBoZWlnaHQ6IHZhbCB9KX1cclxuICAgICAgICAgICAgICAgICAgbWluPXsxMDB9XHJcbiAgICAgICAgICAgICAgICAgIG1heD17MjAwMH1cclxuICAgICAgICAgICAgICAgICAgc3RlcD17MTB9XHJcbiAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ1N0YXJ0IHdpdGggYXVkaW8gbXV0ZWQnKX1cclxuICAgICAgICAgICAgICAgICAgY2hlY2tlZD17YXVkaW9NdXRlZH1cclxuICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyBhdWRpb011dGVkOiB2YWwgfSl9XHJcbiAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ1N0YXJ0IHdpdGggdmlkZW8gbXV0ZWQnKX1cclxuICAgICAgICAgICAgICAgICAgY2hlY2tlZD17dmlkZW9NdXRlZH1cclxuICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyB2aWRlb011dGVkOiB2YWwgfSl9XHJcbiAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ1N0YXJ0IHdpdGggc2NyZWVuIHNoYXJpbmcnKX1cclxuICAgICAgICAgICAgICAgICAgY2hlY2tlZD17c2NyZWVuU2hhcmluZ31cclxuICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyh2YWwpID0+IHNldEF0dHJpYnV0ZXMoeyBzY3JlZW5TaGFyaW5nOiB2YWwgfSl9XHJcbiAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ0VuYWJsZSBJbnZpdGluZycpfVxyXG4gICAgICAgICAgICAgICAgICBjaGVja2VkPXtpbnZpdGV9XHJcbiAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsodmFsKSA9PiBzZXRBdHRyaWJ1dGVzKHsgaW52aXRlOiB2YWwgfSl9XHJcbiAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgICApfVxyXG4gICAgICAgICAgPC9QYW5lbEJvZHk+XHJcbiAgICAgICAgPC9JbnNwZWN0b3JDb250cm9scz5cclxuICAgICAgICA8ZGl2XHJcbiAgICAgICAgICBjbGFzc05hbWU9XCJqaXRzaS13cmFwcGVyXCJcclxuICAgICAgICAgIGRhdGEtbmFtZT17bmFtZX1cclxuICAgICAgICAgIGRhdGEtZG9tYWluPXtkb21haW59XHJcbiAgICAgICAgICBkYXRhLXdpZHRoPXt3aWR0aH1cclxuICAgICAgICAgIGRhdGEtaGVpZ2h0PXtoZWlnaHR9XHJcbiAgICAgICAgICBkYXRhLW11dGU9e2F1ZGlvTXV0ZWR9XHJcbiAgICAgICAgICBkYXRhLXZpZGVvbXV0ZT17dmlkZW9NdXRlZH1cclxuICAgICAgICAgIGRhdGEtc2NyZWVuPXtzY3JlZW5TaGFyaW5nfVxyXG4gICAgICAgICAgZGF0YS1pbnZpdGU9e2ludml0ZX1cclxuICAgICAgICA+e2BodHRwczovLyR7ZG9tYWlufS8ke25hbWV9YH08L2Rpdj5cclxuICAgICAgPC9GcmFnbWVudD5cclxuICAgICk7XHJcbiAgfVxyXG59XHJcblxyXG5yZWdpc3RlckJsb2NrVHlwZSgnaml0c2ktbWVldC13cC9qaXRzaS1tZWV0Jywge1xyXG4gIHRpdGxlOiBfXygnSml0c2kgTWVldCcsICdqaXRzaS1tZWV0LXdwJyksXHJcbiAgaWNvbjogYmxvY2tJY29uLFxyXG4gIGNhdGVnb3J5OiAnZW1iZWQnLFxyXG4gIGtleXdvcmRzOiBbXHJcbiAgICBfXygnaml0c2knLCAnaml0c2ktbWVldC13cCcpLFxyXG4gICAgX18oJ21lZXRpbmcnLCAnaml0c2ktbWVldC13cCcpLFxyXG4gICAgX18oJ3ZpZGVvJywgJ2ppdHNpLW1lZXQtd3AnKSxcclxuICAgIF9fKCdjb25mZXJlbmNlJywgJ2ppdHNpLW1lZXQtd3AnKSxcclxuICAgIF9fKCd6b29tJywgJ2ppdHNpLW1lZXQtd3AnKSxcclxuICBdLFxyXG4gIGF0dHJpYnV0ZXM6IHtcclxuICAgIG5hbWU6IHtcclxuICAgICAgdHlwZTogJ3N0cmluZycsXHJcbiAgICAgIGRlZmF1bHQ6ICcnLFxyXG4gICAgfSxcclxuICAgIGRvbWFpbjoge1xyXG4gICAgICB0eXBlOiAnc3RyaW5nJyxcclxuICAgICAgZGVmYXVsdDogJycsXHJcbiAgICB9LFxyXG4gICAgd2lkdGg6IHtcclxuICAgICAgdHlwZTogJ251bWJlcicsXHJcbiAgICAgIGRlZmF1bHQ6IDEwODAsXHJcbiAgICB9LFxyXG4gICAgaGVpZ2h0OiB7XHJcbiAgICAgIHR5cGU6ICdudW1iZXInLFxyXG4gICAgICBkZWZhdWx0OiA3MjAsXHJcbiAgICB9LFxyXG4gICAgZnJvbUdsb2JhbDoge1xyXG4gICAgICB0eXBlOiAnYm9vbGVhbicsXHJcbiAgICAgIGRlZmF1bHQ6IGZhbHNlLFxyXG4gICAgfSxcclxuICAgIGF1ZGlvTXV0ZWQ6IHtcclxuICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICBkZWZhdWx0OiBmYWxzZSxcclxuICAgIH0sXHJcbiAgICB2aWRlb011dGVkOiB7XHJcbiAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgZGVmYXVsdDogdHJ1ZSxcclxuICAgIH0sXHJcbiAgICBzY3JlZW5TaGFyaW5nOiB7XHJcbiAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgZGVmYXVsdDogZmFsc2UsXHJcbiAgICB9LFxyXG4gICAgaW52aXRlOiB7XHJcbiAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgZGVmYXVsdDogdHJ1ZSxcclxuICAgIH0sXHJcbiAgfSxcclxuICBlZGl0OiBFZGl0QmxvY2ssXHJcbiAgc2F2ZTogZnVuY3Rpb24gKHByb3BzKSB7XHJcbiAgICBjb25zdCB7XHJcbiAgICAgIG5hbWUsXHJcbiAgICAgIGRvbWFpbixcclxuICAgICAgd2lkdGgsXHJcbiAgICAgIGhlaWdodCxcclxuICAgICAgYXVkaW9NdXRlZCxcclxuICAgICAgdmlkZW9NdXRlZCxcclxuICAgICAgc2NyZWVuU2hhcmluZyxcclxuICAgICAgaW52aXRlLFxyXG4gICAgfSA9IHByb3BzLmF0dHJpYnV0ZXM7XHJcblxyXG4gICAgcmV0dXJuIChcclxuICAgICAgPGRpdlxyXG4gICAgICAgIGNsYXNzTmFtZT1cImppdHNpLXdyYXBwZXJcIlxyXG4gICAgICAgIGRhdGEtbmFtZT17bmFtZX1cclxuICAgICAgICBkYXRhLWRvbWFpbj17ZG9tYWlufVxyXG4gICAgICAgIGRhdGEtd2lkdGg9e3dpZHRofVxyXG4gICAgICAgIGRhdGEtaGVpZ2h0PXtoZWlnaHR9XHJcbiAgICAgICAgZGF0YS1tdXRlPXthdWRpb011dGVkfVxyXG4gICAgICAgIGRhdGEtdmlkZW9tdXRlPXt2aWRlb011dGVkfVxyXG4gICAgICAgIGRhdGEtc2NyZWVuPXtzY3JlZW5TaGFyaW5nfVxyXG4gICAgICAgIGRhdGEtaW52aXRlPXtpbnZpdGV9XHJcbiAgICAgICAgc3R5bGU9e3tcclxuICAgICAgICAgIHdpZHRoOiBgJHt3aWR0aH1weGAsXHJcbiAgICAgICAgfX1cclxuICAgICAgPjwvZGl2PlxyXG4gICAgKTtcclxuICB9LFxyXG59KTtcclxuIiwiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW4iLCIvLyByZW1vdmVkIGJ5IGV4dHJhY3QtdGV4dC13ZWJwYWNrLXBsdWdpbiJdLCJzb3VyY2VSb290IjoiIn0=