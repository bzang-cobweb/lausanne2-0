(function( $ ) {

    $.fn.select = function() {

        this.each(function() {
            var $this = this;

            if ($( this ).attr('multiple') !== undefined) {
                var multiple = $( this ).attr('multiple');
                if(multiple === 'multiple'){
                    var list = '<ul class="select-multiple form-control" id="' + $( this ).attr('id') + '">';
                    $( this ).find('option').each(function () {
                        list += addOption({
                            value: $( this ).val(),
                            name: $( $this ).attr('name'),
                            label: $( this ).text(),
                            selected: $( this ).attr('selected') !== undefined && $( this ).attr('selected')
                        })
                    });
                    list += '</ul>';
                    $( this ).replaceWith(list);
                }
            }

            function addOption(option){
                var entry = '<li>';
                if(option.selected === 'selected'){
                    entry += '<input type="checkbox" name="' + option.name + '" value="' + option.value + '" checked="checked">';
                } else {
                    entry += '<input type="checkbox" name="' + option.name + '" value="' + option.value + '">';
                }
                entry += '<span>' + option.label + '</span>';
                entry += '</li>';
                return entry;
            }
        });

        return this;
    };

}( jQuery ));
