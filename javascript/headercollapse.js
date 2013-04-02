YUI().use("node", function(Y) {
    var btn_Click = function(e) {
        console.log('test');
        Y.one('.btn-navbar').addClass('active');
        togglemenu = Y.one('.nav-collapse');
        if (togglemenu.hasClass('active')) {
            togglemenu.setStyle('height','0px');
            togglemenu.removeClass('active');
        } else {
            togglemenu.addClass('active');
            togglemenu.setStyle('height','auto');
        }
    };
    Y.on("click", btn_Click, ".btn-navbar");
});