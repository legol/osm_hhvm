(function ( $ ) {

    $.fn.offsetToParent = function( options ) {

        elem = this[ 0 ];
        if ( !elem ) {
            return;
        }

        var leftOffset = $(elem).offset().left - $(elem).parent().offset().left;
        var topOffset = $(elem).offset().top - $(elem).parent().offset().top;


        return {left:leftOffset, top:topOffset};
    };


}( jQuery ));
