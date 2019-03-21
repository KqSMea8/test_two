/*!
 * weui.js v1.1.0 (https://weui.io)
 * Copyright 2017, wechat ui team
 * MIT license
 */
! function (e, t) {
    "object" == typeof exports && "object" == typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define([], t) : "object" == typeof exports ? exports.weui = t() : e.weui = t()
}(this, function () {
    return function (e) {
        function t(i) {
            if (n[i]) return n[i].exports;
            var a = n[i] = {
                exports: {},
                id: i,
                loaded: !1
            };
            return e[i].call(a.exports, a, a.exports, t), a.loaded = !0, a.exports
        }
        var n = {};
        return t.m = e, t.c = n, t.p = "", t(0)
    }([
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var a = n(1),
                o = i(a),
                r = n(7),
                u = i(r),
                l = n(8),
                d = i(l),
                s = n(9),
                f = i(s),
                c = n(11),
                p = i(c),
                h = n(13),
                v = i(h),
                m = n(15),
                _ = i(m),
                w = n(17),
                y = i(w),
                g = n(18),
                b = i(g),
                k = n(19),
                x = i(k),
                C = n(20),
                j = i(C),
                E = n(24),
                M = n(30),
                S = i(M),
                O = n(32),
                P = i(O);
            t.default = {
                dialog: o.default,
                alert: u.default,
                confirm: d.default,
                toast: f.default,
                loading: p.default,
                actionSheet: v.default,
                topTips: _.default,
                searchBar: y.default,
                tab: b.default,
                form: x.default,
                uploader: j.default,
                picker: E.picker,
                datePicker: E.datePicker,
                gallery: S.default,
                slider: P.default
            }, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                function e() {
                    e = r.default.noop, u.addClass("weui-animate-fade-out"), o.addClass("weui-animate-fade-out").on("animationend webkitAnimationEnd", function () {
                        a.remove(), d = !1
                    })
                }

                function t() {
                    e()
                }
                var n = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {};
                if (d) return d;
                var i = r.default.os.android;
                n = r.default.extend({
                    title: null,
                    content: "",
                    className: "",
                    buttons: [{
                        label: "确定",
                        type: "primary",
                        onClick: r.default.noop
                    }],
                    isAndroid: i
                }, n);
                var a = (0, r.default)(r.default.render(l.default, n)),
                    o = a.find(".weui-dialog"),
                    u = a.find(".weui-mask");
                return (0, r.default)("body").append(a), u.addClass("weui-animate-fade-in"), o.addClass("weui-animate-fade-in"), a.on("click", ".weui-dialog__btn", function (e) {
                    var i = (0, r.default)(this).index();
                    n.buttons[i].onClick ? n.buttons[i].onClick.call(this, e) !== !1 && t() : t()
                }), d = a[0], d.hide = t, d
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(6),
                l = i(u),
                d = void 0;
            t.default = a, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                var t = this.os = {},
                    n = e.match(/(Android);?[\s\/]+([\d.]+)?/);
                n && (t.android = !0, t.version = n[2])
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                return typeof e
            } : function (e) {
                return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
            };
            n(3);
            var r = n(4),
                u = i(r),
                l = n(5),
                d = i(l);
            a.call(d.default, navigator.userAgent), (0, u.default)(d.default.fn, {
                append: function (e) {
                    return e instanceof HTMLElement || (e = e[0]), this.forEach(function (t) {
                        t.appendChild(e)
                    }), this
                }, remove: function () {
                    return this.forEach(function (e) {
                        e.parentNode.removeChild(e)
                    }), this
                }, find: function (e) {
                    return (0, d.default)(e, this)
                }, addClass: function (e) {
                    return this.forEach(function (t) {
                        t.classList.add(e)
                    }), this
                }, removeClass: function (e) {
                    return this.forEach(function (t) {
                        t.classList.remove(e)
                    }), this
                }, eq: function (e) {
                    return (0, d.default)(this[e])
                }, show: function () {
                    return this.forEach(function (e) {
                        e.style.display = "block"
                    }), this
                }, hide: function () {
                    return this.forEach(function (e) {
                        e.style.display = "none"
                    }), this
                }, html: function (e) {
                    return this.forEach(function (t) {
                        t.innerHTML = e
                    }), this
                }, css: function (e) {
                    var t = this;
                    return Object.keys(e).forEach(function (n) {
                        t.forEach(function (t) {
                            t.style[n] = e[n]
                        })
                    }), this
                }, on: function (e, t, n) {
                    var i = "string" == typeof t && "function" == typeof n;
                    return i || (n = t), this.forEach(function (a) {
                        e.split(" ").forEach(function (e) {
                            a.addEventListener(e, function (e) {
                                i ? this.contains(e.target.closest(t)) && n.call(e.target, e) : n.call(this, e)
                            })
                        })
                    }), this
                }, off: function (e, t, n) {
                    return "function" == typeof t && (n = t, t = null), this.forEach(function (i) {
                        e.split(" ").forEach(function (e) {
                            "string" == typeof t ? i.querySelectorAll(t).forEach(function (t) {
                                t.removeEventListener(e, n)
                            }) : i.removeEventListener(e, n)
                        })
                    }), this
                }, index: function () {
                    var e = this[0],
                        t = e.parentNode;
                    return Array.prototype.indexOf.call(t.children, e)
                }, offAll: function () {
                    var e = this;
                    return this.forEach(function (t, n) {
                        var i = t.cloneNode(!0);
                        t.parentNode.replaceChild(i, t), e[n] = i
                    }), this
                }, val: function () {
                    var e = arguments;
                    return arguments.length ? (this.forEach(function (t) {
                        t.value = e[0]
                    }), this) : this[0].value
                }, attr: function () {
                    var e = arguments,
                        t = this;
                    if ("object" == o(arguments[0])) {
                        var n = function () {
                            var n = e[0],
                                i = t;
                            return Object.keys(n).forEach(function (e) {
                                i.forEach(function (t) {
                                    t.setAttribute(e, n[e])
                                })
                            }), {
                                v: t
                            }
                        }();
                        if ("object" === ("undefined" == typeof n ? "undefined" : o(n))) return n.v
                    }
                    return "string" == typeof arguments[0] && arguments.length < 2 ? this[0].getAttribute(arguments[0]) : (this.forEach(function (t) {
                        t.setAttribute(e[0], e[1])
                    }), this)
                }
            }), (0, u.default)(d.default, {
                extend: u.default,
                noop: function () {}, render: function (e, t) {
                    var n = "var p=[];with(this){p.push('" + e.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');";
                    return new Function(n).apply(t)
                }, getStyle: function (e, t) {
                    var n, i = (e.ownerDocument || document).defaultView;
                    return i && i.getComputedStyle ? (t = t.replace(/([A-Z])/g, "-$1").toLowerCase(), i.getComputedStyle(e, null).getPropertyValue(t)) : e.currentStyle ? (t = t.replace(/\-(\w)/g, function (e, t) {
                        return t.toUpperCase()
                    }), n = e.currentStyle[t], /^\d+(em|pt|%|ex)?$/i.test(n) ? function (t) {
                        var n = e.style.left,
                            i = e.runtimeStyle.left;
                        return e.runtimeStyle.left = e.currentStyle.left, e.style.left = t || 0, t = e.style.pixelLeft + "px", e.style.left = n, e.runtimeStyle.left = i, t
                    }(n) : n) : void 0
                }
            }), t.default = d.default, e.exports = t.default
        },
        function (e, t) {
            ! function (e) {
                "function" != typeof e.matches && (e.matches = e.msMatchesSelector || e.mozMatchesSelector || e.webkitMatchesSelector || function (e) {
                        for (var t = this, n = (t.document || t.ownerDocument).querySelectorAll(e), i = 0; n[i] && n[i] !== t;)++i;
                        return Boolean(n[i])
                    }), "function" != typeof e.closest && (e.closest = function (e) {
                    for (var t = this; t && 1 === t.nodeType;) {
                        if (t.matches(e)) return t;
                        t = t.parentNode
                    }
                    return null
                })
            }(window.Element.prototype)
        },
        function (e, t) {
            "use strict";

            function n(e) {
                if (null === e || void 0 === e) throw new TypeError("Object.assign cannot be called with null or undefined");
                return Object(e)
            }

            function i() {
                try {
                    if (!Object.assign) return !1;
                    var e = new String("abc");
                    if (e[5] = "de", "5" === Object.getOwnPropertyNames(e)[0]) return !1;
                    for (var t = {}, n = 0; n < 10; n++) t["_" + String.fromCharCode(n)] = n;
                    var i = Object.getOwnPropertyNames(t).map(function (e) {
                        return t[e]
                    });
                    if ("0123456789" !== i.join("")) return !1;
                    var a = {};
                    return "abcdefghijklmnopqrst".split("").forEach(function (e) {
                        a[e] = e
                    }), "abcdefghijklmnopqrst" === Object.keys(Object.assign({}, a)).join("")
                } catch (e) {
                    return !1
                }
            }
            var a = Object.prototype.hasOwnProperty,
                o = Object.prototype.propertyIsEnumerable;
            e.exports = i() ? Object.assign : function (e, t) {
                for (var i, r, u = n(e), l = 1; l < arguments.length; l++) {
                    i = Object(arguments[l]);
                    for (var d in i) a.call(i, d) && (u[d] = i[d]);
                    if (Object.getOwnPropertySymbols) {
                        r = Object.getOwnPropertySymbols(i);
                        for (var s = 0; s < r.length; s++) o.call(i, r[s]) && (u[r[s]] = i[r[s]])
                    }
                }
                return u
            }
        },
        function (e, t, n) {
            var i, a;
            ! function (n, o) {
                o = function (e, t, n) {
                    function i(a, o, r) {
                        return r = Object.create(i.fn), a && r.push.apply(r, a[t] ? [a] : "" + a === a ? /</.test(a) ? ((o = e.createElement(o || t)).innerHTML = a, o.children) : o ? (o = i(o)[0]) ? o[n](a) : r : e[n](a) : "function" == typeof a ? e.readyState[7] ? a() : e[t]("DOMContentLoaded", a) : a), r
                    }
                    return i.fn = [], i.one = function (e, t) {
                        return i(e, t)[0] || null
                    }, i
                }(document, "addEventListener", "querySelectorAll"), i = [], a = function () {
                    return o
                }.apply(t, i), !(void 0 !== a && (e.exports = a))
            }(this)
        },
        function (e, t) {
            e.exports = '<div class="<%=className%>"> <div class=weui-mask></div> <div class="weui-dialog <% if(isAndroid){ %> weui-skin_android <% } %>"> <% if(title){ %> <div class=weui-dialog__hd><strong class=weui-dialog__title><%=title%></strong></div> <% } %> <div class=weui-dialog__bd><%=content%></div> <div class=weui-dialog__ft> <% for(var i = 0; i < buttons.length; i++){ %> <a href=javascript:; class="weui-dialog__btn weui-dialog__btn_<%=buttons[i][\'type\']%>"><%=buttons[i][\'label\']%></a> <% } %> </div> </div> </div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
                    t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : u.default.noop,
                    n = arguments[2];
                return "object" === ("undefined" == typeof t ? "undefined" : o(t)) && (n = t, t = u.default.noop), n = u.default.extend({
                    content: e,
                    buttons: [{
                        label: "确定",
                        type: "primary",
                        onClick: t
                    }]
                }, n), (0, d.default)(n)
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                    return typeof e
                } : function (e) {
                    return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
                },
                r = n(2),
                u = i(r),
                l = n(1),
                d = i(l);
            t.default = a, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
                    t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : u.default.noop,
                    n = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : u.default.noop,
                    i = arguments[3];
                return "object" === ("undefined" == typeof t ? "undefined" : o(t)) ? (i = t, t = u.default.noop) : "object" === ("undefined" == typeof n ? "undefined" : o(n)) && (i = n, n = u.default.noop), i = u.default.extend({
                    content: e,
                    buttons: [{
                        label: "取消",
                        type: "default",
                        onClick: n
                    }, {
                        label: "确定",
                        type: "primary",
                        onClick: t
                    }]
                }, i), (0, d.default)(i)
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                    return typeof e
                } : function (e) {
                    return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
                },
                r = n(2),
                u = i(r),
                l = n(1),
                d = i(l);
            t.default = a, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
                    t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                if (d) return d;
                "number" == typeof t && (t = {
                    duration: t
                }), "function" == typeof t && (t = {
                    callback: t
                }), t = r.default.extend({
                    content: e,
                    duration: 3e3,
                    callback: r.default.noop,
                    className: ""
                }, t);
                var n = (0, r.default)(r.default.render(l.default, t)),
                    i = n.find(".weui-toast"),
                    a = n.find(".weui-mask");
                return (0, r.default)("body").append(n), i.addClass("weui-animate-fade-in"), a.addClass("weui-animate-fade-in"), setTimeout(function () {
                    a.addClass("weui-animate-fade-out"), i.addClass("weui-animate-fade-out").on("animationend webkitAnimationEnd", function () {
                        n.remove(), d = !1, t.callback()
                    })
                }, t.duration), d = n[0], n[0]
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(10),
                l = i(u),
                d = void 0;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<div class="<%= className %>"> <div class=weui-mask_transparent></div> <div class=weui-toast> <i class="weui-icon_toast weui-icon-success-no-circle"></i> <p class=weui-toast__content><%=content%></p> </div> </div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                function e() {
                    e = r.default.noop, u.addClass("weui-animate-fade-out"), o.addClass("weui-animate-fade-out").on("animationend webkitAnimationEnd", function () {
                        a.remove(), d = !1
                    })
                }

                function t() {
                    e()
                }
                var n = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
                    i = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                if (d) return d;
                i = r.default.extend({
                    content: n,
                    className: ""
                }, i);
                var a = (0, r.default)(r.default.render(l.default, i)),
                    o = a.find(".weui-toast"),
                    u = a.find(".weui-mask");
                return (0, r.default)("body").append(a), o.addClass("weui-animate-fade-in"), u.addClass("weui-animate-fade-in"), d = a[0], d.hide = t, d
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(12),
                l = i(u),
                d = void 0;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<div class="weui-loading_toast <%= className %>"> <div class=weui-mask_transparent></div> <div class=weui-toast> <i class="weui-loading weui-icon_toast"></i> <p class=weui-toast__content><%=content%></p> </div> </div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a() {
                function e() {
                    e = r.default.noop, s.addClass(o ? "weui-animate-fade-out" : "weui-animate-slide-down"), f.addClass("weui-animate-fade-out").on("animationend webkitAnimationEnd", function () {
                        u.remove(), d = !1
                    })
                }

                function t() {
                    e()
                }
                var n = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : [],
                    i = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : [],
                    a = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {};
                if (d) return d;
                var o = r.default.os.android;
                a = r.default.extend({
                    menus: n,
                    actions: i,
                    className: "",
                    isAndroid: o
                }, a);
                var u = (0, r.default)(r.default.render(l.default, a)),
                    s = u.find(".weui-actionsheet"),
                    f = u.find(".weui-mask");
                return (0, r.default)("body").append(u), r.default.getStyle(s[0], "transform"), s.addClass(o ? "weui-animate-fade-in" : "weui-animate-slide-up"), f.addClass("weui-animate-fade-in").on("click", t), u.find(".weui-actionsheet__menu").on("click", ".weui-actionsheet__cell", function (e) {
                    var i = (0, r.default)(this).index();
                    n[i].onClick.call(this, e), t()
                }), u.find(".weui-actionsheet__action").on("click", ".weui-actionsheet__cell", function (e) {
                    var n = (0, r.default)(this).index();
                    i[n].onClick.call(this, e), t()
                }), d = u[0], d.hide = t, d
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(14),
                l = i(u),
                d = void 0;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<div class="<% if(isAndroid){ %>weui-skin_android <% } %><%= className %>"> <div class=weui-mask></div> <div class=weui-actionsheet> <div class=weui-actionsheet__menu> <% for(var i = 0; i < menus.length; i++){ %> <div class=weui-actionsheet__cell><%= menus[i].label %></div> <% } %> </div> <div class=weui-actionsheet__action> <% for(var j = 0; j < actions.length; j++){ %> <div class=weui-actionsheet__cell><%= actions[j].label %></div> <% } %> </div> </div> </div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                function t() {
                    t = r.default.noop, a.remove(), i.callback(), d = null
                }

                function n() {
                    t()
                }
                var i = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                "number" == typeof i && (i = {
                    duration: i
                }), "function" == typeof i && (i = {
                    callback: i
                }), i = r.default.extend({
                    content: e,
                    duration: 3e3,
                    callback: r.default.noop,
                    className: ""
                }, i);
                var a = (0, r.default)(r.default.render(l.default, i));
                return (0, r.default)("body").append(a), d && (clearTimeout(d.timeout), d.hide()), d = {
                    hide: n
                }, d.timeout = setTimeout(n, i.duration), a[0].hide = n, a[0]
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(16),
                l = i(u),
                d = null;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<div class="weui-toptips weui-toptips_warn <%= className %>" style=display:block><%= content %></div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                var t = (0, r.default)(e);
                return t.forEach(function (e) {
                    function t() {
                        a.val(""), n.removeClass("weui-search-bar_focusing")
                    }
                    var n = (0, r.default)(e),
                        i = n.find(".weui-search-bar__label"),
                        a = n.find(".weui-search-bar__input"),
                        o = n.find(".weui-icon-clear"),
                        u = n.find(".weui-search-bar__cancel-btn");
                    i.on("click", function () {
                        n.addClass("weui-search-bar_focusing"), a[0].focus()
                    }), a.on("blur", function () {
                        this.value.length || t()
                    }), o.on("click", function () {
                        a.val(""), a[0].focus()
                    }), u.on("click", function () {
                        t(), a[0].blur()
                    })
                }), t
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o);
            t.default = a, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                    n = (0, r.default)(e);
                return t = r.default.extend({
                    defaultIndex: 0,
                    onChange: r.default.noop
                }, t), n.forEach(function (e) {
                    var n = (0, r.default)(e),
                        i = n.find(".weui-navbar__item, .weui-tabbar__item"),
                        a = n.find(".weui-tab__content");
                    i.eq(t.defaultIndex).addClass("weui-bar__item_on"), a.eq(t.defaultIndex).show(), i.on("click", function () {
                        var e = (0, r.default)(this),
                            n = e.index();
                        i.removeClass("weui-bar__item_on"), e.addClass("weui-bar__item_on"), a.hide(), a.eq(n).show(), t.onChange.call(this, n)
                    })
                }), this
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o);
            t.default = a, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                return e && e.classList ? e.classList.contains("weui-cell") ? e : a(e.parentNode) : null
            }

            function o(e, t, n) {
                var i = e[0],
                    a = e.val();
                if ("INPUT" == i.tagName || "TEXTAREA" == i.tagName) {
                    var o = i.getAttribute("pattern") || "";
                    if ("radio" == i.type) {
                        for (var r = t.find('input[type="radio"][name="' + i.name + '"]'), u = 0, l = r.length; u < l; ++u)
                            if (r[u].checked) return null;
                        return "empty"
                    }
                    if ("checkbox" != i.type) {
                        if (e.val().length) {
                            if (o) {
                                if (/^REG_/.test(o)) {
                                    if (!n) throw "RegExp " + o + " is empty.";
                                    if (o = o.replace(/^REG_/, ""), !n[o]) throw "RegExp " + o + " has not found.";
                                    o = n[o]
                                }
                                return new RegExp(o).test(a) ? null : "notMatch"
                            }
                            return null
                        }
                        return "empty"
                    }
                    if (!o) return i.checked ? null : "empty";
                    var s = function () {
                        var e = t.find('input[type="checkbox"][name="' + i.name + '"]'),
                            n = o.replace(/[{\s}]/g, "").split(","),
                            a = 0;
                        if (2 != n.length) throw i.outerHTML + " regexp is wrong.";
                        return e.forEach(function (e) {
                            e.checked && ++a
                        }), a ? "" === n[1] ? a >= parseInt(n[0]) ? {
                            v: null
                        } : {
                            v: "notMatch"
                        } : parseInt(n[0]) <= a && a <= parseInt(n[1]) ? {
                            v: null
                        } : {
                            v: "notMatch"
                        } : {
                            v: "empty"
                        }
                    }();
                    if ("object" === ("undefined" == typeof s ? "undefined" : d(s))) return s.v
                } else if (a.length) return null;
                return "empty"
            }

            function r(e) {
                if (e) {
                    var t = (0, f.default)(e.ele),
                        n = e.msg,
                        i = t.attr(n + "Tips") || t.attr("tips") || t.attr("placeholder");
                    if (i && (0, p.default)(i), "checkbox" == e.ele.type || "radio" == e.ele.type) return;
                    var o = a(e.ele);
                    o && o.classList.add("weui-cell_warn")
                }
            }

            function u(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : f.default.noop,
                    n = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {},
                    i = (0, f.default)(e);
                return i.forEach(function (e) {
                    var i = (0, f.default)(e),
                        a = i.find("[required]");
                    "function" != typeof t && (t = r);
                    for (var u = 0, l = a.length; u < l; ++u) {
                        var d = a.eq(u),
                            s = o(d, i, n.regexp),
                            c = {
                                ele: d[0],
                                msg: s
                            };
                        if (s) return void(t(c) || r(c))
                    }
                    t(null)
                }), this
            }

            function l(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                    n = (0, f.default)(e);
                return n.forEach(function (e) {
                    var n = (0, f.default)(e);
                    n.find("[required]").on("blur", function () {
                        if ("checkbox" != this.type && "radio" != this.type) {
                            var e = (0, f.default)(this);
                            if (!(e.val().length < 1)) {
                                var i = o(e, n, t.regexp);
                                i && r({
                                    ele: e[0],
                                    msg: i
                                })
                            }
                        }
                    }).on("focus", function () {
                        var e = a(this);
                        e && e.classList.remove("weui-cell_warn")
                    })
                }), this
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var d = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
                    return typeof e
                } : function (e) {
                    return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
                },
                s = n(2),
                f = i(s),
                c = n(15),
                p = i(c);
            t.default = {
                validate: u,
                checkIfBlur: l
            }, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e, t) {
                function n(e, t) {
                    var n = e.find('[data-id="' + t + '"]'),
                        i = n.find(".weui-uploader__file-content");
                    return i.length || (i = (0, r.default)('<div class="weui-uploader__file-content"></div>'), n.append(i)), n.addClass("weui-uploader__file_status"), i
                }

                function i(e, t) {
                    var n = e.find('[data-id="' + t + '"]').removeClass("weui-uploader__file_status");
                    n.find(".weui-uploader__file-content").remove()
                }

                function a(e) {
                    e.url = u.createObjectURL(e), e.status = "ready", e.upload = function () {
                        (0, f.default)(r.default.extend({
                            $uploader: o,
                            file: e
                        }, t))
                    }, e.stop = function () {
                        this.xhr.abort()
                    }, t.onQueued(e), t.auto && e.upload()
                }
                var o = (0, r.default)(e),
                    u = window.URL || window.webkitURL || window.mozURL;
                t = r.default.extend({
                    url: "",
                    auto: !0,
                    type: "file",
                    fileVal: "file",
                    onBeforeQueued: r.default.noop,
                    onQueued: r.default.noop,
                    onBeforeSend: r.default.noop,
                    onSuccess: r.default.noop,
                    onProgress: r.default.noop,
                    onError: r.default.noop
                }, t), t.compress !== !1 && (t.compress = r.default.extend({
                    width: 1600,
                    height: 1600,
                    quality: .8
                }, t.compress)), t.onBeforeQueued && ! function () {
                    var e = t.onBeforeQueued;
                    t.onBeforeQueued = function (t, n) {
                        var i = e.call(t, n);
                        if (i === !1) return !1;
                        if (i !== !0) {
                            var a = (0, r.default)(r.default.render(l.default, {
                                id: t.id
                            }));
                            o.find(".weui-uploader__files").append(a)
                        }
                    }
                }(), t.onQueued && ! function () {
                    var e = t.onQueued;
                    t.onQueued = function (n) {
                        if (!e.call(n)) {
                            var a = o.find('[data-id="' + n.id + '"]');
                            a.css({
                                backgroundImage: 'url("' + (n.base64 || n.url) + '")'
                            }), t.auto || i(o, n.id)
                        }
                    }
                }(), t.onBeforeSend && ! function () {
                    var e = t.onBeforeSend;
                    t.onBeforeSend = function (t, n, i) {
                        var a = e.call(t, n, i);
                        if (a === !1) return !1
                    }
                }(), t.onSuccess && ! function () {
                    var e = t.onSuccess;
                    t.onSuccess = function (t, n) {
                        t.status = "success", e.call(t, n) || i(o, t.id)
                    }
                }(), t.onProgress && ! function () {
                    var e = t.onProgress;
                    t.onProgress = function (t, i) {
                        e.call(t, i) || n(o, t.id).html(i + "%")
                    }
                }(), t.onError && ! function () {
                    var e = t.onError;
                    t.onError = function (t, i) {
                        t.status = "fail", e.call(t, i) || n(o, t.id).html('<i class="weui-icon-warn"></i>')
                    }
                }(), o.find('input[type="file"]').on("change", function (e) {
                    var n = e.target.files;
                    0 !== n.length && (t.compress === !1 && "file" == t.type ? Array.prototype.forEach.call(n, function (e) {
                        e.id = ++c, t.onBeforeQueued(e, n) !== !1 && a(e)
                    }) : Array.prototype.forEach.call(n, function (e) {
                        e.id = ++c, t.onBeforeQueued(e, n) !== !1 && (0, d.compress)(e, t, function (e) {
                            e && a(e)
                        })
                    }), this.value = "")
                })
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(21),
                l = i(u),
                d = n(22),
                s = n(23),
                f = i(s),
                c = 0;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<li class="weui-uploader__file weui-uploader__file_status" data-id="<%= id %>"> <div class=weui-uploader__file-content> <i class=weui-loading style=width:30px;height:30px></i> </div> </li> '
            //e.exports = '<li class="weui-uploader__file weui-uploader__file_status" data-id="<%= id %>"> </li> '
        },
        function (e, t) {
            "use strict";

            function n(e) {
                var t, n = e.naturalHeight,
                    i = document.createElement("canvas");
                i.width = 1, i.height = n;
                var a = i.getContext("2d");
                a.drawImage(e, 0, 0);
                try {
                    t = a.getImageData(0, 0, 1, n).data
                } catch (e) {
                    return 1
                }
                for (var o = 0, r = n, u = n; u > o;) {
                    var l = t[4 * (u - 1) + 3];
                    0 === l ? r = u : o = u, u = r + o >> 1
                }
                var d = u / n;
                return 0 === d ? 1 : d
            }

            function i(e) {
                for (var t = atob(e.split(",")[1]), n = e.split(",")[0].split(":")[1].split(";")[0], i = new ArrayBuffer(t.length), a = new Uint8Array(i), o = 0; o < t.length; o++) a[o] = t.charCodeAt(o);
                return new Blob([i], {
                    type: n
                })
            }

            function a(e, t, a) {
                console.log(111);
                console.log(e);

                var o = new FileReader;
                console.log(o);

                console.log(222);

                o.onload = function (o) {
                    if (t.compress === !1) return e.base64 = o.target.result, void a(e);
                    var r = new Image;
                    r.onload = function () {
                        var o = n(r),
                            u = document.createElement("canvas"),
                            l = u.getContext("2d"),
                            d = t.compress.width,
                            s = t.compress.height,
                            f = r.width,
                            c = r.height,
                            p = void 0;
                        if (f < c && c > s ? (f = parseInt(s * r.width / r.height), c = s) : f >= c && f > d && (c = parseInt(d * r.height / r.width), f = d), u.width = f, u.height = c, l.drawImage(r, 0, 0, f, c / o), p = /image\/jpeg/.test(e.type) || /image\/jpg/.test(e.type) ? u.toDataURL("image/jpeg", t.compress.quality) : u.toDataURL(e.type), "file" == t.type)
                            if (/;base64,null/.test(p) || /;base64,$/.test(p)) a(e);
                            else {
                                var h = i(p);
                                h.id = e.id, h.name = e.name, h.lastModified = e.lastModified, h.lastModifiedDate = e.lastModifiedDate, a(h)
                            } else /;base64,null/.test(p) || /;base64,$/.test(p) ? (t.onError(e, new Error("Compress fail, dataURL is " + p + ".")), a()) : (e.base64 = p, a(e))
                    }, r.src = o.target.result
                }, o.readAsDataURL(e)
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.detectVerticalSquash = n, t.dataURItoBlob = i, t.compress = a
        },
        function (e, t) {
            "use strict";

            function n(e) {
                var t = e.url,
                    n = e.file,
                    i = e.fileVal,
                    a = e.onBeforeSend,
                    o = e.onProgress,
                    r = e.onError,
                    u = e.onSuccess,
                    l = n.name,
                    d = n.type,
                    s = n.lastModifiedDate,
                    f = {
                        name: l,
                        type: d,
                        size: "file" == e.type ? n.size : n.base64.length,
                        lastModifiedDate: s
                    },
                    c = {};
                if (a(n, f, c) !== !1) {
                    n.status = "progress", o(n, 0);
                    var p = new FormData,
                        h = new XMLHttpRequest;
                    n.xhr = h, Object.keys(f).forEach(function (e) {
                        p.append(e, f[e])
                    }), "file" == e.type ? p.append(i, n, l) : p.append(i, n.base64), h.onreadystatechange = function () {
                        if (4 == h.readyState)
                            if (200 == h.status) try {
                                var e = JSON.parse(h.responseText);
                                u(n, e)
                            } catch (e) {
                                r(n, e)
                            } else r(n, new Error("XMLHttpRequest response status is " + h.status))
                    }, h.upload.addEventListener("progress", function (e) {
                        if (0 != e.total) {
                            var t = 100 * Math.ceil(e.loaded / e.total);
                            o(n, t)
                        }
                    }, !1), h.open("POST", t), Object.keys(c).forEach(function (e) {
                        h.setRequestHeader(e, c[e])
                    }), h.send(p)
                }
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = n, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                if (e && e.__esModule) return e;
                var t = {};
                if (null != e)
                    for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
                return t.default = e, t
            }

            function a(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function o(e) {
                this.label = e.label, this.value = e.value
            }

            function r() {
                function e() {
                    (0, d.default)("body").append(h), d.default.getStyle(h[0], "transform"), h.find(".weui-mask").addClass("weui-animate-fade-in"), h.find(".weui-picker").addClass("weui-animate-slide-up")
                }

                function t() {
                    t = d.default.noop, h.find(".weui-mask").addClass("weui-animate-fade-out"), h.find(".weui-picker").addClass("weui-animate-slide-down").on("animationend webkitAnimationEnd", function () {
                        h.remove(), w = !1
                    })
                }

                function n() {
                    t()
                }

                function i(e, t) {
                    if (void 0 === c[t] && r.defaultValue && void 0 !== r.defaultValue[t]) {
                        for (var n = r.defaultValue[t], a = 0, u = e.length; a < u && n != e[a].value; ++a);
                        a < u && (c[t] = a)
                    }
                    h.find(".weui-picker__group").eq(t).scroll({
                        items: e,
                        temp: c[t],
                        onChange: function (e, n) {
                            if (e ? f[t] = new o(e) : f[t] = null, c[t] = n, l) r.onChange(f);
                            else if (e.children && e.children.length > 0) h.find(".weui-picker__group").eq(t + 1).show(), !l && i(e.children, t + 1);
                            else {
                                var a = h.find(".weui-picker__group");
                                a.forEach(function (e, n) {
                                    n > t && (0, d.default)(e).hide()
                                }), f.splice(t + 1), r.onChange(f)
                            }
                        }, onConfirm: r.onConfirm
                    })
                }
                if (w) return w;
                var a = arguments[arguments.length - 1],
                    r = d.default.extend({
                        id: "default",
                        className: "",
                        onChange: d.default.noop,
                        onConfirm: d.default.noop
                    }, a),
                    u = void 0,
                    l = !1;
                if (arguments.length > 2) {
                    var s = 0;
                    for (u = []; s < arguments.length - 1;) u.push(arguments[s++]);
                    l = !0
                } else u = arguments[0];
                y[r.id] = y[r.id] || [];
                for (var f = [], c = y[r.id], h = (0, d.default)(d.default.render(v.default, r)), m = a.depth || (l ? u.length : p.depthOf(u[0])), g = ""; m--;) g += _.default;
                return h.find(".weui-picker__bd").html(g), e(), l ? u.forEach(function (e, t) {
                    i(e, t)
                }) : i(u, 0), h.on("click", ".weui-mask", n).on("click", ".weui-picker__action", n).on("click", "#weui-picker-confirm", function () {
                    r.onConfirm(f)
                }), w = h[0], w.hide = n, w
            }

            function u(e) {
                var t = d.default.extend({
                    id: "datePicker",
                    onChange: d.default.noop,
                    onConfirm: d.default.noop,
                    start: 2e3,
                    end: 2030,
                    cron: "* * *"
                }, e);
                "number" == typeof t.start ? t.start = new Date(t.start + "-01-01") : "string" == typeof t.start && (t.start = new Date(t.start)), "number" == typeof t.end ? t.end = new Date(t.end + "-12-31") : "string" == typeof t.end && (t.end = new Date(t.end));
                var n = function (e, t, n) {
                        for (var i = 0, a = e.length; i < a; i++) {
                            var o = e[i];
                            if (o[t] == n) return o
                        }
                    },
                    i = [],
                    a = f.default.parse(t.cron, t.start, t.end),
                    o = void 0;
                do {
                    o = a.next();
                    var u = o.value.getFullYear(),
                        l = o.value.getMonth() + 1,
                        s = o.value.getDate(),
                        c = n(i, "value", u);
                    c || (c = {
                        label: u + "年",
                        value: u,
                        children: []
                    }, i.push(c));
                    var p = n(c.children, "value", l);
                    p || (p = {
                        label: l + "月",
                        value: l,
                        children: []
                    }, c.children.push(p)), p.children.push({
                        label: s + "日",
                        value: s
                    })
                } while (!o.done);
                return r(i, t)
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var l = n(2),
                d = a(l),
                s = n(25),
                f = a(s);
            n(26);
            var c = n(27),
                p = i(c),
                h = n(28),
                v = a(h),
                m = n(29),
                _ = a(m);
            o.prototype.toString = function () {
                return this.value
            }, o.prototype.valueOf = function () {
                return this.value
            };
            var w = void 0,
                y = {};
            t.default = {
                picker: r,
                datePicker: u
            }, e.exports = t.default
        },
        function (e, t) {
            "use strict";

            function n(e, t) {
                if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function")
            }

            function i(e, t) {
                var n = t[0],
                    i = t[1],
                    a = [],
                    o = void 0;
                e = e.replace(/\*/g, n + "-" + i);
                for (var u = e.split(","), l = 0, d = u.length; l < d; l++) {
                    var s = u[l];
                    s.match(r) && s.replace(r, function (e, t, r, u) {
                        u = parseInt(u) || 1, t = Math.min(Math.max(n, ~~Math.abs(t)), i), r = r ? Math.min(i, ~~Math.abs(r)) : t, o = t;
                        do a.push(o), o += u; while (o <= r)
                    })
                }
                return a
            }

            function a(e, t, n) {
                var a = e.replace(/^\s\s*|\s\s*$/g, "").split(/\s+/),
                    o = [];
                return a.forEach(function (e, t) {
                    var n = u[t];
                    o.push(i(e, n))
                }), new l(o, t, n)
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = function () {
                    function e(e, t) {
                        for (var n = 0; n < t.length; n++) {
                            var i = t[n];
                            i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i)
                        }
                    }
                    return function (t, n, i) {
                        return n && e(t.prototype, n), i && e(t, i), t
                    }
                }(),
                r = /^(\d+)(?:-(\d+))?(?:\/(\d+))?$/g,
                u = [
                    [1, 31],
                    [1, 12],
                    [0, 6]
                ],
                l = function () {
                    function e(t, i, a) {
                        n(this, e), this._dates = t[0], this._months = t[1], this._days = t[2], this._start = i, this._end = a, this._pointer = i
                    }
                    return o(e, [{
                        key: "_findNext",
                        value: function () {
                            for (var e = void 0;;) {
                                if (this._end.getTime() - this._pointer.getTime() <= 0) throw new Error("out of range, end is " + this._end + ", current is " + this._pointer);
                                var t = this._pointer.getMonth(),
                                    n = this._pointer.getDate(),
                                    i = this._pointer.getDay();
                                if (this._months.indexOf(t + 1) !== -1)
                                    if (this._dates.indexOf(n) !== -1) {
                                        if (this._days.indexOf(i) !== -1) {
                                            e = new Date(this._pointer);
                                            break
                                        }
                                        this._pointer.setDate(n + 1)
                                    } else this._pointer.setDate(n + 1);
                                else this._pointer.setMonth(t + 1), this._pointer.setDate(1)
                            }
                            return e
                        }
                    }, {
                        key: "next",
                        value: function () {
                            var e = this._findNext();
                            return this._pointer.setDate(this._pointer.getDate() + 1), {
                                value: e,
                                done: !this.hasNext()
                            }
                        }
                    }, {
                        key: "hasNext",
                        value: function () {
                            try {
                                return this._findNext(), !0
                            } catch (e) {
                                return !1
                            }
                        }
                    }]), e
                }();
            t.default = {
                parse: a
            }, e.exports = t.default
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }
            var a = n(2),
                o = i(a),
                r = function (e, t) {
                    return e.css({
                        "-webkit-transition": "all " + t + "s",
                        transition: "all " + t + "s"
                    })
                },
                u = function (e, t) {
                    return e.css({
                        "-webkit-transform": "translate3d(0, " + t + "px, 0)",
                        transform: "translate3d(0, " + t + "px, 0)"
                    })
                },
                l = function (e) {
                    for (var t = Math.floor(e.length / 2), n = 0; e[t] && e[t].disabled;)
                        if (t = ++t % e.length, n++, n > e.length) throw new Error("No selectable item.");
                    return t
                },
                d = function (e, t, n) {
                    var i = l(n);
                    return (e - i) * t
                },
                s = function (e, t) {
                    return e * t
                },
                f = function (e, t, n) {
                    return -(t * (n - e - 1))
                };
            o.default.fn.scroll = function (e) {
                function t(e) {
                    v = e, _ = +new Date
                }

                function n(e) {
                    m = e;
                    var t = m - v;
                    r(h, 0), u(h, w + t), _ = +new Date, y.push({
                        time: _,
                        y: m
                    }), y.length > 40 && y.shift()
                }

                function i(e) {
                    if (v) {
                        var t = (new Date).getTime(),
                            n = g - c.bodyHeight / 2;
                        if (m = e, t - _ > 100) x(Math.abs(m - v) > 10 ? m - v : n - m);
                        else if (Math.abs(m - v) > 10) {
                            for (var i = y.length - 1, a = i, o = i; o > 0 && _ - y[o].time < 100; o--) a = o;
                            if (a !== i) {
                                var r = y[i],
                                    u = y[a],
                                    l = r.time - u.time,
                                    d = r.y - u.y,
                                    s = d / l,
                                    f = 150 * s + (m - v);
                                x(f)
                            } else x(0)
                        } else x(n - m);
                        v = null
                    }
                }
                var a = this,
                    c = o.default.extend({
                        items: [],
                        scrollable: ".weui-picker__content",
                        offset: 3,
                        rowHeight: 34,
                        onChange: o.default.noop,
                        temp: null,
                        bodyHeight: 238
                    }, e),
                    p = c.items.map(function (e) {
                        return '<div class="weui-picker__item' + (e.disabled ? " weui-picker__item_disabled" : "") + '">' + e.label + "</div>"
                    }).join("");
                (0, o.default)(this).find(".weui-picker__content").html(p);
                var h = (0, o.default)(this).find(c.scrollable),
                    v = void 0,
                    m = void 0,
                    _ = void 0,
                    w = void 0,
                    y = [],
                    g = window.innerHeight;
                if (null !== c.temp && c.temp < c.items.length) {
                    var b = c.temp;
                    c.onChange.call(this, c.items[b], b), w = (c.offset - b) * c.rowHeight
                } else {
                    var k = l(c.items);
                    c.onChange.call(this, c.items[k], k), w = d(c.offset, c.rowHeight, c.items)
                }
                u(h, w);
                var x = function (e) {
                    w += e, w = Math.round(w / c.rowHeight) * c.rowHeight;
                    var t = s(c.offset, c.rowHeight),
                        n = f(c.offset, c.rowHeight, c.items.length);
                    w > t && (w = t), w < n && (w = n);
                    for (var i = c.offset - w / c.rowHeight; c.items[i] && c.items[i].disabled;) e > 0 ? ++i : --i;
                    w = (c.offset - i) * c.rowHeight, r(h, .3), u(h, w), c.onChange.call(a, c.items[i], i)
                };
                h = (0, o.default)(this).offAll().on("touchstart", function (e) {
                    t(e.changedTouches[0].pageY)
                }).on("touchmove", function (e) {
                    n(e.changedTouches[0].pageY), e.preventDefault()
                }).on("touchend", function (e) {
                    i(e.changedTouches[0].pageY)
                }).on("mousedown", function (e) {
                    t(e.pageY), e.stopPropagation(), e.preventDefault()
                }).on("mousemove", function (e) {
                    v && (n(e.pageY), e.stopPropagation(), e.preventDefault())
                }).on("mouseup mouseleave", function (e) {
                    i(e.pageY), e.stopPropagation(), e.preventDefault()
                }).find(c.scrollable)
            }
        },
        function (e, t) {
            "use strict";
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            t.depthOf = function e(t) {
                var n = 1;
                return t.children && t.children[0] && (n = e(t.children[0]) + 1), n
            }
        },
        function (e, t) {
            e.exports = '<div class="<%= className %>"> <div class=weui-mask></div> <div class=weui-picker> <div class=weui-picker__hd> <a href=javascript:; data-action=cancel class=weui-picker__action>取消</a> <a href=javascript:; data-action=select class=weui-picker__action id=weui-picker-confirm>确定</a> </div> <div class=weui-picker__bd></div> </div> </div> '
        },
        function (e, t) {
            e.exports = "<div class=weui-picker__group> <div class=weui-picker__mask></div> <div class=weui-picker__indicator></div> <div class=weui-picker__content></div> </div>"
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                function t() {
                    t = r.default.noop, a.addClass("weui-animate-fade-out").on("animationend webkitAnimationEnd", function () {
                        a.remove(), d = !1
                    })
                }

                function n() {
                    t()
                }
                var i = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                if (d) return d;
                i = r.default.extend({
                    className: "",
                    onDelete: r.default.noop
                }, i);
                var a = (0, r.default)(r.default.render(l.default, r.default.extend({
                    url: e
                }, i)));
                return (0, r.default)("body").append(a), a.find(".weui-gallery__img").on("click", function () {
                    n()
                }), a.find(".weui-gallery__del").on("click", function () {
                    i.onDelete.call(this, e)
                }), a.show().addClass("weui-animate-fade-in"), d = a[0], d.hide = n, d
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o),
                u = n(31),
                l = i(u),
                d = void 0;
            t.default = a, e.exports = t.default
        },
        function (e, t) {
            e.exports = '<div class="weui-gallery <%= className %>"><img class="weui-gallery__img" src="<%= url %>"> <div class=weui-gallery__opr> <a href=javascript: class=weui-gallery__del> <i class="weui-icon-delete weui-icon_gallery-delete"></i> </a> </div> </div> '
        },
        function (e, t, n) {
            "use strict";

            function i(e) {
                return e && e.__esModule ? e : {
                    default: e
                }
            }

            function a(e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                    n = (0, r.default)(e);
                if (t = r.default.extend({
                        step: void 0,
                        defaultValue: 0,
                        onChange: r.default.noop
                    }, t), void 0 !== t.step && (t.step = parseFloat(t.step), !t.step || t.step < 0)) throw new Error("Slider step must be a positive number.");
                if (void 0 !== t.defaultValue && t.defaultValue < 0 || t.defaultValue > 100) throw new Error("Slider defaultValue must be >= 0 and <= 100.");
                return n.forEach(function (e) {
                    function n() {
                        var e = r.default.getStyle(l[0], "left");
                        return e = /%/.test(e) ? d * parseFloat(e) / 100 : parseFloat(e)
                    }

                    function i(n) {
                        var i = void 0,
                            a = void 0;
                        t.step && (n = Math.round(n / p) * p), i = f + n, i = i < 0 ? 0 : i > d ? d : i, a = 100 * i / d, u.css({
                            width: a + "%"
                        }), l.css({
                            left: a + "%"
                        }), t.onChange.call(e, a)
                    }
                    var a = (0, r.default)(e),
                        o = a.find(".weui-slider__inner"),
                        u = a.find(".weui-slider__track"),
                        l = a.find(".weui-slider__handler"),
                        d = parseInt(r.default.getStyle(o[0], "width")),
                        s = o[0].offsetLeft,
                        f = 0,
                        c = 0,
                        p = void 0;
                    t.step && (p = d * t.step / 100), t.defaultValue && i(d * t.defaultValue / 100), a.on("click", function (e) {
                        e.preventDefault(), f = n(), i(e.pageX - s - f)
                    }), l.on("touchstart", function (e) {
                        f = n(), c = e.changedTouches[0].clientX
                    }).on("touchmove", function (e) {
                        e.preventDefault(), i(e.changedTouches[0].clientX - c)
                    })
                }), this
            }
            Object.defineProperty(t, "__esModule", {
                value: !0
            });
            var o = n(2),
                r = i(o);
            t.default = a, e.exports = t.default
        }
    ])
});