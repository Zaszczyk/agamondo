$(document).ready(function() {
    var sections = $('section')
        , nav = $('#slideNav')
        , nav_height = nav.outerHeight();

    $(window).on('scroll', function() {
        var cur_pos = $(this).scrollTop();

        sections.each(function() {
            var top = $(this).offset().top,
                bottom = top + $(this).outerHeight();

            if(cur_pos >= top && cur_pos <= bottom){
                //console.log(nav.find('li'));
                $('#slideNav li').removeClass('active');
                sections.removeClass('active');

                $(this).addClass('active');
                nav.find('a[href="#' + $(this).attr('id') + '"]').addClass('active');
                $('a[href="#' + $(this).attr('id') + '"]').closest('li').addClass('active');
                console.log($('#slideNav a[href="#' + $(this).attr('id') + '"]'));
            }
        });
    });

    $('#slideNav li a').on('click', function(e) {
        e.preventDefault();
        var id = $(this).attr('href');

        var replaced = id.replace('#', '');


        $('html, body').animate({

            scrollTop: $('section[id="' + replaced + '"]').offset().top

        }, 800);

        return false;
    });
});