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
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

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
    CheckboxControl = _wp$components.CheckboxControl,
    ToggleControl = _wp$components.ToggleControl,
    SelectControl = _wp$components.SelectControl;
var withSelect = wp.data.withSelect;

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
    var _this;

    _classCallCheck(this, EditBlock);

    _this = _super.call(this, props);
    _this.state = {
      postArr: []
    };
    return _this;
  }

  _createClass(EditBlock, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this$props = this.props,
          setAttributes = _this$props.setAttributes,
          _this$props$attribute = _this$props.attributes,
          name = _this$props$attribute.name,
          fromGlobal = _this$props$attribute.fromGlobal;

      var _newName = Math.random().toString(36).substring(2, 15);

      if (!name) {
        setAttributes({
          name: _newName
        });
      }
    }
  }, {
    key: "toggleFromPost",
    value: function toggleFromPost() {
      if (this.props.posts && this.state.postArr.length < 1) {
        var options = [];
        this.props.posts.forEach(function (post) {
          options.push({
            value: post.id,
            label: post.title.rendered
          });
        });
        this.setState({
          postArr: options
        });
      }

      var _this$props2 = this.props,
          setAttributes = _this$props2.setAttributes,
          _this$props2$attribut = _this$props2.attributes,
          formPosts = _this$props2$attribut.formPosts,
          postId = _this$props2$attribut.postId;
      setAttributes({
        formPosts: !formPosts
      });

      if (!postId && this.props.posts.length > 0) {
        setAttributes({
          postId: this.props.posts[0].id,
          postTitle: this.props.posts[0].title.rendered
        });
      }
    }
  }, {
    key: "previewMock",
    value: function previewMock() {
      return wp.element.createElement("div", {
        className: "jitsi-preview-people-mock"
      }, wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/01.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/02.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/03.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/04.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/05.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/06.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/07.png)")
        }
      })), wp.element.createElement("div", null, wp.element.createElement("div", {
        style: {
          backgroundImage: "url(".concat(jitsi_pro.plugin_url, "assets/img/08.png)")
        }
      })));
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;

      var _this$props3 = this.props,
          attributes = _this$props3.attributes,
          setAttributes = _this$props3.setAttributes,
          posts = _this$props3.posts;
      var formPosts = attributes.formPosts,
          postId = attributes.postId,
          name = attributes.name,
          width = attributes.width,
          height = attributes.height,
          fromGlobal = attributes.fromGlobal,
          enablewelcomepage = attributes.enablewelcomepage,
          startaudioonly = attributes.startaudioonly,
          startaudiomuted = attributes.startaudiomuted,
          startwithaudiomuted = attributes.startwithaudiomuted,
          startsilent = attributes.startsilent,
          resolution = attributes.resolution,
          maxfullresolutionparticipant = attributes.maxfullresolutionparticipant,
          disablesimulcast = attributes.disablesimulcast,
          startvideomuted = attributes.startvideomuted,
          startwithvideomuted = attributes.startwithvideomuted,
          startscreensharing = attributes.startscreensharing,
          filerecordingsenabled = attributes.filerecordingsenabled,
          transcribingenabled = attributes.transcribingenabled,
          livestreamingenabled = attributes.livestreamingenabled,
          invite = attributes.invite;
      return wp.element.createElement(Fragment, null, wp.element.createElement(InspectorControls, null, wp.element.createElement(PanelBody, {
        title: __('Settings'),
        initialOpen: true
      }, wp.element.createElement(ToggleControl, {
        label: __("From Post?"),
        checked: formPosts,
        onChange: function onChange() {
          return _this2.toggleFromPost();
        }
      }), formPosts && wp.element.createElement(SelectControl, {
        label: __("Select Post"),
        value: postId,
        options: this.state.postArr,
        onChange: function onChange(val) {
          return setAttributes({
            postId: val,
            postTitle: posts.find(function (obj) {
              return obj.id == val;
            }).title.rendered
          });
        }
      }), !formPosts && wp.element.createElement(TextControl, {
        label: __('Name'),
        value: name,
        onChange: function onChange(val) {
          return setAttributes({
            name: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Config from global'),
        checked: fromGlobal,
        onChange: function onChange(val) {
          setAttributes({
            fromGlobal: val
          });

          if (!fromGlobal) {
            setAttributes({
              width: parseInt(jitsi_pro.meeting_width),
              height: parseInt(jitsi_pro.meeting_height),
              enablewelcomepage: parseInt(jitsi_pro.enablewelcomepage) ? true : false,
              startaudioonly: parseInt(jitsi_pro.startaudioonly) ? true : false,
              startaudiomuted: parseInt(jitsi_pro.startaudiomuted) ? parseInt(jitsi_pro.startaudiomuted) : 10,
              startwithaudiomuted: parseInt(jitsi_pro.startwithaudiomuted) ? true : false,
              startsilent: parseInt(jitsi_pro.startsilent) ? true : false,
              resolution: parseInt(jitsi_pro.resolution) ? parseInt(jitsi_pro.resolution) : 720,
              maxfullresolutionparticipant: parseInt(jitsi_pro.maxfullresolutionparticipant) ? parseInt(jitsi_pro.maxfullresolutionparticipant) : 2,
              disablesimulcast: parseInt(jitsi_pro.disablesimulcast) ? true : false,
              startvideomuted: parseInt(jitsi_pro.startvideomuted) ? true : false,
              startwithvideomuted: parseInt(jitsi_pro.startwithvideomuted) ? parseInt(jitsi_pro.startwithvideomuted) : 10,
              startscreensharing: parseInt(jitsi_pro.startscreensharing) ? true : false,
              filerecordingsenabled: parseInt(jitsi_pro.filerecordingsenabled) ? true : false,
              transcribingenabled: parseInt(jitsi_pro.transcribingenabled) ? true : false,
              livestreamingenabled: parseInt(jitsi_pro.livestreamingenabled) ? true : false,
              invite: parseInt(jitsi_pro.invite) ? true : false
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
        label: __('Welcome Page'),
        checked: enablewelcomepage,
        onChange: function onChange(val) {
          return setAttributes({
            enablewelcomepage: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start Audio Only'),
        checked: startaudioonly,
        onChange: function onChange(val) {
          return setAttributes({
            startaudioonly: val
          });
        }
      }), wp.element.createElement(RangeControl, {
        label: __('Audio Muted After'),
        value: startaudiomuted,
        onChange: function onChange(val) {
          return setAttributes({
            startaudiomuted: val
          });
        },
        min: 0,
        max: 20,
        step: 1
      }), wp.element.createElement(CheckboxControl, {
        label: __('Yourself Muted'),
        checked: startwithaudiomuted,
        onChange: function onChange(val) {
          return setAttributes({
            startwithaudiomuted: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start Silent'),
        checked: startsilent,
        onChange: function onChange(val) {
          return setAttributes({
            startsilent: val
          });
        }
      }), wp.element.createElement(SelectControl, {
        label: __("Resolution"),
        value: resolution,
        options: [{
          label: __('480p'),
          value: 480
        }, {
          label: __('720p'),
          value: 720
        }, {
          label: __('1080p'),
          value: 1080
        }, {
          label: __('1440p'),
          value: 1440
        }, {
          label: __('2160p'),
          value: 2160
        }, {
          label: __('4320p'),
          value: 4320
        }],
        onChange: function onChange(val) {
          return setAttributes({
            resolution: val
          });
        }
      }), wp.element.createElement(RangeControl, {
        label: __('Max Full Resolution'),
        value: maxfullresolutionparticipant,
        onChange: function onChange(val) {
          return setAttributes({
            maxfullresolutionparticipant: val
          });
        },
        min: 0,
        max: 20,
        step: 1
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start Video Muted'),
        checked: startvideomuted,
        onChange: function onChange(val) {
          return setAttributes({
            startvideomuted: val
          });
        }
      }), wp.element.createElement(RangeControl, {
        label: __('Video Muted After'),
        value: startwithvideomuted,
        onChange: function onChange(val) {
          return setAttributes({
            startwithvideomuted: val
          });
        },
        min: 0,
        max: 50,
        step: 1
      }), wp.element.createElement(CheckboxControl, {
        label: __('Start Screen Sharing'),
        checked: startscreensharing,
        onChange: function onChange(val) {
          return setAttributes({
            startscreensharing: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Enable Recording'),
        checked: filerecordingsenabled,
        onChange: function onChange(val) {
          return setAttributes({
            filerecordingsenabled: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Enable Transcription'),
        checked: transcribingenabled,
        onChange: function onChange(val) {
          return setAttributes({
            transcribingenabled: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Enable Livestream'),
        checked: livestreamingenabled,
        onChange: function onChange(val) {
          return setAttributes({
            livestreamingenabled: val
          });
        }
      }), wp.element.createElement(CheckboxControl, {
        label: __('Simulcast'),
        checked: disablesimulcast,
        onChange: function onChange(val) {
          return setAttributes({
            disablesimulcast: val
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
        id: "meeting-ui-preview",
        className: "preview-success preview-block"
      }, this.previewMock()));
    }
  }]);

  return EditBlock;
}(Component);

registerBlockType('jitsi-pro/jitsi-pro', {
  title: __('Jitsi Pro', 'jitsi-pro'),
  icon: blockIcon,
  category: 'embed',
  keywords: [__('jitsi', 'jitsi-pro'), __('meeting', 'jitsi-pro'), __('video', 'jitsi-pro'), __('conference', 'jitsi-pro'), __('zoom', 'jitsi-pro')],
  attributes: {
    formPosts: {
      type: 'boolean',
      "default": false
    },
    postId: {
      type: 'number',
      "default": ''
    },
    postTitle: {
      type: 'string',
      "default": ''
    },
    name: {
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
    enablewelcomepage: {
      type: 'boolean',
      "default": true
    },
    startaudioonly: {
      type: 'boolean',
      "default": false
    },
    startaudiomuted: {
      type: 'number',
      "default": 10
    },
    startwithaudiomuted: {
      type: 'boolean',
      "default": false
    },
    startsilent: {
      type: 'boolean',
      "default": false
    },
    resolution: {
      type: 'number',
      "default": 720
    },
    maxfullresolutionparticipant: {
      type: 'number',
      "default": 2
    },
    startvideomuted: {
      type: 'boolean',
      "default": true
    },
    startwithvideomuted: {
      type: 'number',
      "default": 10
    },
    startscreensharing: {
      type: 'boolean',
      "default": false
    },
    filerecordingsenabled: {
      type: 'boolean',
      "default": false
    },
    transcribingenabled: {
      type: 'boolean',
      "default": false
    },
    livestreamingenabled: {
      type: 'boolean',
      "default": false
    },
    disablesimulcast: {
      type: 'boolean',
      "default": false
    },
    invite: {
      type: 'boolean',
      "default": true
    }
  },
  edit: withSelect(function (select) {
    return {
      posts: select('core').getEntityRecords('postType', 'meeting', {
        per_page: -1
      })
    };
  })(EditBlock),
  save: function save(props) {
    var _props$attributes = props.attributes,
        formPosts = _props$attributes.formPosts,
        postId = _props$attributes.postId,
        postTitle = _props$attributes.postTitle,
        name = _props$attributes.name,
        width = _props$attributes.width,
        height = _props$attributes.height,
        enablewelcomepage = _props$attributes.enablewelcomepage,
        startaudioonly = _props$attributes.startaudioonly,
        startaudiomuted = _props$attributes.startaudiomuted,
        startwithaudiomuted = _props$attributes.startwithaudiomuted,
        startsilent = _props$attributes.startsilent,
        resolution = _props$attributes.resolution,
        maxfullresolutionparticipant = _props$attributes.maxfullresolutionparticipant,
        disablesimulcast = _props$attributes.disablesimulcast,
        startvideomuted = _props$attributes.startvideomuted,
        startwithvideomuted = _props$attributes.startwithvideomuted,
        startscreensharing = _props$attributes.startscreensharing,
        filerecordingsenabled = _props$attributes.filerecordingsenabled,
        transcribingenabled = _props$attributes.transcribingenabled,
        livestreamingenabled = _props$attributes.livestreamingenabled,
        invite = _props$attributes.invite;
    return wp.element.createElement("div", null, wp.element.createElement("div", {
      className: "jitsi-wrapper",
      "data-name": formPosts ? postTitle : name,
      "data-width": width,
      "data-height": height,
      "data-startaudioonly": startaudioonly,
      "data-startaudiomuted": startaudiomuted,
      "data-startwithaudiomuted": startwithaudiomuted,
      "data-startsilent": startsilent,
      "data-resolution": resolution,
      "data-maxfullresolutionparticipant": maxfullresolutionparticipant,
      "data-disablesimulcast": disablesimulcast,
      "data-startvideomuted": startvideomuted,
      "data-startwithvideomuted": startwithvideomuted,
      "data-startscreensharing": startscreensharing,
      "data-filerecordingsenabled": filerecordingsenabled,
      "data-transcribingenabled": transcribingenabled,
      "data-livestreamingenabled": livestreamingenabled,
      "data-enablewelcomepage": enablewelcomepage,
      "data-invite": invite,
      style: {
        width: "".concat(width, "px")
      }
    }));
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vYmxvY2tzL3NyYy9ibG9ja3MuanMiLCJ3ZWJwYWNrOi8vLy4vYmxvY2tzL3NyYy9lZGl0b3Iuc2NzcyIsIndlYnBhY2s6Ly8vLi9ibG9ja3Mvc3JjL3N0eWxlLnNjc3MiXSwibmFtZXMiOlsiSW5zcGVjdG9yQ29udHJvbHMiLCJ3cCIsImVkaXRvciIsInJlZ2lzdGVyQmxvY2tUeXBlIiwiYmxvY2tzIiwiX18iLCJpMThuIiwiZWxlbWVudCIsIkNvbXBvbmVudCIsIkZyYWdtZW50IiwiY29tcG9uZW50cyIsIlBhbmVsQm9keSIsIlRleHRDb250cm9sIiwiUmFuZ2VDb250cm9sIiwiQ2hlY2tib3hDb250cm9sIiwiVG9nZ2xlQ29udHJvbCIsIlNlbGVjdENvbnRyb2wiLCJ3aXRoU2VsZWN0IiwiZGF0YSIsImJsb2NrSWNvbiIsIkVkaXRCbG9jayIsInByb3BzIiwic3RhdGUiLCJwb3N0QXJyIiwic2V0QXR0cmlidXRlcyIsImF0dHJpYnV0ZXMiLCJuYW1lIiwiZnJvbUdsb2JhbCIsIl9uZXdOYW1lIiwiTWF0aCIsInJhbmRvbSIsInRvU3RyaW5nIiwic3Vic3RyaW5nIiwicG9zdHMiLCJsZW5ndGgiLCJvcHRpb25zIiwiZm9yRWFjaCIsInBvc3QiLCJwdXNoIiwidmFsdWUiLCJpZCIsImxhYmVsIiwidGl0bGUiLCJyZW5kZXJlZCIsInNldFN0YXRlIiwiZm9ybVBvc3RzIiwicG9zdElkIiwicG9zdFRpdGxlIiwiYmFja2dyb3VuZEltYWdlIiwiaml0c2lfcHJvIiwicGx1Z2luX3VybCIsIndpZHRoIiwiaGVpZ2h0IiwiZW5hYmxld2VsY29tZXBhZ2UiLCJzdGFydGF1ZGlvb25seSIsInN0YXJ0YXVkaW9tdXRlZCIsInN0YXJ0d2l0aGF1ZGlvbXV0ZWQiLCJzdGFydHNpbGVudCIsInJlc29sdXRpb24iLCJtYXhmdWxscmVzb2x1dGlvbnBhcnRpY2lwYW50IiwiZGlzYWJsZXNpbXVsY2FzdCIsInN0YXJ0dmlkZW9tdXRlZCIsInN0YXJ0d2l0aHZpZGVvbXV0ZWQiLCJzdGFydHNjcmVlbnNoYXJpbmciLCJmaWxlcmVjb3JkaW5nc2VuYWJsZWQiLCJ0cmFuc2NyaWJpbmdlbmFibGVkIiwibGl2ZXN0cmVhbWluZ2VuYWJsZWQiLCJpbnZpdGUiLCJ0b2dnbGVGcm9tUG9zdCIsInZhbCIsImZpbmQiLCJvYmoiLCJwYXJzZUludCIsIm1lZXRpbmdfd2lkdGgiLCJtZWV0aW5nX2hlaWdodCIsInByZXZpZXdNb2NrIiwiaWNvbiIsImNhdGVnb3J5Iiwia2V5d29yZHMiLCJ0eXBlIiwiZWRpdCIsInNlbGVjdCIsImdldEVudGl0eVJlY29yZHMiLCJwZXJfcGFnZSIsInNhdmUiXSwibWFwcGluZ3MiOiI7UUFBQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTs7O1FBR0E7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLDBDQUEwQyxnQ0FBZ0M7UUFDMUU7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQSx3REFBd0Qsa0JBQWtCO1FBQzFFO1FBQ0EsaURBQWlELGNBQWM7UUFDL0Q7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBLHlDQUF5QyxpQ0FBaUM7UUFDMUUsZ0hBQWdILG1CQUFtQixFQUFFO1FBQ3JJO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0EsMkJBQTJCLDBCQUEwQixFQUFFO1FBQ3ZELGlDQUFpQyxlQUFlO1FBQ2hEO1FBQ0E7UUFDQTs7UUFFQTtRQUNBLHNEQUFzRCwrREFBK0Q7O1FBRXJIO1FBQ0E7OztRQUdBO1FBQ0E7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNsRkEsSUFBUUEsaUJBQVIsR0FBOEJDLEVBQUUsQ0FBQ0MsTUFBakMs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QkFiVjtBQUFBLFFBY1VDLGdCQWRWLHFCQWNVQSxnQkFkVjtBQUFBLFFBZVVDLGVBZlYscUJBZVVBLGVBZlY7QUFBQSxRQWdCVUMsbUJBaEJWLHFCQWdCVUEsbUJBaEJWO0FBQUEsUUFpQlVDLGtCQWpCVixxQkFpQlVBLGtCQWpCVjtBQUFBLFFBa0JVQyxxQkFsQlYscUJBa0JVQSxxQkFsQlY7QUFBQSxRQW1CVUMsbUJBbkJWLHFCQW1CVUEsbUJBbkJWO0FBQUEsUUFvQlVDLG9CQXBCVixxQkFvQlVBLG9CQXBCVjtBQUFBLFFBcUJVQyxNQXJCVixxQkFxQlVBLE1BckJWO0FBd0JBLFdBQ1Usc0NBQ0k7QUFDSSxlQUFTLEVBQUMsZUFEZDtBQUVJLG1CQUFXdEIsU0FBUyxHQUFHRSxTQUFILEdBQWVyQixJQUZ2QztBQUdJLG9CQUFZeUIsS0FIaEI7QUFJSSxxQkFBYUMsTUFKakI7QUFLSSw2QkFBcUJFLGNBTHpCO0FBTUksOEJBQXNCQyxlQU4xQjtBQU9JLGtDQUEwQkMsbUJBUDlCO0FBUUksMEJBQWtCQyxXQVJ0QjtBQVNJLHlCQUFpQkMsVUFUckI7QUFVSSwyQ0FBbUNDLDRCQVZ2QztBQVdJLCtCQUF1QkMsZ0JBWDNCO0FBWUksOEJBQXNCQyxlQVoxQjtBQWFJLGtDQUEwQkMsbUJBYjlCO0FBY0ksaUNBQXlCQyxrQkFkN0I7QUFlSSxvQ0FBNEJDLHFCQWZoQztBQWdCSSxrQ0FBMEJDLG1CQWhCOUI7QUFpQkksbUNBQTJCQyxvQkFqQi9CO0FBa0JJLGdDQUF3QmIsaUJBbEI1QjtBQW1CSSxxQkFBYWMsTUFuQmpCO0FBb0JJLFdBQUssRUFBRTtBQUNIaEIsYUFBSyxZQUFLQSxLQUFMO0FBREY7QUFwQlgsTUFESixDQURWO0FBNEJBO0FBL0p1QyxDQUF4QixDQUFqQixDOzs7Ozs7Ozs7OztBQ2pSQSx5Qzs7Ozs7Ozs7Ozs7QUNBQSx5QyIsImZpbGUiOiJibG9ja3MuYnVpbGQuanMiLCJzb3VyY2VzQ29udGVudCI6WyIgXHQvLyBUaGUgbW9kdWxlIGNhY2hlXG4gXHR2YXIgaW5zdGFsbGVkTW9kdWxlcyA9IHt9O1xuXG4gXHQvLyBUaGUgcmVxdWlyZSBmdW5jdGlvblxuIFx0ZnVuY3Rpb24gX193ZWJwYWNrX3JlcXVpcmVfXyhtb2R1bGVJZCkge1xuXG4gXHRcdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuIFx0XHRpZihpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSkge1xuIFx0XHRcdHJldHVybiBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXS5leHBvcnRzO1xuIFx0XHR9XG4gXHRcdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG4gXHRcdHZhciBtb2R1bGUgPSBpbnN0YWxsZWRNb2R1bGVzW21vZHVsZUlkXSA9IHtcbiBcdFx0XHRpOiBtb2R1bGVJZCxcbiBcdFx0XHRsOiBmYWxzZSxcbiBcdFx0XHRleHBvcnRzOiB7fVxuIFx0XHR9O1xuXG4gXHRcdC8vIEV4ZWN1dGUgdGhlIG1vZHVsZSBmdW5jdGlvblxuIFx0XHRtb2R1bGVzW21vZHVsZUlkXS5jYWxsKG1vZHVsZS5leHBvcnRzLCBtb2R1bGUsIG1vZHVsZS5leHBvcnRzLCBfX3dlYnBhY2tfcmVxdWlyZV9fKTtcblxuIFx0XHQvLyBGbGFnIHRoZSBtb2R1bGUgYXMgbG9hZGVkXG4gXHRcdG1vZHVsZS5sID0gdHJ1ZTtcblxuIFx0XHQvLyBSZXR1cm4gdGhlIGV4cG9ydHMgb2YgdGhlIG1vZHVsZVxuIFx0XHRyZXR1cm4gbW9kdWxlLmV4cG9ydHM7XG4gXHR9XG5cblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubSA9IG1vZHVsZXM7XG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlIGNhY2hlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmMgPSBpbnN0YWxsZWRNb2R1bGVzO1xuXG4gXHQvLyBkZWZpbmUgZ2V0dGVyIGZ1bmN0aW9uIGZvciBoYXJtb255IGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uZCA9IGZ1bmN0aW9uKGV4cG9ydHMsIG5hbWUsIGdldHRlcikge1xuIFx0XHRpZighX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIG5hbWUpKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIG5hbWUsIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBnZXR0ZXIgfSk7XG4gXHRcdH1cbiBcdH07XG5cbiBcdC8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbiBcdF9fd2VicGFja19yZXF1aXJlX18uciA9IGZ1bmN0aW9uKGV4cG9ydHMpIHtcbiBcdFx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG4gXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG4gXHRcdH1cbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbiBcdH07XG5cbiBcdC8vIGNyZWF0ZSBhIGZha2UgbmFtZXNwYWNlIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDE6IHZhbHVlIGlzIGEgbW9kdWxlIGlkLCByZXF1aXJlIGl0XG4gXHQvLyBtb2RlICYgMjogbWVyZ2UgYWxsIHByb3BlcnRpZXMgb2YgdmFsdWUgaW50byB0aGUgbnNcbiBcdC8vIG1vZGUgJiA0OiByZXR1cm4gdmFsdWUgd2hlbiBhbHJlYWR5IG5zIG9iamVjdFxuIFx0Ly8gbW9kZSAmIDh8MTogYmVoYXZlIGxpa2UgcmVxdWlyZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy50ID0gZnVuY3Rpb24odmFsdWUsIG1vZGUpIHtcbiBcdFx0aWYobW9kZSAmIDEpIHZhbHVlID0gX193ZWJwYWNrX3JlcXVpcmVfXyh2YWx1ZSk7XG4gXHRcdGlmKG1vZGUgJiA4KSByZXR1cm4gdmFsdWU7XG4gXHRcdGlmKChtb2RlICYgNCkgJiYgdHlwZW9mIHZhbHVlID09PSAnb2JqZWN0JyAmJiB2YWx1ZSAmJiB2YWx1ZS5fX2VzTW9kdWxlKSByZXR1cm4gdmFsdWU7XG4gXHRcdHZhciBucyA9IE9iamVjdC5jcmVhdGUobnVsbCk7XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18ucihucyk7XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShucywgJ2RlZmF1bHQnLCB7IGVudW1lcmFibGU6IHRydWUsIHZhbHVlOiB2YWx1ZSB9KTtcbiBcdFx0aWYobW9kZSAmIDIgJiYgdHlwZW9mIHZhbHVlICE9ICdzdHJpbmcnKSBmb3IodmFyIGtleSBpbiB2YWx1ZSkgX193ZWJwYWNrX3JlcXVpcmVfXy5kKG5zLCBrZXksIGZ1bmN0aW9uKGtleSkgeyByZXR1cm4gdmFsdWVba2V5XTsgfS5iaW5kKG51bGwsIGtleSkpO1xuIFx0XHRyZXR1cm4gbnM7XG4gXHR9O1xuXG4gXHQvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5uID0gZnVuY3Rpb24obW9kdWxlKSB7XG4gXHRcdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuIFx0XHRcdGZ1bmN0aW9uIGdldERlZmF1bHQoKSB7IHJldHVybiBtb2R1bGVbJ2RlZmF1bHQnXTsgfSA6XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0TW9kdWxlRXhwb3J0cygpIHsgcmV0dXJuIG1vZHVsZTsgfTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgJ2EnLCBnZXR0ZXIpO1xuIFx0XHRyZXR1cm4gZ2V0dGVyO1xuIFx0fTtcblxuIFx0Ly8gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSBmdW5jdGlvbihvYmplY3QsIHByb3BlcnR5KSB7IHJldHVybiBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqZWN0LCBwcm9wZXJ0eSk7IH07XG5cbiBcdC8vIF9fd2VicGFja19wdWJsaWNfcGF0aF9fXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnAgPSBcIlwiO1xuXG5cbiBcdC8vIExvYWQgZW50cnkgbW9kdWxlIGFuZCByZXR1cm4gZXhwb3J0c1xuIFx0cmV0dXJuIF9fd2VicGFja19yZXF1aXJlX18oX193ZWJwYWNrX3JlcXVpcmVfXy5zID0gXCIuL2Jsb2Nrcy9zcmMvYmxvY2tzLmpzXCIpO1xuIiwiY29uc3QgeyBJbnNwZWN0b3JDb250cm9scyB9ID0gd3AuZWRpdG9yO1xyXG5jb25zdCB7IHJlZ2lzdGVyQmxvY2tUeXBlIH0gPSB3cC5ibG9ja3M7XHJcbmNvbnN0IHsgX18gfSA9IHdwLmkxOG47XHJcbmNvbnN0IHsgQ29tcG9uZW50LCBGcmFnbWVudCB9ID0gd3AuZWxlbWVudDtcclxuY29uc3QgeyBQYW5lbEJvZHksIFRleHRDb250cm9sLCBSYW5nZUNvbnRyb2wsIENoZWNrYm94Q29udHJvbCwgVG9nZ2xlQ29udHJvbCwgU2VsZWN0Q29udHJvbCB9ID0gd3AuY29tcG9uZW50cztcclxuY29uc3QgeyB3aXRoU2VsZWN0IH0gPSB3cC5kYXRhO1xyXG5cclxuY29uc3QgYmxvY2tJY29uID0gKCkgPT4ge1xyXG5cdHJldHVybiAoXHJcblx0XHQ8c3ZnIHhtbG5zPVwiaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmdcIiBoZWlnaHQ9XCI0MFwiIHZpZXdCb3g9XCIwIDAgNDkgMjhcIiBmaWxsPVwibm9uZVwiPlxyXG4gICAgICAgICAgICA8cGF0aCBmaWxsLXJ1bGU9XCJldmVub2RkXCIgY2xpcC1ydWxlPVwiZXZlbm9kZFwiIGQ9XCJNMzQuNDc1NyAyMi4wNjE0VjE3LjI5NDFMNDMuMDMyMyAyMy40MDYxQzQzLjUzNjEgMjMuNzY1OSA0NC4xOTg3IDIzLjgxNCA0NC43NDkxIDIzLjUzMDdDNDUuMjk5NiAyMy4yNDc0IDQ1LjY0NTUgMjIuNjgwMyA0NS42NDU1IDIyLjA2MTJWNS41MzQ5MkM0NS42NDU1IDQuOTE1ODcgNDUuMjk5NiA0LjM0ODczIDQ0Ljc0OTEgNC4wNjU0NUM0NC4xOTg3IDMuNzgyMTkgNDMuNTM2MSAzLjgzMDMgNDMuMDMyMyA0LjE5MDEyTDM0LjQ3NTcgMTAuMzAyMVY1LjUzNTA0QzM0LjQ3NTcgMi42MTc0MSAzMS44Nzg0IDAuNTc3MTQ4IDI5LjA5OTggMC41NzcxNDhIOC42MjIzOUM1Ljg0Mzg3IDAuNTc3MTQ4IDMuMjQ2NTggMi42MTc0MSAzLjI0NjU4IDUuNTM1MDRWMjIuMDYxNEMzLjI0NjU4IDI0Ljk3OSA1Ljg0Mzg3IDI3LjAxOTMgOC42MjIzOSAyNy4wMTkzSDI5LjA5OThDMzEuODc4NCAyNy4wMTkzIDM0LjQ3NTcgMjQuOTc5IDM0LjQ3NTcgMjIuMDYxNFpNMjAuMzMxNiAxOC4xNzU5QzE3LjgyMzIgMTYuODkwNiAxNS43NjY4IDE0Ljg0MzEgMTQuNDkwNCAxMi4zMzQ3TDE2LjQ0MDQgMTAuMzg0N0MxNi42ODg2IDEwLjEzNjUgMTYuNzU5NiA5Ljc5MDgxIDE2LjY2MjEgOS40ODA1OUMxNi4zMzQxIDguNDg3ODQgMTYuMTU2OCA3LjQyNDIxIDE2LjE1NjggNi4zMTYyN0MxNi4xNTY4IDUuODI4NzYgMTUuNzU4IDUuNDI5OSAxNS4yNzA0IDUuNDI5OUgxMi4xNjgxQzExLjY4MDcgNS40Mjk5IDExLjI4MTggNS44Mjg3NiAxMS4yODE4IDYuMzE2MjdDMTEuMjgxOCAxNC42MzkzIDE4LjAyNyAyMS4zODQ1IDI2LjM1IDIxLjM4NDVDMjYuODM3NSAyMS4zODQ1IDI3LjIzNjQgMjAuOTg1NiAyNy4yMzY0IDIwLjQ5ODFWMTcuNDA0N0MyNy4yMzY0IDE2LjkxNzIgMjYuODM3NSAxNi41MTgzIDI2LjM1IDE2LjUxODNDMjUuMjUwOSAxNi41MTgzIDI0LjE3ODQgMTYuMzQxIDIzLjE4NTcgMTYuMDEzMUMyMi44NzU1IDE1LjkwNjggMjIuNTIwOSAxNS45ODY1IDIyLjI4MTYgMTYuMjI1OEwyMC4zMzE2IDE4LjE3NTlaTTI1Ljg2MjUgNS40MjEwM0wyNi40OTE4IDYuMDQxNDlMMjAuODk4OSAxMS42MzQ1SDI0LjU3NzNWMTIuNTIwOUgxOS4yNTkxVjcuMjAyNjRIMjAuMTQ1NVYxMS4wMDUxTDI1Ljg2MjUgNS40MjEwM1pcIiBmaWxsPVwiIzQwN0JGRlwiLz5cclxuICAgICAgICA8L3N2Zz5cclxuXHQpXHJcbn1cclxuXHJcbmltcG9ydCAnLi9zdHlsZS5zY3NzJztcclxuaW1wb3J0ICcuL2VkaXRvci5zY3NzJztcclxuXHJcbmNsYXNzIEVkaXRCbG9jayBleHRlbmRzIENvbXBvbmVudHtcclxuXHRjb25zdHJ1Y3Rvcihwcm9wcykge1xyXG5cdFx0c3VwZXIoIHByb3BzICk7XHJcbiAgICAgICAgdGhpcy5zdGF0ZSA9IHtcclxuICAgICAgICAgICAgcG9zdEFycjogW11cclxuICAgICAgICB9XHJcbiAgICB9XHJcbiAgICBcclxuICAgIGNvbXBvbmVudERpZE1vdW50KCkge1xyXG4gICAgICAgIGNvbnN0IHsgc2V0QXR0cmlidXRlcywgYXR0cmlidXRlczogeyBuYW1lLCBmcm9tR2xvYmFsIH0gfSA9IHRoaXMucHJvcHM7XHJcbiAgICAgICAgY29uc3QgX25ld05hbWUgPSBNYXRoLnJhbmRvbSgpLnRvU3RyaW5nKDM2KS5zdWJzdHJpbmcoMiwgMTUpO1xyXG4gICAgICAgIGlmICggIW5hbWUgKSB7XHJcbiAgICAgICAgICAgIHNldEF0dHJpYnV0ZXMoeyBuYW1lOiBfbmV3TmFtZSB9KTtcclxuICAgICAgICB9ICAgICAgICAgIFxyXG4gICAgfVxyXG5cclxuICAgIHRvZ2dsZUZyb21Qb3N0KCl7XHJcbiAgICAgICAgaWYgKHRoaXMucHJvcHMucG9zdHMgJiYgdGhpcy5zdGF0ZS5wb3N0QXJyLmxlbmd0aCA8IDEpIHtcclxuICAgICAgICAgICAgbGV0IG9wdGlvbnMgPSBbXTtcclxuICAgICAgICAgICAgdGhpcy5wcm9wcy5wb3N0cy5mb3JFYWNoKChwb3N0KSA9PiB7XHJcbiAgICAgICAgICAgICAgICBvcHRpb25zLnB1c2goXHJcbiAgICAgICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZTogcG9zdC5pZCxcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw6IHBvc3QudGl0bGUucmVuZGVyZWRcclxuICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgIHRoaXMuc2V0U3RhdGUoe3Bvc3RBcnI6IG9wdGlvbnN9KVxyXG4gICAgICAgIH0gXHJcbiAgICAgICAgY29uc3QgeyBzZXRBdHRyaWJ1dGVzLCBhdHRyaWJ1dGVzOiB7IGZvcm1Qb3N0cywgcG9zdElkIH0gfSA9IHRoaXMucHJvcHM7XHJcbiAgICAgICAgc2V0QXR0cmlidXRlcyh7IGZvcm1Qb3N0czogIWZvcm1Qb3N0cyB9KTtcclxuICAgICAgICBpZighcG9zdElkICYmIHRoaXMucHJvcHMucG9zdHMubGVuZ3RoID4gMCl7XHJcbiAgICAgICAgICAgIHNldEF0dHJpYnV0ZXMoeyBwb3N0SWQ6IHRoaXMucHJvcHMucG9zdHNbMF0uaWQsIHBvc3RUaXRsZTogdGhpcy5wcm9wcy5wb3N0c1swXS50aXRsZS5yZW5kZXJlZCB9KTtcclxuICAgICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgcHJldmlld01vY2soKXtcclxuICAgICAgICByZXR1cm4oXHJcbiAgICAgICAgICAgIDxkaXYgY2xhc3NOYW1lPVwiaml0c2ktcHJldmlldy1wZW9wbGUtbW9ja1wiPlxyXG4gICAgICAgICAgICAgICAgPGRpdj48ZGl2IHN0eWxlPXt7YmFja2dyb3VuZEltYWdlOiBgdXJsKCR7aml0c2lfcHJvLnBsdWdpbl91cmx9YXNzZXRzL2ltZy8wMS5wbmcpYH19PjwvZGl2PjwvZGl2PiAgXHJcbiAgICAgICAgICAgICAgICA8ZGl2PjxkaXYgc3R5bGU9e3tiYWNrZ3JvdW5kSW1hZ2U6IGB1cmwoJHtqaXRzaV9wcm8ucGx1Z2luX3VybH1hc3NldHMvaW1nLzAyLnBuZylgfX0+PC9kaXY+PC9kaXY+ICBcclxuICAgICAgICAgICAgICAgIDxkaXY+PGRpdiBzdHlsZT17e2JhY2tncm91bmRJbWFnZTogYHVybCgke2ppdHNpX3Byby5wbHVnaW5fdXJsfWFzc2V0cy9pbWcvMDMucG5nKWB9fT48L2Rpdj48L2Rpdj4gIFxyXG4gICAgICAgICAgICAgICAgPGRpdj48ZGl2IHN0eWxlPXt7YmFja2dyb3VuZEltYWdlOiBgdXJsKCR7aml0c2lfcHJvLnBsdWdpbl91cmx9YXNzZXRzL2ltZy8wNC5wbmcpYH19PjwvZGl2PjwvZGl2PiAgXHJcbiAgICAgICAgICAgICAgICA8ZGl2PjxkaXYgc3R5bGU9e3tiYWNrZ3JvdW5kSW1hZ2U6IGB1cmwoJHtqaXRzaV9wcm8ucGx1Z2luX3VybH1hc3NldHMvaW1nLzA1LnBuZylgfX0+PC9kaXY+PC9kaXY+ICBcclxuICAgICAgICAgICAgICAgIDxkaXY+PGRpdiBzdHlsZT17e2JhY2tncm91bmRJbWFnZTogYHVybCgke2ppdHNpX3Byby5wbHVnaW5fdXJsfWFzc2V0cy9pbWcvMDYucG5nKWB9fT48L2Rpdj48L2Rpdj4gIFxyXG4gICAgICAgICAgICAgICAgPGRpdj48ZGl2IHN0eWxlPXt7YmFja2dyb3VuZEltYWdlOiBgdXJsKCR7aml0c2lfcHJvLnBsdWdpbl91cmx9YXNzZXRzL2ltZy8wNy5wbmcpYH19PjwvZGl2PjwvZGl2PiAgXHJcbiAgICAgICAgICAgICAgICA8ZGl2PjxkaXYgc3R5bGU9e3tiYWNrZ3JvdW5kSW1hZ2U6IGB1cmwoJHtqaXRzaV9wcm8ucGx1Z2luX3VybH1hc3NldHMvaW1nLzA4LnBuZylgfX0+PC9kaXY+PC9kaXY+ICBcclxuICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgKVxyXG4gICAgfVxyXG5cclxuXHRyZW5kZXIoKXtcclxuXHRcdGNvbnN0IHtcclxuXHRcdFx0YXR0cmlidXRlcyxcclxuXHRcdFx0c2V0QXR0cmlidXRlcyxcclxuICAgICAgICAgICAgcG9zdHNcclxuICAgICAgICB9ID0gdGhpcy5wcm9wcztcclxuICAgICAgICBcclxuICAgICAgICBjb25zdCB7XHJcbiAgICAgICAgICAgIGZvcm1Qb3N0cyxcclxuICAgICAgICAgICAgcG9zdElkLFxyXG4gICAgICAgICAgICBuYW1lLFxyXG4gICAgICAgICAgICB3aWR0aCxcclxuICAgICAgICAgICAgaGVpZ2h0LFxyXG4gICAgICAgICAgICBmcm9tR2xvYmFsLFxyXG4gICAgICAgICAgICBlbmFibGV3ZWxjb21lcGFnZSxcclxuICAgICAgICAgICAgc3RhcnRhdWRpb29ubHksXHJcbiAgICAgICAgICAgIHN0YXJ0YXVkaW9tdXRlZCxcclxuICAgICAgICAgICAgc3RhcnR3aXRoYXVkaW9tdXRlZCxcclxuICAgICAgICAgICAgc3RhcnRzaWxlbnQsXHJcbiAgICAgICAgICAgIHJlc29sdXRpb24sXHJcbiAgICAgICAgICAgIG1heGZ1bGxyZXNvbHV0aW9ucGFydGljaXBhbnQsXHJcbiAgICAgICAgICAgIGRpc2FibGVzaW11bGNhc3QsXHJcbiAgICAgICAgICAgIHN0YXJ0dmlkZW9tdXRlZCxcclxuICAgICAgICAgICAgc3RhcnR3aXRodmlkZW9tdXRlZCxcclxuICAgICAgICAgICAgc3RhcnRzY3JlZW5zaGFyaW5nLFxyXG4gICAgICAgICAgICBmaWxlcmVjb3JkaW5nc2VuYWJsZWQsXHJcbiAgICAgICAgICAgIHRyYW5zY3JpYmluZ2VuYWJsZWQsXHJcbiAgICAgICAgICAgIGxpdmVzdHJlYW1pbmdlbmFibGVkLFxyXG4gICAgICAgICAgICBpbnZpdGVcclxuICAgICAgICB9ID0gYXR0cmlidXRlcztcclxuXHJcblx0XHRyZXR1cm4oXHJcblx0XHRcdDxGcmFnbWVudD5cclxuICAgICAgICAgICAgICAgIDxJbnNwZWN0b3JDb250cm9scz5cclxuICAgICAgICAgICAgICAgICAgICA8UGFuZWxCb2R5IHRpdGxlPXtfXygnU2V0dGluZ3MnKX0gaW5pdGlhbE9wZW49e3RydWV9PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8VG9nZ2xlQ29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKFwiRnJvbSBQb3N0P1wiKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9e2Zvcm1Qb3N0c31cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsoKSA9PiB0aGlzLnRvZ2dsZUZyb21Qb3N0KCl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHtmb3JtUG9zdHMgJiZcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxTZWxlY3RDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKFwiU2VsZWN0IFBvc3RcIil9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWU9eyBwb3N0SWQgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9wdGlvbnM9e3RoaXMuc3RhdGUucG9zdEFycn1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17ICggdmFsICkgPT4gc2V0QXR0cmlidXRlcyh7cG9zdElkOiB2YWwsIHBvc3RUaXRsZTogcG9zdHMuZmluZChvYmogPT4gb2JqLmlkID09IHZhbCkudGl0bGUucmVuZGVyZWR9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHshZm9ybVBvc3RzICYmXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8VGV4dENvbnRyb2xcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ05hbWUnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZT17IG5hbWUgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgKCB2YWwgKSA9PiBzZXRBdHRyaWJ1dGVzKHtuYW1lOiB2YWx9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9ICBcclxuICAgICAgICAgICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKCdDb25maWcgZnJvbSBnbG9iYWwnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBmcm9tR2xvYmFsIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgdmFsID0+IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZXRBdHRyaWJ1dGVzKHtmcm9tR2xvYmFsOiB2YWx9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZighZnJvbUdsb2JhbCl7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNldEF0dHJpYnV0ZXMoe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgd2lkdGg6IHBhcnNlSW50KGppdHNpX3Byby5tZWV0aW5nX3dpZHRoKSwgXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBoZWlnaHQ6IHBhcnNlSW50KGppdHNpX3Byby5tZWV0aW5nX2hlaWdodCksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlbmFibGV3ZWxjb21lcGFnZTogcGFyc2VJbnQoaml0c2lfcHJvLmVuYWJsZXdlbGNvbWVwYWdlKSA/IHRydWUgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN0YXJ0YXVkaW9vbmx5OiBwYXJzZUludChqaXRzaV9wcm8uc3RhcnRhdWRpb29ubHkpID8gdHJ1ZSA6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3RhcnRhdWRpb211dGVkOiBwYXJzZUludChqaXRzaV9wcm8uc3RhcnRhdWRpb211dGVkKSA/IHBhcnNlSW50KGppdHNpX3Byby5zdGFydGF1ZGlvbXV0ZWQpIDogMTAsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGFydHdpdGhhdWRpb211dGVkOiBwYXJzZUludChqaXRzaV9wcm8uc3RhcnR3aXRoYXVkaW9tdXRlZCkgPyB0cnVlIDogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGFydHNpbGVudDogcGFyc2VJbnQoaml0c2lfcHJvLnN0YXJ0c2lsZW50KSA/IHRydWUgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlc29sdXRpb246IHBhcnNlSW50KGppdHNpX3Byby5yZXNvbHV0aW9uKSA/IHBhcnNlSW50KGppdHNpX3Byby5yZXNvbHV0aW9uKSA6IDcyMCxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1heGZ1bGxyZXNvbHV0aW9ucGFydGljaXBhbnQ6IHBhcnNlSW50KGppdHNpX3Byby5tYXhmdWxscmVzb2x1dGlvbnBhcnRpY2lwYW50KSA/IHBhcnNlSW50KGppdHNpX3Byby5tYXhmdWxscmVzb2x1dGlvbnBhcnRpY2lwYW50KSA6IDIsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkaXNhYmxlc2ltdWxjYXN0OiBwYXJzZUludChqaXRzaV9wcm8uZGlzYWJsZXNpbXVsY2FzdCkgPyB0cnVlIDogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGFydHZpZGVvbXV0ZWQ6IHBhcnNlSW50KGppdHNpX3Byby5zdGFydHZpZGVvbXV0ZWQpID8gdHJ1ZSA6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3RhcnR3aXRodmlkZW9tdXRlZDogcGFyc2VJbnQoaml0c2lfcHJvLnN0YXJ0d2l0aHZpZGVvbXV0ZWQpID8gcGFyc2VJbnQoaml0c2lfcHJvLnN0YXJ0d2l0aHZpZGVvbXV0ZWQpIDogMTAsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGFydHNjcmVlbnNoYXJpbmc6IHBhcnNlSW50KGppdHNpX3Byby5zdGFydHNjcmVlbnNoYXJpbmcpID8gdHJ1ZSA6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZmlsZXJlY29yZGluZ3NlbmFibGVkOiBwYXJzZUludChqaXRzaV9wcm8uZmlsZXJlY29yZGluZ3NlbmFibGVkKSA/IHRydWUgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRyYW5zY3JpYmluZ2VuYWJsZWQ6IHBhcnNlSW50KGppdHNpX3Byby50cmFuc2NyaWJpbmdlbmFibGVkKSA/IHRydWUgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxpdmVzdHJlYW1pbmdlbmFibGVkOiBwYXJzZUludChqaXRzaV9wcm8ubGl2ZXN0cmVhbWluZ2VuYWJsZWQpID8gdHJ1ZSA6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaW52aXRlOiBwYXJzZUludChqaXRzaV9wcm8uaW52aXRlKSA/IHRydWUgOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH19XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8+ICAgICAgXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHshZnJvbUdsb2JhbCAmJiAoXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxSYW5nZUNvbnRyb2xcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKCdXaWR0aCcpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZT17IHdpZHRoIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyAoIHZhbCApID0+IHNldEF0dHJpYnV0ZXMoe3dpZHRoOiB2YWx9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1pbj17IDEwMCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1heD17IDIwMDAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGVwPXsgMTAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPFJhbmdlQ29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ0hlaWdodCcpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZT17IGhlaWdodCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgKCB2YWwgKSA9PiBzZXRBdHRyaWJ1dGVzKHtoZWlnaHQ6IHZhbH0pIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWluPXsgMTAwIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWF4PXsgMjAwMCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN0ZXA9eyAxMCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnV2VsY29tZSBQYWdlJyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBlbmFibGV3ZWxjb21lcGFnZSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgdmFsID0+IHNldEF0dHJpYnV0ZXMoe2VuYWJsZXdlbGNvbWVwYWdlOiB2YWx9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnU3RhcnQgQXVkaW8gT25seScpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjaGVja2VkPXsgc3RhcnRhdWRpb29ubHkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHtzdGFydGF1ZGlvb25seTogdmFsfSkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPFJhbmdlQ29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ0F1ZGlvIE11dGVkIEFmdGVyJyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHZhbHVlPXsgc3RhcnRhdWRpb211dGVkIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyAoIHZhbCApID0+IHNldEF0dHJpYnV0ZXMoe3N0YXJ0YXVkaW9tdXRlZDogdmFsfSkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtaW49eyAwIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWF4PXsgMjAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGVwPXsgMSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnWW91cnNlbGYgTXV0ZWQnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2hlY2tlZD17IHN0YXJ0d2l0aGF1ZGlvbXV0ZWQgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHtzdGFydHdpdGhhdWRpb211dGVkOiB2YWx9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnU3RhcnQgU2lsZW50Jyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBzdGFydHNpbGVudCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgdmFsID0+IHNldEF0dHJpYnV0ZXMoe3N0YXJ0c2lsZW50OiB2YWx9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8U2VsZWN0Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oXCJSZXNvbHV0aW9uXCIpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZT17IHJlc29sdXRpb24gfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvcHRpb25zPXtbXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7IGxhYmVsOiBfXygnNDgwcCcpLCB2YWx1ZTogNDgwIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7IGxhYmVsOiBfXygnNzIwcCcpLCB2YWx1ZTogNzIwIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7IGxhYmVsOiBfXygnMTA4MHAnKSwgdmFsdWU6IDEwODAgfSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHsgbGFiZWw6IF9fKCcxNDQwcCcpLCB2YWx1ZTogMTQ0MCB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgeyBsYWJlbDogX18oJzIxNjBwJyksIHZhbHVlOiAyMTYwIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7IGxhYmVsOiBfXygnNDMyMHAnKSwgdmFsdWU6IDQzMjAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBdfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17ICggdmFsICkgPT4gc2V0QXR0cmlidXRlcyh7IHJlc29sdXRpb246IHZhbCB9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8UmFuZ2VDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnTWF4IEZ1bGwgUmVzb2x1dGlvbicpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZT17IG1heGZ1bGxyZXNvbHV0aW9ucGFydGljaXBhbnQgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17ICggdmFsICkgPT4gc2V0QXR0cmlidXRlcyh7bWF4ZnVsbHJlc29sdXRpb25wYXJ0aWNpcGFudDogdmFsfSkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtaW49eyAwIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWF4PXsgMjAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdGVwPXsgMSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnU3RhcnQgVmlkZW8gTXV0ZWQnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2hlY2tlZD17IHN0YXJ0dmlkZW9tdXRlZCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgdmFsID0+IHNldEF0dHJpYnV0ZXMoeyBzdGFydHZpZGVvbXV0ZWQ6IHZhbCB9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8UmFuZ2VDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnVmlkZW8gTXV0ZWQgQWZ0ZXInKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWU9eyBzdGFydHdpdGh2aWRlb211dGVkIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyAoIHZhbCApID0+IHNldEF0dHJpYnV0ZXMoe3N0YXJ0d2l0aHZpZGVvbXV0ZWQ6IHZhbH0pIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbWluPXsgMCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1heD17IDUwIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3RlcD17IDEgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ1N0YXJ0IFNjcmVlbiBTaGFyaW5nJyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBzdGFydHNjcmVlbnNoYXJpbmcgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHsgc3RhcnRzY3JlZW5zaGFyaW5nOiB2YWwgfSkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPENoZWNrYm94Q29udHJvbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbD17X18oJ0VuYWJsZSBSZWNvcmRpbmcnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2hlY2tlZD17IGZpbGVyZWNvcmRpbmdzZW5hYmxlZCB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uQ2hhbmdlPXsgdmFsID0+IHNldEF0dHJpYnV0ZXMoeyBmaWxlcmVjb3JkaW5nc2VuYWJsZWQ6IHZhbCB9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnRW5hYmxlIFRyYW5zY3JpcHRpb24nKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2hlY2tlZD17IHRyYW5zY3JpYmluZ2VuYWJsZWQgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHsgdHJhbnNjcmliaW5nZW5hYmxlZDogdmFsIH0pIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxDaGVja2JveENvbnRyb2xcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw9e19fKCdFbmFibGUgTGl2ZXN0cmVhbScpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjaGVja2VkPXsgbGl2ZXN0cmVhbWluZ2VuYWJsZWQgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHsgbGl2ZXN0cmVhbWluZ2VuYWJsZWQ6IHZhbCB9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnU2ltdWxjYXN0Jyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBkaXNhYmxlc2ltdWxjYXN0IH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25DaGFuZ2U9eyB2YWwgPT4gc2V0QXR0cmlidXRlcyh7IGRpc2FibGVzaW11bGNhc3Q6IHZhbCB9KSB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8Q2hlY2tib3hDb250cm9sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsPXtfXygnRW5hYmxlIEludml0aW5nJyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNoZWNrZWQ9eyBpbnZpdGUgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbkNoYW5nZT17IHZhbCA9PiBzZXRBdHRyaWJ1dGVzKHsgaW52aXRlOiB2YWwgfSkgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgKX0gXHJcbiAgICAgICAgICAgICAgICAgICAgPC9QYW5lbEJvZHk+ICAgICAgICAgICAgICAgICAgICBcclxuICAgICAgICAgICAgICAgIDwvSW5zcGVjdG9yQ29udHJvbHM+XHJcbiAgICAgICAgICAgICAgICA8ZGl2IGlkPVwibWVldGluZy11aS1wcmV2aWV3XCIgY2xhc3NOYW1lPVwicHJldmlldy1zdWNjZXNzIHByZXZpZXctYmxvY2tcIj5cclxuICAgICAgICAgICAgICAgICAgICB7dGhpcy5wcmV2aWV3TW9jaygpfVxyXG4gICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgIDwvRnJhZ21lbnQ+XHJcblx0XHQpO1xyXG5cdH1cclxufVxyXG5cclxucmVnaXN0ZXJCbG9ja1R5cGUoJ2ppdHNpLXByby9qaXRzaS1wcm8nLCB7XHJcbiAgdGl0bGU6IF9fKCdKaXRzaSBQcm8nLCAnaml0c2ktcHJvJyksXHJcbiAgaWNvbjogYmxvY2tJY29uLFxyXG4gIGNhdGVnb3J5OiAnZW1iZWQnLFxyXG4gIGtleXdvcmRzOiBbXHJcbiAgICBfXyggJ2ppdHNpJywgJ2ppdHNpLXBybycgKSxcclxuICAgIF9fKCAnbWVldGluZycsICdqaXRzaS1wcm8nICksXHJcbiAgICBfXyggJ3ZpZGVvJywgJ2ppdHNpLXBybycgKSxcclxuICAgIF9fKCAnY29uZmVyZW5jZScsICdqaXRzaS1wcm8nICksXHJcbiAgICBfXyggJ3pvb20nLCAnaml0c2ktcHJvJyApXHJcbiAgXSxcclxuICBhdHRyaWJ1dGVzOiB7XHJcbiAgICBmb3JtUG9zdHM6IHtcclxuICAgICAgICB0eXBlOiAnYm9vbGVhbicsXHJcbiAgICAgICAgZGVmYXVsdDogZmFsc2VcclxuICAgIH0sXHJcbiAgICBwb3N0SWQ6IHtcclxuICAgICAgICB0eXBlOiAnbnVtYmVyJyxcclxuICAgICAgICBkZWZhdWx0OiAnJ1xyXG4gICAgfSxcclxuICAgIHBvc3RUaXRsZToge1xyXG4gICAgICAgIHR5cGU6ICdzdHJpbmcnLFxyXG4gICAgICAgIGRlZmF1bHQ6ICcnXHJcbiAgICB9LFxyXG4gICAgbmFtZToge1xyXG4gICAgICAgIHR5cGU6ICdzdHJpbmcnLFxyXG4gICAgICAgIGRlZmF1bHQ6ICcnXHJcbiAgICB9LFxyXG4gICAgd2lkdGg6IHtcclxuICAgICAgICB0eXBlOiAnbnVtYmVyJyxcclxuICAgICAgICBkZWZhdWx0OiAxMDgwXHJcbiAgICB9LFxyXG4gICAgaGVpZ2h0OiB7XHJcbiAgICAgICAgdHlwZTogJ251bWJlcicsXHJcbiAgICAgICAgZGVmYXVsdDogNzIwXHJcbiAgICB9LFxyXG4gICAgZnJvbUdsb2JhbDoge1xyXG4gICAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgICBkZWZhdWx0OiBmYWxzZVxyXG4gICAgfSxcclxuICAgIGVuYWJsZXdlbGNvbWVwYWdlOiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IHRydWVcclxuICAgIH0sXHJcbiAgICBzdGFydGF1ZGlvb25seToge1xyXG4gICAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgICBkZWZhdWx0OiBmYWxzZVxyXG4gICAgfSxcclxuICAgIHN0YXJ0YXVkaW9tdXRlZDoge1xyXG4gICAgICAgIHR5cGU6ICdudW1iZXInLFxyXG4gICAgICAgIGRlZmF1bHQ6IDEwXHJcbiAgICB9LFxyXG4gICAgc3RhcnR3aXRoYXVkaW9tdXRlZDoge1xyXG4gICAgICAgIHR5cGU6ICdib29sZWFuJyxcclxuICAgICAgICBkZWZhdWx0OiBmYWxzZVxyXG4gICAgfSxcclxuICAgIHN0YXJ0c2lsZW50OiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IGZhbHNlXHJcbiAgICB9LFxyXG4gICAgcmVzb2x1dGlvbjoge1xyXG4gICAgICAgIHR5cGU6ICdudW1iZXInLFxyXG4gICAgICAgIGRlZmF1bHQ6IDcyMFxyXG4gICAgfSxcclxuICAgIG1heGZ1bGxyZXNvbHV0aW9ucGFydGljaXBhbnQ6IHtcclxuICAgICAgICB0eXBlOiAnbnVtYmVyJyxcclxuICAgICAgICBkZWZhdWx0OiAyXHJcbiAgICB9LFxyXG4gICAgc3RhcnR2aWRlb211dGVkOiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IHRydWVcclxuICAgIH0sXHJcbiAgICBzdGFydHdpdGh2aWRlb211dGVkOiB7XHJcbiAgICAgICAgdHlwZTogJ251bWJlcicsXHJcbiAgICAgICAgZGVmYXVsdDogMTBcclxuICAgIH0sXHJcbiAgICBzdGFydHNjcmVlbnNoYXJpbmc6IHtcclxuICAgICAgICB0eXBlOiAnYm9vbGVhbicsXHJcbiAgICAgICAgZGVmYXVsdDogZmFsc2VcclxuICAgIH0sXHJcbiAgICBmaWxlcmVjb3JkaW5nc2VuYWJsZWQ6IHtcclxuICAgICAgICB0eXBlOiAnYm9vbGVhbicsXHJcbiAgICAgICAgZGVmYXVsdDogZmFsc2VcclxuICAgIH0sXHJcbiAgICB0cmFuc2NyaWJpbmdlbmFibGVkOiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IGZhbHNlXHJcbiAgICB9LFxyXG4gICAgbGl2ZXN0cmVhbWluZ2VuYWJsZWQ6IHtcclxuICAgICAgICB0eXBlOiAnYm9vbGVhbicsXHJcbiAgICAgICAgZGVmYXVsdDogZmFsc2VcclxuICAgIH0sXHJcbiAgICBkaXNhYmxlc2ltdWxjYXN0OiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IGZhbHNlXHJcbiAgICB9LFxyXG4gICAgaW52aXRlOiB7XHJcbiAgICAgICAgdHlwZTogJ2Jvb2xlYW4nLFxyXG4gICAgICAgIGRlZmF1bHQ6IHRydWVcclxuICAgIH1cclxuICB9LFxyXG4gIGVkaXQ6IHdpdGhTZWxlY3QoKHNlbGVjdCkgPT4ge1xyXG4gICAgcmV0dXJuIHtcclxuICAgICAgICBwb3N0czogc2VsZWN0KCdjb3JlJykuZ2V0RW50aXR5UmVjb3JkcygncG9zdFR5cGUnLCAnbWVldGluZycsIHtwZXJfcGFnZTogLTF9KSxcclxuICAgIH07XHJcbiAgfSkoRWRpdEJsb2NrKSxcclxuICBzYXZlOiBmdW5jdGlvbiggcHJvcHMgKSB7XHJcblx0XHRjb25zdCB7XHJcbiAgICAgICAgICAgIGZvcm1Qb3N0cyxcclxuICAgICAgICAgICAgcG9zdElkLFxyXG4gICAgICAgICAgICBwb3N0VGl0bGUsXHJcbiAgICAgICAgICAgIG5hbWUsXHJcbiAgICAgICAgICAgIHdpZHRoLFxyXG4gICAgICAgICAgICBoZWlnaHQsXHJcbiAgICAgICAgICAgIGVuYWJsZXdlbGNvbWVwYWdlLFxyXG4gICAgICAgICAgICBzdGFydGF1ZGlvb25seSxcclxuICAgICAgICAgICAgc3RhcnRhdWRpb211dGVkLFxyXG4gICAgICAgICAgICBzdGFydHdpdGhhdWRpb211dGVkLFxyXG4gICAgICAgICAgICBzdGFydHNpbGVudCxcclxuICAgICAgICAgICAgcmVzb2x1dGlvbixcclxuICAgICAgICAgICAgbWF4ZnVsbHJlc29sdXRpb25wYXJ0aWNpcGFudCxcclxuICAgICAgICAgICAgZGlzYWJsZXNpbXVsY2FzdCxcclxuICAgICAgICAgICAgc3RhcnR2aWRlb211dGVkLFxyXG4gICAgICAgICAgICBzdGFydHdpdGh2aWRlb211dGVkLFxyXG4gICAgICAgICAgICBzdGFydHNjcmVlbnNoYXJpbmcsXHJcbiAgICAgICAgICAgIGZpbGVyZWNvcmRpbmdzZW5hYmxlZCxcclxuICAgICAgICAgICAgdHJhbnNjcmliaW5nZW5hYmxlZCxcclxuICAgICAgICAgICAgbGl2ZXN0cmVhbWluZ2VuYWJsZWQsXHJcbiAgICAgICAgICAgIGludml0ZVxyXG5cdFx0fSA9IHByb3BzLmF0dHJpYnV0ZXM7XHJcblxyXG5cdFx0cmV0dXJuIChcclxuICAgICAgICAgICAgPGRpdj5cclxuICAgICAgICAgICAgICAgIDxkaXYgXHJcbiAgICAgICAgICAgICAgICAgICAgY2xhc3NOYW1lPVwiaml0c2ktd3JhcHBlclwiIFxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGEtbmFtZT17Zm9ybVBvc3RzID8gcG9zdFRpdGxlIDogbmFtZX0gXHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS13aWR0aD17d2lkdGh9IFxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGEtaGVpZ2h0PXtoZWlnaHR9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1zdGFydGF1ZGlvb25seT17c3RhcnRhdWRpb29ubHl9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1zdGFydGF1ZGlvbXV0ZWQ9e3N0YXJ0YXVkaW9tdXRlZH1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLXN0YXJ0d2l0aGF1ZGlvbXV0ZWQ9e3N0YXJ0d2l0aGF1ZGlvbXV0ZWR9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1zdGFydHNpbGVudD17c3RhcnRzaWxlbnR9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1yZXNvbHV0aW9uPXtyZXNvbHV0aW9ufVxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGEtbWF4ZnVsbHJlc29sdXRpb25wYXJ0aWNpcGFudD17bWF4ZnVsbHJlc29sdXRpb25wYXJ0aWNpcGFudH1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLWRpc2FibGVzaW11bGNhc3Q9e2Rpc2FibGVzaW11bGNhc3R9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1zdGFydHZpZGVvbXV0ZWQ9e3N0YXJ0dmlkZW9tdXRlZH1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLXN0YXJ0d2l0aHZpZGVvbXV0ZWQ9e3N0YXJ0d2l0aHZpZGVvbXV0ZWR9XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS1zdGFydHNjcmVlbnNoYXJpbmc9e3N0YXJ0c2NyZWVuc2hhcmluZ31cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLWZpbGVyZWNvcmRpbmdzZW5hYmxlZD17ZmlsZXJlY29yZGluZ3NlbmFibGVkfVxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGEtdHJhbnNjcmliaW5nZW5hYmxlZD17dHJhbnNjcmliaW5nZW5hYmxlZH1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLWxpdmVzdHJlYW1pbmdlbmFibGVkPXtsaXZlc3RyZWFtaW5nZW5hYmxlZH1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLWVuYWJsZXdlbGNvbWVwYWdlPXtlbmFibGV3ZWxjb21lcGFnZX1cclxuICAgICAgICAgICAgICAgICAgICBkYXRhLWludml0ZT17aW52aXRlfVxyXG4gICAgICAgICAgICAgICAgICAgIHN0eWxlPXt7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHdpZHRoOiBgJHt3aWR0aH1weGBcclxuICAgICAgICAgICAgICAgICAgICB9fVxyXG4gICAgICAgICAgICAgICAgPjwvZGl2PlxyXG4gICAgICAgICAgICA8L2Rpdj5cclxuXHRcdCk7XHJcblx0fVxyXG59KTtcclxuIiwiLy8gcmVtb3ZlZCBieSBleHRyYWN0LXRleHQtd2VicGFjay1wbHVnaW4iLCIvLyByZW1vdmVkIGJ5IGV4dHJhY3QtdGV4dC13ZWJwYWNrLXBsdWdpbiJdLCJzb3VyY2VSb290IjoiIn0=