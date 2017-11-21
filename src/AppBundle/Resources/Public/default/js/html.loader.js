jQuery.fn.htmlLoader = function() {
    return this.each(function() {
        if ($( this ).attr('id') !== undefined && $( this ).attr('data-url') !== undefined) {
            var url = $( this ).attr('data-url');
            var id = $( this ).attr('id');
            if(id !== '' && url !== '') {
                var $element = this;
                $.ajax({
                    method: 'GET',
                    url: url,
                    data: {},
                    dataType: 'html',
                    success: function (response) {
                        $($element).html(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            }
        }
    });
};