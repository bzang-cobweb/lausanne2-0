jQuery.fn.htmlReplacer = function() {
    return this.each(function() {
        if ($( this ).attr('id') !== undefined && $( this ).attr('data-url') !== undefined) {
            var id = $( this ).attr('id');
            var url = $( this ).attr('data-url');
            if(id !== '' && url !== '') {
                $.ajax({
                    method: 'GET',
                    url: url,
                    data: {},
                    dataType: 'html',
                    success: function (response) {
                        $('#' + id).replaceWith(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            }
        }
    });
};