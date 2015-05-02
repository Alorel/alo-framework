(function () {
	var e = !1;
	if ("undefined" === typeof kintInitialized) {
		kintInitialized = 1;
		var f = [], g = -1, h = function (b, a) {
			"undefined" === typeof a && (a = "kint-show");
			return RegExp("(\\s|^)" + a + "(\\s|$)").test(b.className)
		}, k = function (b, a) {
			"undefined" === typeof a && (a = "kint-show");
			j(b, a).className += " " + a
		}, j = function (b, a) {
			"undefined" === typeof a && (a = "kint-show");
			b.className = b.className.replace(RegExp("(\\s|^)" + a + "(\\s|$)"), " ");
			return b
		}, l = function (b) {
			do {
				b = b.nextElementSibling;
			} while ("dd" !== b.nodeName.toLowerCase());
			return b
		}, m = function (b, a) {
			var c =
				l(b);
			"undefined" === typeof a && (a = h(b));
			a ? j(b) : k(b);
			1 === c.childNodes.length && (c = c.childNodes[0].childNodes[0], h(c, "kint-parent") && m(c, a))
		}, n = function (b, a) {
			var c = l(b).getElementsByClassName("kint-parent"), d = c.length;
			for ("undefined" === typeof a && (a = h(b)); d--;)m(c[d], a);
			m(b, a)
		}, p = function (b) {
			var a = b, c = 0;
			b.parentNode.getElementsByClassName("kint-active-tab")[0].className = "";
			for (b.className = "kint-active-tab"; a = a.previousSibling;)1 === a.nodeType && c++;
			b = b.parentNode.nextSibling.childNodes;
			for (var d = 0; d < b.length; d++)d ===
			                                  c ? (b[d].style.display = "block", 1 === b[d].childNodes.length &&
			(a = b[d].childNodes[0].childNodes[0], h(a, "kint-parent") && m(a, e))) : b[d].style.display = "none"
		}, q = function (b) {
			for (; !(b = b.parentNode, !b || h(b, "kint")););
			return !!b
		}, r = function () {
			f = [];
			Array.prototype.slice.call(document.querySelectorAll(".kint nav, .kint-tabs>li:not(.kint-active-tab)"), 0).forEach(function (b) {
				(0 !== b.offsetWidth || 0 !== b.offsetHeight) && f.push(b)
			})
		}, s = function (b) {
			var a = document.querySelector(".kint-focused");
			a && j(a, "kint-focused");
			if (-1 !==
				b) {
				a = f[b];
				k(a, "kint-focused");
				var c = function (a) {
					return a.offsetTop + (a.offsetParent ? c(a.offsetParent) : 0)
				};
				window.scrollTo(0, c(a) - window.innerHeight / 2)
			}
			g = b
		}, t = function (b, a) {
			b ? 0 > --a && (a = f.length - 1) : ++a >= f.length && (a = 0);
			s(a);
			return e
		};
		window.addEventListener("click", function (b) {
			var a = b.target, c = a.nodeName.toLowerCase();
			if (q(a)) {
				if ("dfn" === c) {
					var d = a, u = window.getSelection(), v = document.createRange();
					v.selectNodeContents(d);
					u.removeAllRanges();
					u.addRange(v);
					a = a.parentNode
				} else {
					"var" === c && (a = a.parentNode,
						c = a.nodeName.toLowerCase());
				}
				if ("li" === c && "kint-tabs" === a.parentNode.className) {
					return "kint-active-tab" !== a.className &&
					(p(a), -1 !== g && r()), e;
				}
				if ("nav" === c) {
					return setTimeout(function () {
						0 < parseInt(a.a, 10) ? a.a-- : (n(a.parentNode), -1 !== g && r())
					}, 300), b.stopPropagation(), e;
				}
				if (h(a, "kint-parent")) {
					return m(a), -1 !== g && r(), e;
				}
				if (h(a, "kint-ide-link")) {
					return b.preventDefault(), b =
						new XMLHttpRequest, b.open("GET", a.href), b.send(null), e
				}
			}
		}, e);
		window.addEventListener("dblclick", function (b) {
			var a = b.target;
			if (q(a) && "nav" ===
				a.nodeName.toLowerCase()) {
				a.a = 2;
				for (var c = document.getElementsByClassName("kint-parent"), d = c.length, a = h(a.parentNode);
				     d--;)m(c[d], a);
				-1 !== g && r();
				b.stopPropagation()
			}
		}, e);
		window.onkeydown = function (b) {
			if (!(b.target !== document.body || b.altKey)) {
				var a = b.keyCode, c = b.shiftKey;
				b = g;
				if (68 === a) {
					if (-1 === b) {
						return r(), t(e, b);
					}
					s(-1);
					return e
				}
				if (-1 !== b) {
					if (9 === a) {
						return t(c, b);
					}
					if (38 === a) {
						return t(!0, b);
					}
					if (40 === a) {
						return t(e, b);
					}
					if (-1 !== b) {
						c = f[b];
						if ("li" === c.nodeName.toLowerCase()) {
							if (32 === a || 13 === a) {
								return p(c), r(), t(!0,
									b);
							}
							if (39 === a) {
								return t(e, b);
							}
							if (37 === a) {
								return t(!0, b)
							}
						}
						c = c.parentNode;
						if (32 === a || 13 === a) {
							return m(c), r(), e;
						}
						if (39 === a || 37 === a) {
							var d = h(c), a = 37 === a;
							if (d) {
								n(c, a);
							} else {
								if (a) {
									do {
										c = c.parentNode;
									} while (c && "dd" !== c.nodeName.toLowerCase());
									if (c) {
										c = c.previousElementSibling;
										b = -1;
										for (d = c.querySelector("nav"); d !== f[++b];);
										s(b)
									} else {
										c = f[b].parentNode
									}
								}
								m(c, a)
							}
							r();
							return e
						}
					}
				}
			}
		}
	}
	;
})()
