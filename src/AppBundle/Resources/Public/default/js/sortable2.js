jQuery.fn.sortTable2 = function( options ) {
    var defaults = {
        position: -1,
        dir: '',
        rank: false,
        header: false,
    }

    var settings = $.extend( {}, defaults, options );

    this.each(function() {
        if (settings.position >= 0 && (settings.dir === 'asc' || settings.dir === 'desc')) {
            sort(this, settings.position, settings.dir, settings.rank, settings.header);
        }
    });

    function sort(table, position, dir, rank, header) {
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
            for (i = header ? 1 : 0; i < (rows.length - 1); i++) {
                // Start by saying there should be no switching:
                shouldSwitch = false;
                /* Get the two elements you want to compare,
                 one from current row and one from the next: */
                x = rows[i].getElementsByTagName("TD")[position];
                y = rows[i + 1].getElementsByTagName("TD")[position];


                /* Check if the two rows should switch place,
                 based on the direction, asc or desc: */
                if (dir == "asc") {
                    if (parseInt(x.innerHTML.toLowerCase()) > parseInt(y.innerHTML.toLowerCase())) {
                        // If so, mark as a switch and break the loop:
                        shouldSwitch= true;
                        break;
                    }
                } else if (dir == "desc") {
                    if (parseInt(x.innerHTML.toLowerCase()) < parseInt(y.innerHTML.toLowerCase())) {
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
                if(rank) {
                    rows[i].getElementsByTagName("TD")[0].innerHTML = header ? i : i + 1;
                    rows[i + 1].getElementsByTagName("TD")[0].innerHTML = header ? i + 1 : i + 2;
                }
                switching = true;
                // Each time a switch is done, increase this count by 1:
                switchcount++;
            }
        }
    }

};


