(() => {
	"use strict";
	var r,
		e = {
			697: () => {
				const r = window.wp.blocks,
					e = JSON.parse('{"UU":"omee-home-block/feature"}');
				(0, r.registerBlockType)(e.UU, {});
			},
		},
		o = {};
	function t(r) {
		var n = o[r];
		if (void 0 !== n) return n.exports;
		var s = (o[r] = { exports: {} });
		return (e[r](s, s.exports, t), s.exports);
	}
	((t.m = e),
		(r = []),
		(t.O = (e, o, n, s) => {
			if (!o) {
				var a = 1 / 0;
				for (p = 0; p < r.length; p++) {
					((o = r[p][0]), (n = r[p][1]), (s = r[p][2]));
					for (var l = !0, c = 0; c < o.length; c++)
						(!1 & s || a >= s) && Object.keys(t.O).every((r) => t.O[r](o[c]))
							? o.splice(c--, 1)
							: ((l = !1), s < a && (a = s));
					if (l) {
						r.splice(p--, 1);
						var i = n();
						void 0 !== i && (e = i);
					}
				}
				return e;
			}
			s = s || 0;
			for (var p = r.length; p > 0 && r[p - 1][2] > s; p--) r[p] = r[p - 1];
			r[p] = [o, n, s];
		}),
		(t.o = (r, e) => Object.prototype.hasOwnProperty.call(r, e)),
		(() => {
			var r = { 438: 0, 50: 0 };
			t.O.j = (e) => 0 === r[e];
			var e = (e, o) => {
					var n,
						s,
						a = o[0],
						l = o[1],
						c = o[2],
						i = 0;
					if (a.some((e) => 0 !== r[e])) {
						for (n in l) t.o(l, n) && (t.m[n] = l[n]);
						if (c) var p = c(t);
					}
					for (e && e(o); i < a.length; i++)
						((s = a[i]), t.o(r, s) && r[s] && r[s][0](), (r[s] = 0));
					return t.O(p);
				},
				o = (self.webpackChunkcoe_blocks = self.webpackChunkcoe_blocks || []);
			(o.forEach(e.bind(null, 0)), (o.push = e.bind(null, o.push.bind(o))));
		})());
	var n = t.O(void 0, [50], () => t(697));
	n = t.O(n);
})();
