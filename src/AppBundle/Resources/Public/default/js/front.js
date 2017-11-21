$( document ).ready(function() {
    $( '#menu-close').on('click', function(){
        $( '#btn-burger')
            .addClass('collapsed')
            .attr('aria-expanded', false);
        $('#navbarSupportedContent').removeClass('show');
    });

    $( '#submenu > #navTab > .nav-item > .nav-link').on('click', function(){
        var id = $( this ).attr('href');
        $( '#content-left, #content-middle, #content-right').each(function(){
            var current = '#' + $( this ).attr('id');
            if(current === id){
                $( this ).removeClass('hide-width');
            } else if(!$( this ).hasClass('hide-width')) {
                $( this ).addClass('hide-width');
            }
        });
    });

    $(window).scroll(lazyload);
    $('.html-replace').htmlReplacer();

    $('.carousel').carousel();

    $('.thumbnail-gallery').lightGallery({
        thumbnail:true
    });

    $('.gallery').lightGallery({
        thumbnail:true
    });

    $('table.sortable').sortTable();
    $('table.latest-standing').sortTable2({
        position: 2,
        dir: 'desc',
        rank: true
    });
});

function lazyload(){
    var wt = $(window).scrollTop();    //* top of the window
    var wb = wt + $(window).height();  //* bottom of the window

    $(".html-replace").each(function(){
        var ot = $(this).offset().top;  //* top of object (i.e. advertising div)
        var ob = ot + $(this).height(); //* bottom of object

        if(!$(this).attr("loaded") && wt<=ob && wb >= ot){
            $(this).attr("loaded",true);
            $(this).htmlReplacer();
        }
    });
}




