/**
 * Created by ChenJie3 on 2016/2/18.
 */

if (!Tile) {
    var Tile = function () {
        this.data = new Object();

        this.data.position = new Object();

        var log = log4javascript.getDefaultLogger();
        log.info("Tile loaded...");


    };

    Tile.prototype = {
        init: function (_left, _top) {
            var log = log4javascript.getDefaultLogger();
            log.info("Tile initialized.");

            this.data.position.left = _left;
            this.data.position.top = _top;

            this.data.div = document.createElement('div');
            this.data.div.innerText = "";
            this.data.div.id = this.getId();
            this.data.div.className = 'tile';

            this.data.debug_div = document.createElement('div');
            this.data.debug_div.innerText = this.getId();
            this.data.debug_div.id = this.getId() + "_debug";
            this.data.debug_div.className = 'tile_debug';

            this.data.div.appendChild(this.data.debug_div);
        },

        getId: function(){
            return 'tile_' + this.data.position.left + '_' + this.data.position.top;
        },

        addTo : function(parentId){
            document.getElementById(parentId).appendChild(this.data.div);

            var jDiv = $(this.data.div);
            jDiv.offsetTop = 0;
            jDiv.offsetLeft = 0;

            jDiv.position({
                my: "left top",
                at: "left+" + this.data.position.left + " top+" + this.data.position.top,
                of: "#" + parentId,
                collision: "none"
            });
        },

        removeFromParent: function(){

        },

        setDebugText: function(debug_text){
            this.data.debug_div.innerText = debug_text;
        }
    };
}

