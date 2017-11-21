(function( $ ) {

    $.fn.validate = function() {

        this.each(function() {
            $( this ).find('.invalid-feedback').each(function() {
                if($( this ).has('ul').length > 0){
                    $( this ).closest('.form-group').find('.form-control').addClass('is-invalid');
                }
            });
        });

        return this;
    };

}( jQuery ));
