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
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\nvar __ = wp.i18n.__;\nvar _wp$blocks = wp.blocks,\n    registerBlockType = _wp$blocks.registerBlockType,\n    createBlock = _wp$blocks.createBlock;\nvar TextControl = wp.components.TextControl;\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (registerBlockType('gaoop/opt-out-block', {\n  title: __('Analytics Opt Out', 'google-analytics-opt-out'),\n  category: 'widgets',\n  icon: wp.element.createElement(\n    'svg',\n    { width: '18', height: '20',\n      xmlns: 'http://www.w3.org/2000/svg' },\n    wp.element.createElement('path', {\n      d: 'M15.61 16.086c.093.094.14.2.14.316 0 .118-.047.211-.14.282a2.763 2.763 0 0 1-2.075.878c-.82 0-1.512-.292-2.074-.878L7.383 12.64l-.88.879c.165.398.247.808.247 1.23 0 .938-.328 1.734-.984 2.39-.657.657-1.454.985-2.391.985-.938 0-1.734-.328-2.39-.984C.327 16.484 0 15.688 0 14.75c0-.937.328-1.734.984-2.39.657-.657 1.453-.985 2.391-.985.164 0 .316.012.457.035l1.16-1.16-1.16-1.16c-.14.023-.293.035-.457.035-.938 0-1.734-.328-2.39-.984C.327 7.484 0 6.688 0 5.75c0-.937.328-1.734.984-2.39.657-.657 1.453-.985 2.391-.985.937 0 1.734.328 2.39.984.657.657.985 1.454.985 2.391 0 .422-.082.832-.246 1.23l.879.88 4.078-4.044a2.763 2.763 0 0 1 2.074-.878c.82 0 1.512.293 2.074.878.094.07.141.164.141.282a.437.437 0 0 1-.14.316L9.772 10.25l5.836 5.836zM3.374 4.625c-.305 0-.568.111-.791.334a1.081 1.081 0 0 0-.334.791c0 .305.111.568.334.791.223.223.486.334.791.334.305 0 .568-.111.791-.334.223-.223.334-.486.334-.791 0-.305-.111-.568-.334-.791a1.081 1.081 0 0 0-.791-.334zm0 9c-.305 0-.568.111-.791.334a1.081 1.081 0 0 0-.334.791c0 .305.111.568.334.791.223.223.486.334.791.334.305 0 .568-.111.791-.334.223-.223.334-.486.334-.791 0-.305-.111-.568-.334-.791a1.081 1.081 0 0 0-.791-.334zm3.937-3.797a.407.407 0 0 0-.298.123.407.407 0 0 0-.123.299c0 .117.04.217.123.299a.407.407 0 0 0 .298.123.407.407 0 0 0 .3-.123.407.407 0 0 0 .122-.299.407.407 0 0 0-.123-.299.407.407 0 0 0-.299-.123z',\n      fill: '#000' }),\n    wp.element.createElement('path', { fill: '#196EEE', d: 'M16 0H18V4H16z' }),\n    wp.element.createElement('path', { fill: '#D9442F', d: 'M16 4H18V8H16z' }),\n    wp.element.createElement('path', { fill: '#FFBB04', d: 'M16 8H18V12H16z' }),\n    wp.element.createElement('path', { fill: '#176CED', d: 'M16 12H18V16H16z' }),\n    wp.element.createElement('path', { fill: '#03A25D', d: 'M16 16H18V20H16z' })\n  ),\n  keywords: ['gaoop', __('Analytics Opt Out', 'google-analytics-opt-out')],\n  attributes: {\n    content: {\n      source: 'text',\n      selector: 'a',\n      default: __('Click here to opt out', 'google-analytics-opt-out')\n    }\n  },\n\n  transforms: {\n    to: [{\n      type: 'block',\n      blocks: ['core/paragraph'],\n      transform: function transform(_ref) {\n        var content = _ref.content;\n\n        return createBlock('core/paragraph', {\n          content: content\n        });\n      }\n    }],\n    from: [{\n      type: 'block',\n      blocks: ['core/paragraph'],\n      transform: function transform(_ref2) {\n        var content = _ref2.content;\n\n        return createBlock('gaoop/opt-out-block', {\n          content: content\n        });\n      }\n    }]\n  },\n\n  edit: function edit(props) {\n    var isSelected = props.isSelected,\n        setAttributes = props.setAttributes;\n\n\n    var content = '' === props.attributes.content ? __('Click here to opt out', 'google-analytics-opt-out') : props.attributes.content;\n\n    return isSelected ? wp.element.createElement(TextControl, {\n      label: __('Enter a link text', 'google-analytics-opt-out'),\n      value: content,\n      onChange: function onChange(value) {\n        return setAttributes({ content: value });\n      }\n    }) : wp.element.createElement(\n      'a',\n      { className: 'gaoop-block', href: '#' },\n      content\n    );\n  },\n  save: function save(props) {\n\n    var content = '' === props.attributes.content ? __('Click here to opt out', 'google-analytics-opt-out') : props.attributes.content;\n\n    return wp.element.createElement(\n      'a',\n      { className: 'gaoop-block',\n        href: 'javascript:gaoop_analytics_optout();' },\n      content\n    );\n  }\n}));//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiMC5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Jsb2Nrcy9vcHQtb3V0LmpzP2M4YzciXSwic291cmNlc0NvbnRlbnQiOlsidmFyIF9fID0gd3AuaTE4bi5fXztcbnZhciBfd3AkYmxvY2tzID0gd3AuYmxvY2tzLFxuICAgIHJlZ2lzdGVyQmxvY2tUeXBlID0gX3dwJGJsb2Nrcy5yZWdpc3RlckJsb2NrVHlwZSxcbiAgICBjcmVhdGVCbG9jayA9IF93cCRibG9ja3MuY3JlYXRlQmxvY2s7XG52YXIgVGV4dENvbnRyb2wgPSB3cC5jb21wb25lbnRzLlRleHRDb250cm9sO1xuXG5cbmV4cG9ydCBkZWZhdWx0IHJlZ2lzdGVyQmxvY2tUeXBlKCdnYW9vcC9vcHQtb3V0LWJsb2NrJywge1xuICB0aXRsZTogX18oJ0FuYWx5dGljcyBPcHQgT3V0JywgJ2dvb2dsZS1hbmFseXRpY3Mtb3B0LW91dCcpLFxuICBjYXRlZ29yeTogJ3dpZGdldHMnLFxuICBpY29uOiB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoXG4gICAgJ3N2ZycsXG4gICAgeyB3aWR0aDogJzE4JywgaGVpZ2h0OiAnMjAnLFxuICAgICAgeG1sbnM6ICdodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZycgfSxcbiAgICB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoJ3BhdGgnLCB7XG4gICAgICBkOiAnTTE1LjYxIDE2LjA4NmMuMDkzLjA5NC4xNC4yLjE0LjMxNiAwIC4xMTgtLjA0Ny4yMTEtLjE0LjI4MmEyLjc2MyAyLjc2MyAwIDAgMS0yLjA3NS44NzhjLS44MiAwLTEuNTEyLS4yOTItMi4wNzQtLjg3OEw3LjM4MyAxMi42NGwtLjg4Ljg3OWMuMTY1LjM5OC4yNDcuODA4LjI0NyAxLjIzIDAgLjkzOC0uMzI4IDEuNzM0LS45ODQgMi4zOS0uNjU3LjY1Ny0xLjQ1NC45ODUtMi4zOTEuOTg1LS45MzggMC0xLjczNC0uMzI4LTIuMzktLjk4NEMuMzI3IDE2LjQ4NCAwIDE1LjY4OCAwIDE0Ljc1YzAtLjkzNy4zMjgtMS43MzQuOTg0LTIuMzkuNjU3LS42NTcgMS40NTMtLjk4NSAyLjM5MS0uOTg1LjE2NCAwIC4zMTYuMDEyLjQ1Ny4wMzVsMS4xNi0xLjE2LTEuMTYtMS4xNmMtLjE0LjAyMy0uMjkzLjAzNS0uNDU3LjAzNS0uOTM4IDAtMS43MzQtLjMyOC0yLjM5LS45ODRDLjMyNyA3LjQ4NCAwIDYuNjg4IDAgNS43NWMwLS45MzcuMzI4LTEuNzM0Ljk4NC0yLjM5LjY1Ny0uNjU3IDEuNDUzLS45ODUgMi4zOTEtLjk4NS45MzcgMCAxLjczNC4zMjggMi4zOS45ODQuNjU3LjY1Ny45ODUgMS40NTQuOTg1IDIuMzkxIDAgLjQyMi0uMDgyLjgzMi0uMjQ2IDEuMjNsLjg3OS44OCA0LjA3OC00LjA0NGEyLjc2MyAyLjc2MyAwIDAgMSAyLjA3NC0uODc4Yy44MiAwIDEuNTEyLjI5MyAyLjA3NC44NzguMDk0LjA3LjE0MS4xNjQuMTQxLjI4MmEuNDM3LjQzNyAwIDAgMS0uMTQuMzE2TDkuNzcyIDEwLjI1bDUuODM2IDUuODM2ek0zLjM3NCA0LjYyNWMtLjMwNSAwLS41NjguMTExLS43OTEuMzM0YTEuMDgxIDEuMDgxIDAgMCAwLS4zMzQuNzkxYzAgLjMwNS4xMTEuNTY4LjMzNC43OTEuMjIzLjIyMy40ODYuMzM0Ljc5MS4zMzQuMzA1IDAgLjU2OC0uMTExLjc5MS0uMzM0LjIyMy0uMjIzLjMzNC0uNDg2LjMzNC0uNzkxIDAtLjMwNS0uMTExLS41NjgtLjMzNC0uNzkxYTEuMDgxIDEuMDgxIDAgMCAwLS43OTEtLjMzNHptMCA5Yy0uMzA1IDAtLjU2OC4xMTEtLjc5MS4zMzRhMS4wODEgMS4wODEgMCAwIDAtLjMzNC43OTFjMCAuMzA1LjExMS41NjguMzM0Ljc5MS4yMjMuMjIzLjQ4Ni4zMzQuNzkxLjMzNC4zMDUgMCAuNTY4LS4xMTEuNzkxLS4zMzQuMjIzLS4yMjMuMzM0LS40ODYuMzM0LS43OTEgMC0uMzA1LS4xMTEtLjU2OC0uMzM0LS43OTFhMS4wODEgMS4wODEgMCAwIDAtLjc5MS0uMzM0em0zLjkzNy0zLjc5N2EuNDA3LjQwNyAwIDAgMC0uMjk4LjEyMy40MDcuNDA3IDAgMCAwLS4xMjMuMjk5YzAgLjExNy4wNC4yMTcuMTIzLjI5OWEuNDA3LjQwNyAwIDAgMCAuMjk4LjEyMy40MDcuNDA3IDAgMCAwIC4zLS4xMjMuNDA3LjQwNyAwIDAgMCAuMTIyLS4yOTkuNDA3LjQwNyAwIDAgMC0uMTIzLS4yOTkuNDA3LjQwNyAwIDAgMC0uMjk5LS4xMjN6JyxcbiAgICAgIGZpbGw6ICcjMDAwJyB9KSxcbiAgICB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoJ3BhdGgnLCB7IGZpbGw6ICcjMTk2RUVFJywgZDogJ00xNiAwSDE4VjRIMTZ6JyB9KSxcbiAgICB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoJ3BhdGgnLCB7IGZpbGw6ICcjRDk0NDJGJywgZDogJ00xNiA0SDE4VjhIMTZ6JyB9KSxcbiAgICB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoJ3BhdGgnLCB7IGZpbGw6ICcjRkZCQjA0JywgZDogJ00xNiA4SDE4VjEySDE2eicgfSksXG4gICAgd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KCdwYXRoJywgeyBmaWxsOiAnIzE3NkNFRCcsIGQ6ICdNMTYgMTJIMThWMTZIMTZ6JyB9KSxcbiAgICB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoJ3BhdGgnLCB7IGZpbGw6ICcjMDNBMjVEJywgZDogJ00xNiAxNkgxOFYyMEgxNnonIH0pXG4gICksXG4gIGtleXdvcmRzOiBbJ2dhb29wJywgX18oJ0FuYWx5dGljcyBPcHQgT3V0JywgJ2dvb2dsZS1hbmFseXRpY3Mtb3B0LW91dCcpXSxcbiAgYXR0cmlidXRlczoge1xuICAgIGNvbnRlbnQ6IHtcbiAgICAgIHNvdXJjZTogJ3RleHQnLFxuICAgICAgc2VsZWN0b3I6ICdhJyxcbiAgICAgIGRlZmF1bHQ6IF9fKCdDbGljayBoZXJlIHRvIG9wdCBvdXQnLCAnZ29vZ2xlLWFuYWx5dGljcy1vcHQtb3V0JylcbiAgICB9XG4gIH0sXG5cbiAgdHJhbnNmb3Jtczoge1xuICAgIHRvOiBbe1xuICAgICAgdHlwZTogJ2Jsb2NrJyxcbiAgICAgIGJsb2NrczogWydjb3JlL3BhcmFncmFwaCddLFxuICAgICAgdHJhbnNmb3JtOiBmdW5jdGlvbiB0cmFuc2Zvcm0oX3JlZikge1xuICAgICAgICB2YXIgY29udGVudCA9IF9yZWYuY29udGVudDtcblxuICAgICAgICByZXR1cm4gY3JlYXRlQmxvY2soJ2NvcmUvcGFyYWdyYXBoJywge1xuICAgICAgICAgIGNvbnRlbnQ6IGNvbnRlbnRcbiAgICAgICAgfSk7XG4gICAgICB9XG4gICAgfV0sXG4gICAgZnJvbTogW3tcbiAgICAgIHR5cGU6ICdibG9jaycsXG4gICAgICBibG9ja3M6IFsnY29yZS9wYXJhZ3JhcGgnXSxcbiAgICAgIHRyYW5zZm9ybTogZnVuY3Rpb24gdHJhbnNmb3JtKF9yZWYyKSB7XG4gICAgICAgIHZhciBjb250ZW50ID0gX3JlZjIuY29udGVudDtcblxuICAgICAgICByZXR1cm4gY3JlYXRlQmxvY2soJ2dhb29wL29wdC1vdXQtYmxvY2snLCB7XG4gICAgICAgICAgY29udGVudDogY29udGVudFxuICAgICAgICB9KTtcbiAgICAgIH1cbiAgICB9XVxuICB9LFxuXG4gIGVkaXQ6IGZ1bmN0aW9uIGVkaXQocHJvcHMpIHtcbiAgICB2YXIgaXNTZWxlY3RlZCA9IHByb3BzLmlzU2VsZWN0ZWQsXG4gICAgICAgIHNldEF0dHJpYnV0ZXMgPSBwcm9wcy5zZXRBdHRyaWJ1dGVzO1xuXG5cbiAgICB2YXIgY29udGVudCA9ICcnID09PSBwcm9wcy5hdHRyaWJ1dGVzLmNvbnRlbnQgPyBfXygnQ2xpY2sgaGVyZSB0byBvcHQgb3V0JywgJ2dvb2dsZS1hbmFseXRpY3Mtb3B0LW91dCcpIDogcHJvcHMuYXR0cmlidXRlcy5jb250ZW50O1xuXG4gICAgcmV0dXJuIGlzU2VsZWN0ZWQgPyB3cC5lbGVtZW50LmNyZWF0ZUVsZW1lbnQoVGV4dENvbnRyb2wsIHtcbiAgICAgIGxhYmVsOiBfXygnRW50ZXIgYSBsaW5rIHRleHQnLCAnZ29vZ2xlLWFuYWx5dGljcy1vcHQtb3V0JyksXG4gICAgICB2YWx1ZTogY29udGVudCxcbiAgICAgIG9uQ2hhbmdlOiBmdW5jdGlvbiBvbkNoYW5nZSh2YWx1ZSkge1xuICAgICAgICByZXR1cm4gc2V0QXR0cmlidXRlcyh7IGNvbnRlbnQ6IHZhbHVlIH0pO1xuICAgICAgfVxuICAgIH0pIDogd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KFxuICAgICAgJ2EnLFxuICAgICAgeyBjbGFzc05hbWU6ICdnYW9vcC1ibG9jaycsIGhyZWY6ICcjJyB9LFxuICAgICAgY29udGVudFxuICAgICk7XG4gIH0sXG4gIHNhdmU6IGZ1bmN0aW9uIHNhdmUocHJvcHMpIHtcblxuICAgIHZhciBjb250ZW50ID0gJycgPT09IHByb3BzLmF0dHJpYnV0ZXMuY29udGVudCA/IF9fKCdDbGljayBoZXJlIHRvIG9wdCBvdXQnLCAnZ29vZ2xlLWFuYWx5dGljcy1vcHQtb3V0JykgOiBwcm9wcy5hdHRyaWJ1dGVzLmNvbnRlbnQ7XG5cbiAgICByZXR1cm4gd3AuZWxlbWVudC5jcmVhdGVFbGVtZW50KFxuICAgICAgJ2EnLFxuICAgICAgeyBjbGFzc05hbWU6ICdnYW9vcC1ibG9jaycsXG4gICAgICAgIGhyZWY6ICdqYXZhc2NyaXB0Omdhb29wX2FuYWx5dGljc19vcHRvdXQoKTsnIH0sXG4gICAgICBjb250ZW50XG4gICAgKTtcbiAgfVxufSk7XG5cblxuLy8vLy8vLy8vLy8vLy8vLy8vXG4vLyBXRUJQQUNLIEZPT1RFUlxuLy8gLi9ibG9ja3Mvb3B0LW91dC5qc1xuLy8gbW9kdWxlIGlkID0gMFxuLy8gbW9kdWxlIGNodW5rcyA9IDAiXSwibWFwcGluZ3MiOiJBQUFBO0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///0\n");

/***/ })
/******/ ]);