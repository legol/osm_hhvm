/**
 * Created by ChenJie3 on 2016/2/18.
 */

if (!Tile) {
    var Tile = function () {
        this.data = new Object();

        this.data.position = new Object();

        window.console.log("Tile loaded...");
    };

    Tile.prototype = {
        init: function (tile_x, tile_y, tile_level) {
            window.console.log("Tile initialized.");

            this.data.tile_x = tile_x;
            this.data.tile_y = tile_y;
            this.data.tile_level = tile_level;

            this.data.div = document.createElement('div');
            this.data.div.className = 'tile';

            this.data.debug_div = document.createElement('div');
            this.data.debug_div.className = 'tile_debug';
            this.data.div.appendChild(this.data.debug_div);

            this.data.img = document.createElement('img');
            this.data.img.className = 'tile_img';
            this.data.div.appendChild(this.data.img);
        },

        getTileId: function(l, t) {
          return ''.concat(l.toString(), ':', t.toString());
        },

        addTo : function(parentId, _left, _top){
            document.getElementById(parentId).appendChild(this.data.div);

            var newPos = {
                x:_left,
                y:_top,
            }
            $(this.data.div).position(newPos);

            $(this.data.debug_div).text(this.getTileId(_left, _top) + '-->' + `${this.data.tile_x},${this.data.tile_y},${this.data.tile_level}`);
            $(this.data.img).attr(
              'src',
              `http://192.168.1.111:10002/ci/index.php?c=TileGenerator&m=getTile&at=${this.data.tile_x},${this.data.tile_y},${this.data.tile_level}`,
            );
        },
    };
}
