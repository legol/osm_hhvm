(function ( $ ) {
    $.fn.position = function( options ) {
      return this.css('transform', `translate3d(${options.x}px, ${options.y}px, 0px)`);
    };
    $.fn.get_position = function( options ) {
      var transform = this.css('transform');

      if (transform == 'none') {
        return {x:0, y:0};
      } else {
        var matched = transform.match(/matrix\(\d+, \d+, \d+, \d+, (\d+), (\d+)\)/);
        if (matched[0] != transform) {
          return {x:0, y:0};
        } else {
          return {x:parseInt(matched[1]), y:parseInt(matched[2])};
        }
      }
    };
}( jQuery ));
