/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/rest-timer.js":
/*!************************************!*\
  !*** ./resources/js/rest-timer.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   RestTimer: () => (/* binding */ RestTimer)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Rest Timer Handler
 */
var RestTimer = /*#__PURE__*/function () {
  function RestTimer() {
    _classCallCheck(this, RestTimer);
    this.secondsRemaining = 0;
    this.intervalId = null;
    this.modalElement = null;
    this.audioContext = null;
  }
  _createClass(RestTimer, [{
    key: "start",
    value: function start(seconds, exerciseName) {
      var _this = this;
      if (this.intervalId) {
        this.stop();
      }
      this.secondsRemaining = seconds;
      this.showModal(exerciseName);
      this.intervalId = setInterval(function () {
        return _this.tick();
      }, 1000);
    }
  }, {
    key: "stop",
    value: function stop() {
      if (this.intervalId) {
        clearInterval(this.intervalId);
        this.intervalId = null;
      }
      this.hideModal();
    }
  }, {
    key: "tick",
    value: function tick() {
      this.secondsRemaining--;
      this.updateDisplay();
      if (this.secondsRemaining <= 0) {
        this.complete();
      }
    }
  }, {
    key: "complete",
    value: function complete() {
      this.alert();
      this.stop();
    }
  }, {
    key: "showModal",
    value: function showModal(exerciseName) {
      this.modalElement = document.getElementById('rest-timer-modal');
      this.updateDisplay();
      this.modalElement.classList.add('active');
    }
  }, {
    key: "hideModal",
    value: function hideModal() {
      if (this.modalElement) {
        this.modalElement.classList.remove('active');
      }
    }
  }, {
    key: "updateDisplay",
    value: function updateDisplay() {
      var minutes = Math.floor(this.secondsRemaining / 60);
      var seconds = this.secondsRemaining % 60;
      var display = "".concat(minutes, ":").concat(seconds.toString().padStart(2, '0'));
      document.getElementById('rest-timer-display').textContent = display;
    }
  }, {
    key: "alert",
    value: function alert() {
      // Try vibration first (mobile)
      if ('vibrate' in navigator) {
        navigator.vibrate([200, 100, 200]);
      }

      // Try audio beep (fallback)
      try {
        if (!this.audioContext) {
          this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        var oscillator = this.audioContext.createOscillator();
        oscillator.connect(this.audioContext.destination);
        oscillator.frequency.value = 800;
        oscillator.start();
        oscillator.stop(this.audioContext.currentTime + 0.2);
      } catch (e) {
        // Audio not supported, silent fallback
      }
    }
  }]);
  return RestTimer;
}();

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*****************************************!*\
  !*** ./resources/js/workout-session.js ***!
  \*****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _rest_timer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./rest-timer.js */ "./resources/js/rest-timer.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; var r = _regenerator(), e = r.m(_regeneratorRuntime), t = (Object.getPrototypeOf ? Object.getPrototypeOf(e) : e.__proto__).constructor; function n(r) { var e = "function" == typeof r && r.constructor; return !!e && (e === t || "GeneratorFunction" === (e.displayName || e.name)); } var o = { "throw": 1, "return": 2, "break": 3, "continue": 3 }; function a(r) { var e, t; return function (n) { e || (e = { stop: function stop() { return t(n.a, 2); }, "catch": function _catch() { return n.v; }, abrupt: function abrupt(r, e) { return t(n.a, o[r], e); }, delegateYield: function delegateYield(r, o, a) { return e.resultName = o, t(n.d, _regeneratorValues(r), a); }, finish: function finish(r) { return t(n.f, r); } }, t = function t(r, _t, o) { n.p = e.prev, n.n = e.next; try { return r(_t, o); } finally { e.next = n.n; } }), e.resultName && (e[e.resultName] = n.v, e.resultName = void 0), e.sent = n.v, e.next = n.n; try { return r.call(this, e); } finally { n.p = e.prev, n.n = e.next; } }; } return (_regeneratorRuntime = function _regeneratorRuntime() { return { wrap: function wrap(e, t, n, o) { return r.w(a(e), t, n, o && o.reverse()); }, isGeneratorFunction: n, mark: r.m, awrap: function awrap(r, e) { return new _OverloadYield(r, e); }, AsyncIterator: _regeneratorAsyncIterator, async: function async(r, e, t, o, u) { return (n(e) ? _regeneratorAsyncGen : _regeneratorAsync)(a(r), e, t, o, u); }, keys: _regeneratorKeys, values: _regeneratorValues }; })(); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(_typeof(e) + " is not iterable"); }
function _regeneratorKeys(e) { var n = Object(e), r = []; for (var t in n) r.unshift(t); return function e() { for (; r.length;) if ((t = r.pop()) in n) return e.value = t, e.done = !1, e; return e.done = !0, e; }; }
function _regeneratorAsync(n, e, r, t, o) { var a = _regeneratorAsyncGen(n, e, r, t, o); return a.next().then(function (n) { return n.done ? n.value : a.next(); }); }
function _regeneratorAsyncGen(r, e, t, o, n) { return new _regeneratorAsyncIterator(_regenerator().w(r, e, t, o), n || Promise); }
function _regeneratorAsyncIterator(t, e) { function n(r, o, i, f) { try { var c = t[r](o), u = c.value; return u instanceof _OverloadYield ? e.resolve(u.v).then(function (t) { n("next", t, i, f); }, function (t) { n("throw", t, i, f); }) : e.resolve(u).then(function (t) { c.value = t, i(c); }, function (t) { return n("throw", t, i, f); }); } catch (t) { f(t); } } var r; this.next || (_regeneratorDefine2(_regeneratorAsyncIterator.prototype), _regeneratorDefine2(_regeneratorAsyncIterator.prototype, "function" == typeof Symbol && Symbol.asyncIterator || "@asyncIterator", function () { return this; })), _regeneratorDefine2(this, "_invoke", function (t, o, i) { function f() { return new e(function (e, r) { n(t, i, e, r); }); } return r = r ? r.then(f, f) : f(); }, !0); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function _OverloadYield(e, d) { this.v = e, this.k = d; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Workout Session AJAX Handler
 */
var WorkoutSession = /*#__PURE__*/function () {
  function WorkoutSession() {
    _classCallCheck(this, WorkoutSession);
    this.sessionId = document.getElementById('workout-session').dataset.sessionId;
    this.csrfToken = document.getElementById('workout-session').dataset.csrf;
    this.restTimer = new _rest_timer_js__WEBPACK_IMPORTED_MODULE_0__.RestTimer();
    this.init();
  }
  _createClass(WorkoutSession, [{
    key: "init",
    value: function init() {
      this.attachEventListeners();
      this.checkRestTimer();
    }
  }, {
    key: "checkRestTimer",
    value: function checkRestTimer() {
      var timerData = localStorage.getItem('restTimer');
      if (timerData) {
        var _JSON$parse = JSON.parse(timerData),
          seconds = _JSON$parse.seconds,
          exerciseName = _JSON$parse.exerciseName;
        localStorage.removeItem('restTimer');
        this.restTimer.start(seconds, exerciseName);
      }
    }
  }, {
    key: "shouldStartTimer",
    value: function shouldStartTimer(exerciseDiv, setDiv) {
      var expectedSets = parseInt(exerciseDiv.dataset.expectedSets);
      var currentSetIndex = parseInt(setDiv.dataset.setIndex);
      var exerciseSort = parseInt(exerciseDiv.dataset.sort);
      var totalExercises = parseInt(document.getElementById('workout-session').dataset.totalExercises);

      // Don't start timer if this is the last set of the last exercise
      var isLastSet = currentSetIndex >= expectedSets;
      var isLastExercise = exerciseSort >= totalExercises;
      return !(isLastSet && isLastExercise);
    }
  }, {
    key: "attachEventListeners",
    value: function attachEventListeners() {
      var _this = this;
      // Use event delegation on the workout-session container
      var container = document.getElementById('workout-session');
      container.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-set')) {
          _this.handleAddSet(e);
        } else if (e.target.classList.contains('save-set')) {
          _this.handleSaveSet(e);
        } else if (e.target.classList.contains('delete-set')) {
          _this.handleDeleteSet(e);
        } else if (e.target.classList.contains('add-extra-set')) {
          _this.handleAddExtraSet(e);
        }
      });

      // Complete workout button
      document.getElementById('complete-workout').addEventListener('click', function () {
        _this.handleCompleteWorkout();
      });

      // Skip rest button
      document.getElementById('skip-rest').addEventListener('click', function () {
        _this.restTimer.stop();
      });
    }
  }, {
    key: "handleAddSet",
    value: function () {
      var _handleAddSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee(e) {
        var button, setDiv, exerciseDiv, weightInput, repsInput, weight, reps, exerciseId, workoutExerciseId, restSeconds, sort, response, newSet, exerciseName;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              button = e.target; // Prevent double-clicks
              if (!button.disabled) {
                _context.next = 3;
                break;
              }
              return _context.abrupt("return");
            case 3:
              button.disabled = true;
              setDiv = e.target.closest('.set');
              exerciseDiv = e.target.closest('.exercise');
              weightInput = setDiv.querySelector('.set-weight');
              repsInput = setDiv.querySelector('.set-reps');
              weight = parseFloat(weightInput.value);
              reps = parseInt(repsInput.value);
              if (!(weightInput.value === '' || !reps)) {
                _context.next = 14;
                break;
              }
              alert('Please enter both weight and reps');
              button.disabled = false;
              return _context.abrupt("return");
            case 14:
              exerciseId = exerciseDiv.dataset.exerciseId;
              workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;
              restSeconds = exerciseDiv.dataset.restSeconds;
              sort = exerciseDiv.dataset.sort;
              _context.prev = 18;
              if (workoutExerciseId) {
                _context.next = 26;
                break;
              }
              _context.next = 22;
              return this.createWorkoutExercise(exerciseId, weight, reps, restSeconds, sort);
            case 22:
              response = _context.sent;
              // Update exercise div with new workout_exercise_id
              exerciseDiv.dataset.workoutExerciseId = response.workout_exercise_id;
              _context.next = 29;
              break;
            case 26:
              _context.next = 28;
              return this.addSetToExercise(workoutExerciseId, weight, reps);
            case 28:
              response = _context.sent;
            case 29:
              newSet = response.set; // Transform the set div from "empty" to "completed" state
              this.convertSetToCompleted(setDiv, newSet);

              // Update CSS classes for this exercise's sets
              this.updateSetClasses(exerciseDiv);

              // Start rest timer if not last set of last exercise
              if (this.shouldStartTimer(exerciseDiv, setDiv)) {
                exerciseName = exerciseDiv.querySelector('h2').textContent;
                this.restTimer.start(parseInt(restSeconds), exerciseName);
              }
              _context.next = 39;
              break;
            case 35:
              _context.prev = 35;
              _context.t0 = _context["catch"](18);
              alert('Failed to add set: ' + _context.t0.message);
              button.disabled = false;
            case 39:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[18, 35]]);
      }));
      function handleAddSet(_x) {
        return _handleAddSet.apply(this, arguments);
      }
      return handleAddSet;
    }()
  }, {
    key: "convertSetToCompleted",
    value: function convertSetToCompleted(setDiv, setData) {
      // Add set ID to div
      setDiv.dataset.setId = setData.id;

      // Replace the inner HTML with completed state
      var fieldsDiv = setDiv.querySelector('.set--fields');
      fieldsDiv.innerHTML = this.generateCompletedSetFieldsHtml(setData);

      // Add completed class
      setDiv.classList.remove('set--incomplete', 'set--next');
      setDiv.classList.add('set--complete');
    }
  }, {
    key: "updateSetClasses",
    value: function updateSetClasses(exerciseDiv) {
      var sets = exerciseDiv.querySelectorAll('.set');
      var foundNext = false;
      sets.forEach(function (setDiv) {
        var isCompleted = setDiv.hasAttribute('data-set-id');

        // Remove all state classes first
        setDiv.classList.remove('set--complete', 'set--incomplete', 'set--next');
        if (isCompleted) {
          setDiv.classList.add('set--complete');
        } else {
          setDiv.classList.add('set--incomplete');
          // First incomplete set is the "next" set
          if (!foundNext) {
            setDiv.classList.add('set--next');
            foundNext = true;
          }
        }
      });
    }
  }, {
    key: "generateCompletedSetFieldsHtml",
    value: function generateCompletedSetFieldsHtml(setData) {
      return "\n            <div class=\"set--field-group\">\n                <input type=\"number\"\n                       class=\"set-weight\"\n                       data-set-id=\"".concat(setData.id, "\"\n                       value=\"").concat(setData.weight_kg, "\"\n                       step=\"0.5\"\n                       placeholder=\"Weight (kg)\">\n                <span>kg</span>\n            </div>\n            <div class=\"set--field-group\">\n                <input type=\"number\"\n                       class=\"set-reps\"\n                       data-set-id=\"").concat(setData.id, "\"\n                       value=\"").concat(setData.number_reps, "\"\n                       placeholder=\"Reps\">\n                <span>reps</span>\n            </div>\n            <button class=\"save-set\" data-set-id=\"").concat(setData.id, "\" aria-label=\"Save set\">&#10003;</button>\n            <button class=\"delete-set\" data-set-id=\"").concat(setData.id, "\" aria-label=\"Delete set\">&times;</button>\n        ");
    }
  }, {
    key: "handleSaveSet",
    value: function () {
      var _handleSaveSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee2(e) {
        var button, setDiv, exerciseDiv, setId, weightInput, repsInput, weight, reps, workoutExerciseId, exerciseName, restSeconds;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              button = e.target; // Prevent double-clicks
              if (!button.disabled) {
                _context2.next = 3;
                break;
              }
              return _context2.abrupt("return");
            case 3:
              button.disabled = true;
              setDiv = e.target.closest('.set');
              exerciseDiv = e.target.closest('.exercise');
              setId = e.target.dataset.setId;
              weightInput = setDiv.querySelector(".set-weight[data-set-id=\"".concat(setId, "\"]"));
              repsInput = setDiv.querySelector(".set-reps[data-set-id=\"".concat(setId, "\"]"));
              weight = parseFloat(weightInput.value);
              reps = parseInt(repsInput.value);
              if (!(weightInput.value === '' || !reps)) {
                _context2.next = 15;
                break;
              }
              alert('Please enter both weight and reps');
              button.disabled = false;
              return _context2.abrupt("return");
            case 15:
              workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;
              _context2.prev = 16;
              _context2.next = 19;
              return this.updateSet(workoutExerciseId, setId, weight, reps);
            case 19:
              // Start rest timer after saving (no reload for saves)
              if (this.shouldStartTimer(exerciseDiv, setDiv)) {
                exerciseName = exerciseDiv.querySelector('h2').textContent;
                restSeconds = exerciseDiv.dataset.restSeconds;
                this.restTimer.start(parseInt(restSeconds), exerciseName);
              }
              alert('Set updated!');
              _context2.next = 26;
              break;
            case 23:
              _context2.prev = 23;
              _context2.t0 = _context2["catch"](16);
              alert('Failed to update set: ' + _context2.t0.message);
            case 26:
              _context2.prev = 26;
              button.disabled = false;
              return _context2.finish(26);
            case 29:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this, [[16, 23, 26, 29]]);
      }));
      function handleSaveSet(_x2) {
        return _handleSaveSet.apply(this, arguments);
      }
      return handleSaveSet;
    }()
  }, {
    key: "handleDeleteSet",
    value: function () {
      var _handleDeleteSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee3(e) {
        var setDiv, exerciseDiv, setId, workoutExerciseId, remainingSets;
        return _regeneratorRuntime().wrap(function _callee3$(_context3) {
          while (1) switch (_context3.prev = _context3.next) {
            case 0:
              if (confirm('Delete this set?')) {
                _context3.next = 2;
                break;
              }
              return _context3.abrupt("return");
            case 2:
              setDiv = e.target.closest('.set');
              exerciseDiv = e.target.closest('.exercise');
              setId = e.target.dataset.setId;
              workoutExerciseId = exerciseDiv.dataset.workoutExerciseId;
              _context3.prev = 6;
              _context3.next = 9;
              return this.deleteSet(workoutExerciseId, setId);
            case 9:
              // Remove the set from DOM
              setDiv.remove();

              // Check if this was the last set for this exercise
              remainingSets = exerciseDiv.querySelectorAll('.set[data-set-id]');
              if (remainingSets.length === 0) {
                // Reset exercise to "not started" state by removing workout-exercise-id
                exerciseDiv.removeAttribute('data-workout-exercise-id');
              }

              // Update CSS classes for remaining sets
              this.updateSetClasses(exerciseDiv);
              _context3.next = 18;
              break;
            case 15:
              _context3.prev = 15;
              _context3.t0 = _context3["catch"](6);
              alert('Failed to delete set: ' + _context3.t0.message);
            case 18:
            case "end":
              return _context3.stop();
          }
        }, _callee3, this, [[6, 15]]);
      }));
      function handleDeleteSet(_x3) {
        return _handleDeleteSet.apply(this, arguments);
      }
      return handleDeleteSet;
    }()
  }, {
    key: "handleAddExtraSet",
    value: function () {
      var _handleAddExtraSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee4(e) {
        var exerciseDiv, setsContainer, currentSets, nextSetIndex, setHtml;
        return _regeneratorRuntime().wrap(function _callee4$(_context4) {
          while (1) switch (_context4.prev = _context4.next) {
            case 0:
              exerciseDiv = e.target.closest('.exercise');
              setsContainer = exerciseDiv.querySelector('.sets-container');
              currentSets = setsContainer.querySelectorAll('.set').length;
              nextSetIndex = currentSets + 1; // Add new empty set to DOM
              setHtml = "\n            <div class=\"set set--incomplete\" data-set-index=\"".concat(nextSetIndex, "\">\n                <div class=\"set--number\">Set ").concat(nextSetIndex, "</div>\n                <div class=\"set--fields\">\n                    <div class=\"set--field-group\">\n                        <input type=\"number\"\n                               class=\"set-weight\"\n                               step=\"0.5\"\n                               placeholder=\"Weight (kg)\">\n                        <span>kg</span>\n                    </div>\n                    <div class=\"set--field-group\">\n                        <input type=\"number\"\n                               class=\"set-reps\"\n                               placeholder=\"Reps\">\n                        <span>reps</span>\n                    </div>\n                    <button class=\"add-set\" aria-label=\"Add set\">&#10003;</button>\n                    <button class=\"dummy-delete-set\">&times;</button>\n                </div>\n            </div>\n        "); // Insert before the "Add Another Set" button
              e.target.insertAdjacentHTML('beforebegin', setHtml);

              // Update CSS classes for all sets in this exercise
              this.updateSetClasses(exerciseDiv);
            case 7:
            case "end":
              return _context4.stop();
          }
        }, _callee4, this);
      }));
      function handleAddExtraSet(_x4) {
        return _handleAddExtraSet.apply(this, arguments);
      }
      return handleAddExtraSet;
    }()
  }, {
    key: "handleCompleteWorkout",
    value: function () {
      var _handleCompleteWorkout = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee5() {
        var response;
        return _regeneratorRuntime().wrap(function _callee5$(_context5) {
          while (1) switch (_context5.prev = _context5.next) {
            case 0:
              if (confirm('Complete this workout?')) {
                _context5.next = 2;
                break;
              }
              return _context5.abrupt("return");
            case 2:
              _context5.prev = 2;
              _context5.next = 5;
              return this.apiRequest('POST', "/workouts/".concat(this.sessionId, "/complete"));
            case 5:
              response = _context5.sent;
              if (response.success) {
                window.location.href = response.redirect;
              }
              _context5.next = 12;
              break;
            case 9:
              _context5.prev = 9;
              _context5.t0 = _context5["catch"](2);
              alert('Failed to complete workout: ' + _context5.t0.message);
            case 12:
            case "end":
              return _context5.stop();
          }
        }, _callee5, this, [[2, 9]]);
      }));
      function handleCompleteWorkout() {
        return _handleCompleteWorkout.apply(this, arguments);
      }
      return handleCompleteWorkout;
    }() // API methods
  }, {
    key: "createWorkoutExercise",
    value: function () {
      var _createWorkoutExercise = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee6(exerciseId, weightKg, numberReps, restSeconds, sort) {
        return _regeneratorRuntime().wrap(function _callee6$(_context6) {
          while (1) switch (_context6.prev = _context6.next) {
            case 0:
              _context6.next = 2;
              return this.apiRequest('POST', "/workouts/".concat(this.sessionId, "/exercises"), {
                exercise_id: exerciseId,
                weight_kg: weightKg,
                number_reps: numberReps,
                rest_seconds: restSeconds,
                sort: sort
              });
            case 2:
              return _context6.abrupt("return", _context6.sent);
            case 3:
            case "end":
              return _context6.stop();
          }
        }, _callee6, this);
      }));
      function createWorkoutExercise(_x5, _x6, _x7, _x8, _x9) {
        return _createWorkoutExercise.apply(this, arguments);
      }
      return createWorkoutExercise;
    }()
  }, {
    key: "addSetToExercise",
    value: function () {
      var _addSetToExercise = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee7(workoutExerciseId, weightKg, numberReps) {
        return _regeneratorRuntime().wrap(function _callee7$(_context7) {
          while (1) switch (_context7.prev = _context7.next) {
            case 0:
              _context7.next = 2;
              return this.apiRequest('POST', "/workouts/".concat(this.sessionId, "/exercises/").concat(workoutExerciseId, "/sets"), {
                weight_kg: weightKg,
                number_reps: numberReps
              });
            case 2:
              return _context7.abrupt("return", _context7.sent);
            case 3:
            case "end":
              return _context7.stop();
          }
        }, _callee7, this);
      }));
      function addSetToExercise(_x10, _x11, _x12) {
        return _addSetToExercise.apply(this, arguments);
      }
      return addSetToExercise;
    }()
  }, {
    key: "updateSet",
    value: function () {
      var _updateSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee8(workoutExerciseId, setId, weightKg, numberReps) {
        return _regeneratorRuntime().wrap(function _callee8$(_context8) {
          while (1) switch (_context8.prev = _context8.next) {
            case 0:
              _context8.next = 2;
              return this.apiRequest('PATCH', "/workouts/".concat(this.sessionId, "/exercises/").concat(workoutExerciseId, "/sets/").concat(setId), {
                weight_kg: weightKg,
                number_reps: numberReps
              });
            case 2:
              return _context8.abrupt("return", _context8.sent);
            case 3:
            case "end":
              return _context8.stop();
          }
        }, _callee8, this);
      }));
      function updateSet(_x13, _x14, _x15, _x16) {
        return _updateSet.apply(this, arguments);
      }
      return updateSet;
    }()
  }, {
    key: "deleteSet",
    value: function () {
      var _deleteSet = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee9(workoutExerciseId, setId) {
        return _regeneratorRuntime().wrap(function _callee9$(_context9) {
          while (1) switch (_context9.prev = _context9.next) {
            case 0:
              _context9.next = 2;
              return this.apiRequest('DELETE', "/workouts/".concat(this.sessionId, "/exercises/").concat(workoutExerciseId, "/sets/").concat(setId));
            case 2:
              return _context9.abrupt("return", _context9.sent);
            case 3:
            case "end":
              return _context9.stop();
          }
        }, _callee9, this);
      }));
      function deleteSet(_x17, _x18) {
        return _deleteSet.apply(this, arguments);
      }
      return deleteSet;
    }()
  }, {
    key: "apiRequest",
    value: function () {
      var _apiRequest = _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee10(method, url) {
        var data,
          options,
          response,
          error,
          _args10 = arguments;
        return _regeneratorRuntime().wrap(function _callee10$(_context10) {
          while (1) switch (_context10.prev = _context10.next) {
            case 0:
              data = _args10.length > 2 && _args10[2] !== undefined ? _args10[2] : null;
              options = {
                method: method,
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': this.csrfToken,
                  'Accept': 'application/json'
                }
              };
              if (data) {
                options.body = JSON.stringify(data);
              }
              _context10.next = 5;
              return fetch(url, options);
            case 5:
              response = _context10.sent;
              if (response.ok) {
                _context10.next = 11;
                break;
              }
              _context10.next = 9;
              return response.json();
            case 9:
              error = _context10.sent;
              throw new Error(error.message || 'Request failed');
            case 11:
              _context10.next = 13;
              return response.json();
            case 13:
              return _context10.abrupt("return", _context10.sent);
            case 14:
            case "end":
              return _context10.stop();
          }
        }, _callee10, this);
      }));
      function apiRequest(_x19, _x20) {
        return _apiRequest.apply(this, arguments);
      }
      return apiRequest;
    }()
  }]);
  return WorkoutSession;
}(); // Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  new WorkoutSession();
});
})();

/******/ })()
;