jQuery.fn.dataTable = function( options ) {
    //var settings = $.extend( {}, defaults, options );
    return this.each(function() {
        var id = $( this ).attr('id');
        console.log('id: ' + id);
        $( document ).on('click', '#' + id + '.dataTable table tr th.sortable', function(){
            var $wrapper = $( this ).closest('.dataTable');
            var settings = init($wrapper);
            pushData(settings, 'order', $( this ).attr('data-order'));
            pushData(settings, 'dir', $( this ).attr('data-dir') == 'asc' ? 'desc' : 'asc');
            ajaxLoad(settings, $wrapper);
            event.preventDefault();
        });

        $( document ).on('submit', '#' + id + '.dataTable form.dt-search', function(event){
            var $wrapper = $( this ).closest('.dataTable');
            var settings = init($wrapper);
            pushData(settings, 'search', $( this ).find('input[name=search]').val());
            ajaxLoad(settings, $wrapper);
            event.preventDefault();
        });

        $( document ).on('click', '#' + id + '.dataTable ul.pagination li.page-item a.page-link', function(){
            var $wrapper = $( this ).closest('.dataTable');
            if ($( this ).attr('data-page') !== undefined) {
                var settings = init($wrapper);
                pushData(settings, 'page', $( this ).attr('data-page'));
                ajaxLoad(settings, $wrapper);
            }
            event.preventDefault();
        });

        $(document).on('click', '#' + id + '.dataTable input[name=dt_check_all]', function () {
            var $wrapper = $( this ).closest('.dataTable');
            $( $wrapper ).find('input.dt_check').prop('checked', $( this ).prop('checked'));
            checkBoxListener($wrapper);
        });

        $(document).on('click', '#' + id + '.dataTable table tr > td > input.dt_check', function () {
            checkBoxListener($( this ).closest('.dataTable'));
        });

        $(document).on('click', '#' + id + '.dataTable .dropdown.action .dropdown-menu > .dropdown-item', function () {
            if($( this ).attr('data-action') !== undefined){
                var $wrapper = $( this ).closest('.dataTable');
                var settings = init($wrapper);
                var ids = [];
                $( $wrapper ).find('table tr > td > input.dt_check:checked').each(function(){
                    ids.push($( this ).val());
                });
                pushData(settings, 'table_group_action', $( this ).attr('data-action'));
                pushData(settings, 'table_selected_ids', JSON.stringify(ids));
                ajaxLoad(settings, $wrapper);
            }
            event.preventDefault();
        });

        $(document).on('click', '#' + id + '.dataTable .dropdown.select .dropdown-menu > .dropdown-item', function () {
            if($( this ).attr('data-value') !== undefined){
                var value = $( this ).attr('data-value');
                if(value !== ''){
                    var $parent = $( this ).closest('.dropdown-menu');
                    if($( $parent ).attr('data-selected') !== undefined && $( $parent ).attr('data-key') !== undefined){
                        var $wrapper = $( this ).closest('.dataTable');
                        var settings = init($wrapper);
                        var selected = $( $parent ).attr('data-selected');
                        var key = $( $parent ).attr('data-key');
                        pushData(settings, $( $parent ).attr('data-key'), value);
                        ajaxLoad(settings, $wrapper);
                    }
                }
            }
            event.preventDefault();
        });


        function pushData(settings, key, value){
            settings.data.push({name: key, value: value});
        }

        function checkBoxListener($wrapper){
            if($( $wrapper ).find('input.dt_check:checked').length) {
                $( $wrapper ).find('.dropdown.action .dropdown-toggle').removeClass('disabled');
            } else {
                $( $wrapper ).find('.dropdown.action .dropdown-toggle').addClass('disabled');
            }
        }

        function init($wrapper){
            var settings = {
                url: "",
                method: "GET",
                data: [],
                dataType: "html",
            };

            var $table = $( $wrapper ).find('table.table:first-child');
            if($( $table ).length){
                if ($($table).attr('data-url') !== undefined) {
                    settings.url = $($table).attr('data-url');
                }
                $($table).find('tr > th.sortable').each(function () {
                    if ($( this ).hasClass('order')) {
                        pushData(settings, 'order', $( this ).attr('data-order'));
                        pushData(settings, 'dir', $( this ).attr('data-dir'));
                    }
                });
            }

            var $search = $( $wrapper ).find('form.dt-search:first-child');
            if($( $search ).length){
                pushData(settings, 'search', $( $search ).find('input[name=search]').val());
            }

            $( $wrapper ).find('ul.pagination li.page-item.active a.page-link').each(function(){
                if ($( this ).attr('data-page') !== undefined) {
                    pushData(settings, 'page', $( this ).attr('data-page'));
                }
            });

            $( $wrapper ).find('.dropdown.select .dropdown-menu').each(function(){
                if($( this ).attr('data-selected') !== undefined && $( this ).attr('data-key') !== undefined){
                    var selected = $( this ).attr('data-selected');
                    var key = $( this ).attr('data-key');
                    if(key !== '' && selected !== '' && selected > 0) {
                        settings.data.season = selected;
                        pushData(settings, key, selected);
                    }
                }
            });

            return settings;
        }

        function ajaxLoad(settings, $wrapper){
            console.log(settings);
            if(settings.url) {
                $.ajax({
                    method: settings.method,
                    url: settings.url,
                    data: settings.data,
                    dataType: settings.dataType,
                    success: function (response) {
                        $( $wrapper ).html(response);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            }
        }
    });

};