(function ( $ ) {

    $.fn.greenify = function( options ) {

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            color: "#556b2f",
            backgroundColor: "white"
        }, options );

        // Greenify the collection based on the settings variable.
        return this.css({
            color: settings.color,
            backgroundColor: settings.backgroundColor
        });

    };

    $.fn.drag = function( options ) {

        var Tracker = function(){
            this.data = new Object();

            this.data.target = null; // which DOM element
            this.data.oldPosition = {x:0, y:0}; // the mouse screen position on mouse down
            this.data.mousedown = false; // whether mouse is down.
            this.data.isDragging = false;
            this.data.oldElementPosition = {x:0, y:0}; // relative to parent
        };

        Tracker.prototype = {
            init: function(_target, options){
                this.data.target = _target;

                $(this.data.target).on("mousedown", $.proxy(this.onMouseDown, this));
                $(this.data.target).on("mouseup", $.proxy(this.onMouseUp, this));
                $(this.data.target).on("mousemove", $.proxy(this.onMouseMove, this));

                this.data.fnWillDrag = options.willDrag;
                this.data.fnDidDrag = options.didDrag;
                this.data.fnDragEnd = options.dragEnd;
            },

            onMouseDown: function (event) {
                this.data.mousedown = true;
                this.data.isDragging = false;
                this.data.oldMousePosition = {
                    x:event.screenX,
                    y:event.screenY
                };

                this.data.oldElementPosition = {
                    x:$(this.data.target).offsetToParent().left,
                    y:$(this.data.target).offsetToParent().top
                };
            },

            onMouseUp:function(event){
                this.data.mousedown = false;
                this.data.oldPosition = {x:0, y:0};

                if (this.data.isDragging){
                    if (this.data.dragEnd != null){
                        this.data.dragEnd();
                    }
                }
            },

            onMouseMove:function(event){
                if (!this.data.mousedown){
                    return;
                }

                if (!this.data.isDragging){
                    if (this.data.fnWillDrag){
                        this.data.fnWillDrag();
                    }
                    this.data.isDragging = true;
                }

                if (this.data.isDragging){

                    var delta = {
                        x: event.screenX - this.data.oldMousePosition.x,
                        y: event.screenY - this.data.oldMousePosition.y
                    }

                    var newPos = {
                        x:delta.x + this.data.oldElementPosition.x,
                        y:delta.y + this.data.oldElementPosition.y
                    }
                    $(this.data.target).position({
                        my: "left top",
                        at: "left+"+(newPos.x) + " top+"+(newPos.y),
                        of: "#" + $(this.data.target).parent().get(0).id,
                        collision: "none"
                    });

                    if (this.data.fnDidDrag){
                        this.data.fnDidDrag();
                    }
                }
            }
        };

        var tracker = new Tracker();
        tracker.init(this, options);

        return this;
    };


}( jQuery ));
