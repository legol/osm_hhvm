/**
 * Created by ChenJie3 on 2016/2/18.
 */

if (!TileController) {
    var TileController = function () {
        this.data = new Object();

        this.data.tileWidth = 256;
        this.data.tileHeight = 256;

        this.data.tiles = new Map();

        window.console.log("TileController loaded...");
    };

    TileController.prototype = {
        init: function(){
            window.console.log("TileController initialized.");

            $("#map_canvas").drag({
                willDrag: $.proxy(this.willDrag, this),
                didDrag: $.proxy(this.didDrag, this),
                dragEnd: $.proxy(this.dragEnd, this)
            });

            this.tile();
        },

        getViewport: function() {
          var canvas_pos = $("#map_canvas").get_position();
          return {
            left:-canvas_pos.x,
            top:-canvas_pos.y,
            width:$("#map_container").innerWidth(),
            height:$("#map_container").innerHeight(),
          };
        },

        willDrag: function(ui) {
            window.console.log("will drag.");
        },

        didDrag: function(delta, ui) {
            window.console.log("didDrag.");

            this.tile();
        },

        dragEnd: function(ui) {
            window.console.log("drag end.");
        },

        getTileId: function(l, t) {
          return ''.concat(l.toString(), ':', t.toString());
        },

        tile: function() {
            var tileWidth = this.data.tileWidth;
            var tileHeight = this.data.tileHeight;

            // add missing tiles and remove redundant ones.
            var viewport = this.getViewport();
            window.console.log('viewport:' + JSON.stringify(viewport));

            var t = viewport.top;
            var b = viewport.top + viewport.height;
            var l = viewport.left;
            var r = viewport.left + viewport.width;

            l = (l <= 0) ? Math.floor(l / tileWidth) * tileWidth : Math.ceil(l / tileWidth) * tileWidth;
            t = (t <= 0) ? Math.floor(t / tileHeight) * tileHeight : Math.ceil(t / tileHeight) * tileHeight;

            window.console.log("l, t, r, b = " + l + "," + t + "," + r + "," + b);

            while(t <= b){

                var l = viewport.left;
                l = (l <= 0) ? Math.floor(l / tileWidth) * tileWidth : Math.ceil(l / tileWidth) * tileWidth;

                window.console.log("l, t, r, b = " + l + "," + t + "," + r + "," + b);

                while(l <= r){
                    window.console.log("checking: " + this.getTileId(l, t));

                    var tile = this.data.tiles.get(this.getTileId(l, t));
                    if (tile === undefined){
                      window.console.log("add tile at: " + this.getTileId(l, t));

                      var newTile = new Tile();
                      newTile.init();
                      newTile.addTo("map_canvas", l, t);

                      this.data.tiles.set(this.getTileId(l, t), newTile);
                    }

                    l += tileWidth;
                }

                t += tileHeight;
            }
        },
    };

    window.tileController = new TileController();
}
