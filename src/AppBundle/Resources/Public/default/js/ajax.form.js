jQuery.fn.ajaxSubmitForm = function() {
    return this.each(function() {
        $( document ).on('submit', 'form.ajax-form', function(event){
            var url = $( this ).attr('action');
            if(url !== '') {
                var $element = this;
                var data = $( this ).serialize();
                console.log(data);
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if(response.type){
                            if(response.type === 'error'){

                            } else if(response.type === 'success') {
                                if(response.refresh && response.refresh === 1){
                                    if(response.element && response.element !== ''){
                                        //$('#' + response.element ).refresh();
                                    }
                                }
                            }
                        }
                        console.log(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            }

            event.preventDefault();
        });
    });
};