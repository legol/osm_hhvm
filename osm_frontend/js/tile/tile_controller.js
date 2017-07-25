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

            // jquery.ui won't support scaled div, so I implemented my own drag.
            //$("#map_canvas").draggable({
            //    start: $.proxy(this.onDragStart, this),
            //    drag: $.proxy(this.onDrag, this),
            //    stop: $.proxy(this.onDragEnd, this)
            //});

            $("#map_canvas").drag({
                willDrag: $.proxy(this.willDrag, this),
                didDrag: $.proxy(this.didDrag, this),
                dragEnd: $.proxy(this.dragEnd, this)
            });

            $("#map_canvas").on("mousewheel", $.proxy(this.onMouseWheel, this));

            this.data.viewport = {left:-$("#map_canvas").offsetToParent().left,
                top:-$("#map_canvas").offsetToParent().top,
                width:$("#map_container").innerWidth(),
                height:$("#map_container").innerHeight()};

            this.data.tileArray = new Array();
            this.data.tileWidth = 256;
            this.data.tileHeight = 256;

            this.data.scale = 1.0;
            this.data.scaling = false;

            this.observeParentSizeChange();
            this.viewportChanged();
        },

        onMouseWheel: function(event){

            var scale = 1.0;
            if (event.deltaY >= 0){
                scale = 2.0;
            }
            else if (event.deltaY <= 0){
                scale = 0.5;
            }

            var oldScale = this.data.scale;
            this.data.scale *= scale;

            // assume map_container is the document.
            var mouseX = event.pageX - $("#map_container").offset().left;
            var mouseY = event.pageY - $("#map_container").offset().top;

            // calculate new offset
            // vec_offset_canvas_to_document + vec_offset_relative_to_canvas = vec_mouse_relative_to_document
            // so, vec_mouse_relative_to_document - vec_offset_relative_to_canvas = new vec_offset_canvas_to_document
            var oldOffset = {
                left:$("#map_canvas").offsetToParent().left, // parent is map_container
                top:$("#map_canvas").offsetToParent().top
            };

            var posRelativeToCanvas = {
                left: mouseX - oldOffset.left,
                top: mouseY - oldOffset.top
            };

            var newViewportOffset = {
                left:mouseX - posRelativeToCanvas.left * scale,
                top: mouseY - posRelativeToCanvas.top * scale
            };

            this.data.scaling = true;
                $("#map_canvas").css('-webkit-transform', 'scale(' + this.data.scale + ')');
                this.moveCanvas(newViewportOffset.left, newViewportOffset.top);
                this.viewportChanged();

                //this.data.tileWidth = 256 * this.data.scale;
                //this.data.tileHeight = 256 * this.data.scale;
            this.data.scaling = false;

            event.preventDefault();

        },

        willDrag: function(ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("will drag.");

            this.onDragCanvas(event, ui);
        },

        didDrag: function(delta, ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("didDrag.");

            this.onDragCanvas();
        },

        dragEnd: function(ui) {
            var log = log4javascript.getDefaultLogger();
            log.info("drag end.");

            this.onDragCanvas();
        },

        onDragCanvas: function(){
            this.viewportChanged();
        },

        moveCanvas: function(newL, newT){
            $("#map_canvas").position({
                my: "left top",
                at: "left+"+newL + " top+"+newT,
                of: "#map_container",
                collision: "none"
            });

            this.viewportChanged();
        },

        observeParentSizeChange: function() {
            $(window).on("resize", $.proxy(this.viewportChanged, this));
            //$("#map_container").on("resize", $.proxy(this.viewportChanged, this));
        },

        viewportChanged: function() {
            var log = log4javascript.getDefaultLogger();
            log.info("TileController.viewportChanged() called.");

            this.data.viewport = {left:-$("#map_canvas").offsetToParent().left / this.data.scale,
                top:-$("#map_canvas").offsetToParent().top / this.data.scale,
                width:$("#map_container").innerWidth() / this.data.scale,
                height:$("#map_container").innerHeight() / this.data.scale};

            log.info("viewport=" + JSON.stringify(this.data.viewport));

            if (!this.data.scaling){
                this.tile();
            }
        },

        tile: function() {
            var log = log4javascript.getDefaultLogger();

            var tileWidth = this.data.tileWidth;
            var tileHeight = this.data.tileHeight;

            // add missing tiles and remove redundant ones.
            var t = this.data.viewport.top;
            var b = this.data.viewport.top + this.data.viewport.height;
            var l = this.data.viewport.left;
            var r = this.data.viewport.left + this.data.viewport.width;

            l = (l <= 0) ? Math.floor(l / tileWidth) * tileWidth : Math.ceil(l / tileWidth) * tileWidth;
            t = (t <= 0) ? Math.floor(t / tileHeight) * tileHeight : Math.ceil(t / tileHeight) * tileHeight;

            log.info("l, t, r, b = " + l + "," + t + "," + r + "," + b);

            while(t <= b){

                var l = this.data.viewport.left;
                l = (l <= 0) ? Math.floor(l / tileWidth) * tileWidth : Math.ceil(l / tileWidth) * tileWidth;

                log.info("l, t, r, b = " + l + "," + t + "," + r + "," + b);

                while(l <= r){
                    log.info("checking: " + l + ", " + t);

                    var tileExist = false;

                    for (var tileIndex = 0; tileIndex < this.data.tileArray.length; tileIndex++){
                        if (this.data.tileArray[tileIndex].data.position.left == l &&
                            this.data.tileArray[tileIndex].data.position.top == t){
                            tileExist = true;
                            break;
                        }
                    }

                    if (!tileExist){
                        var log = log4javascript.getDefaultLogger();
                        //log.info("add tile at: " + l + ", " + t);

                        var newTile = new Tile();
                        newTile.init(l, t);
                        newTile.addTo("map_canvas");

                        this.data.tileArray.push(newTile);
                    }
                    else{
                        //log.info("tile exists at: " + l + ", " + t);
                    }

                    l += tileWidth;
                }

                t += tileHeight;
            }
        },
    };

    window.tileController = new TileController();
}
