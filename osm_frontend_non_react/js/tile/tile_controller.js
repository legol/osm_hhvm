/**
 * Created by ChenJie3 on 2016/2/18.
 */

if (!TileController) {
    var TileController = function () {
        this.data = new Object();

        var log = log4javascript.getDefaultLogger();
        log.info("TileController loaded...");
    };

    TileController.prototype = {
        init: function(){
            var log = log4javascript.getDefaultLogger();
            log.info("TileController initialized.");

            $("#map_canvas").drag({
                willDrag: $.proxy(this.willDrag, this),
                didDrag: $.proxy(this.didDrag, this),
                dragEnd: $.proxy(this.dragEnd, this)
            });
        },

        willDrag: function(ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("will drag.");
        },

        didDrag: function(delta, ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("didDrag.");
        },

        dragEnd: function(ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("drag end.");
        },
    };

    window.tileController = new TileController();
}
