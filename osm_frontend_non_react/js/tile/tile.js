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
        init: function () {
            window.console.log("Tile initialized.");

            this.data.div = document.createElement('div');
            this.data.div.innerText = "";
            // this.data.div.id = this.getId();
            this.data.div.className = 'tile';

            this.data.debug_div = document.createElement('div');
            // this.data.debug_div.innerText = this.getId();
            // this.data.debug_div.id = this.getId() + "_debug";
            this.data.debug_div.className = 'tile_debug';

            this.data.div.appendChild(this.data.debug_div);
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

            $(this.data.debug_div).text(this.getTileId(_left, _top));
        },
    };
}
