
$(document).ready(function() {
    var c = {setting: {startline: 20,scrollto: 0,scrollduration: 300,fadeduration: [500, 100]},controlHTML: '<a id="totop" href="#" class="totop"><i class="icon-circle-arrow-up"></i></a>',controlattrs: {offsetx: 0,offsety: 30},anchorkeyword: "#top",state: {isvisible: !1,shouldvisible: !1},scrollup: function() {
            this.cssfixedsupport || this.$control.css({opacity: 0});
            var a = isNaN(this.setting.scrollto) ? this.setting.scrollto : parseInt(this.setting.scrollto), a = "string" == typeof a && 1 == jQuery("#" + a).length ? jQuery("#" + 
            a).offset().top : 0;
            this.$body.animate({scrollTop: a}, this.setting.scrollduration)
        },keepfixed: function() {
            var a = jQuery(window), b = (a.scrollLeft() + a.width()) / 2, a = a.scrollTop() + a.height() - this.$control.height() - this.controlattrs.offsety;
            this.$control.css({left: b + "px",top: a + "px"})
        },togglecontrol: function() {
            var a = jQuery(window).scrollTop();
            this.cssfixedsupport || this.keepfixed();
            this.state.shouldvisible = a >= this.setting.startline ? !0 : !1;
            this.state.shouldvisible && !this.state.isvisible ? (this.$control.stop().animate({opacity: 1}, 
            this.setting.fadeduration[0]), this.state.isvisible = !0) : !1 == this.state.shouldvisible && this.state.isvisible && (this.$control.stop().animate({opacity: 0}, this.setting.fadeduration[1]), this.state.isvisible = !1)
        },init: function() {
            jQuery(document).ready(function(a) {
                var b = c, d = document.all;
                b.cssfixedsupport = !d || d && "CSS1Compat" == document.compatMode && window.XMLHttpRequest;
                b.$body = window.opera ? "CSS1Compat" == document.compatMode ? a("html") : a("body") : a("html,body");
                b.$control = a('<div id="topcontrol">' + b.controlHTML + 
                "</div>").css({position: b.cssfixedsupport ? "fixed" : "absolute",bottom: b.controlattrs.offsety,left: "50%",marginLeft: "500px",opacity: 0,cursor: "pointer"}).attr({title: "杩斿洖椤堕儴"}).click(function() {
                    b.scrollup();
                    return !1
                }).appendTo("body");
                document.all && !window.XMLHttpRequest && "" != b.$control.text() && b.$control.css({width: b.$control.width()});
                b.togglecontrol();
                a('a[href="' + b.anchorkeyword + '"]').click(function() {
                    b.scrollup();
                    return !1
                });
                a(window).bind("scroll resize", function() {
                    b.togglecontrol()
                })
            })
        }};
    c.init()
});
