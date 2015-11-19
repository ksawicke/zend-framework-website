require([
  'jquery',
  'jqueryUI',
  'bootstrap',
  'dropzone',
  'ckeditor-jquery',
  'parsleyremote',
  'parsley'
], function ($) {
    $(function () {
        $.fn.extend({
            clickToggle: function (e, t) {
                return this.each(function () {
                    var o = !1;
                    $(this).bind('click', function () {
                        return o ? (o = !1, t.apply(this, arguments))  : (o = !0, e.apply(this, arguments))
                    })
                })
            }
        });

        $.base_url = requirejs.s.contexts._.config.baseUrl.slice(0,-1);

        function t() {
            for (var t = new Array(20), e = 0; e < t.length; e++) t[e] = [
                5 + r(),
                10 + r(),
                15 + r(),
                20 + r(),
                30 + r(),
                35 + r(),
                40 + r(),
                45 + r(),
                50 + r()
            ];
            return t
        }
        function r() {
            return Math.floor(80 * Math.random())
        }
    });
});
