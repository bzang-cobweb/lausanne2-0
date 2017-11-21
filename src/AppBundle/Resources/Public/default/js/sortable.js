(function( $ ) {

    $.fn.sortTable = function() {
        this.each(function() {
            var table = this;
            var $table = this;
            var dir = '';
            var position = -1;

            $(this).find('th').each(function(index){
                if($(this).hasClass('sort')) {
                    if ($(this).hasClass('desc')) {
                        dir = 'desc';
                        position = index;
                    } else if ($(this).hasClass('asc')) {
                        dir = 'asc';
                        position = index;
                    }

                    $(this).on('click', function() {
                        init($table, index, $(this).hasClass('desc') ? 'asc' : 'desc');
                        sort(table, index, $(this).hasClass('desc') ? 'desc' : 'asc');
                    })
                }
            });

            if(position >= 0){
                init($table, position, dir);
                sort(table, position, dir);
            }
        });

        function init($table, position, dir){
            $($table).find('tr').each(function(){
                // add sort icons on header column
                $(this).find('th').each(function(index){
                    if($(this).hasClass('sort')) {
                        $(this).find('i').remove();
                        $(this).removeClass('asc');
                        $(this).removeClass('desc');
                        if(index === position){
                            $(this).addClass(dir);
                            $(this).append(' <i class="fa fa-sort-'+dir+'" aria-hidden="true"></i>');
                        } else {
                            $(this).append(' <i class="fa fa-sort" aria-hidden="true"></i>');
                        }
                    }
                });
                // hihlight the sorted column
                $(this).find('td').removeClass('highlight');
                $(this).find('td:eq('+position+')').addClass('highlight');
            });
        }

        function sort(table, position, dir) {
            var rows, switching, i, x, y, shouldSwitch, switchcount = 0;
            switching = true;
            // Set the sorting direction to ascending:
            /* Make a loop that will continue until
             no switching has been done: */
            while (switching) {
                // Start by saying: no switching is done:
                switching = false;
                rows = table.getElementsByTagName("TR");
                /* Loop through all table rows (except the
                 first, which contains table headers): */
                for (i = 1; i < (rows.length - 1); i++) {
                    // Start by saying there should be no switching:
                    shouldSwitch = false;
                    /* Get the two elements you want to compare,
                     one from current row and one from the next: */
                    x = rows[i].getElementsByTagName("TD")[position];
                    y = rows[i + 1].getElementsByTagName("TD")[position];

                    /* Check if the two rows should switch place,
                     based on the direction, asc or desc: */
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch= true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            // If so, mark as a switch and break the loop:
                            shouldSwitch=true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    /* If a switch has been marked, make the switch
                     and mark that a switch has been done: */
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    rows[i].getElementsByTagName("TD")[0].innerHTML = i;
                    rows[i + 1].getElementsByTagName("TD")[0].innerHTML = i + 1;
                    switching = true;
                    // Each time a switch is done, increase this count by 1:
                    switchcount++;
                }
            }
        }
    };

}( jQuery ));


