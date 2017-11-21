jQuery.fn.jsonDelete = function() {
    return this.each(function() {
        $( this ).on('click', function(event) {
            event.preventDefault();
            if ($( this ).attr('href') !== undefined) {
                var url = $( this ).attr('href');
                if (url !== '') {
                    $.ajax({
                        method: 'GET',
                        url: url,
                        data: {},
                        dataType: 'json',
                        success: function (data) {
                            $('#' + data.element).remove();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(textStatus, errorThrown);
                        }
                    });
                }
            }
        });
    });
};