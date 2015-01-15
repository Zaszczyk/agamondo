$( document ).ready(function() {
    var sections = $('section')
        , nav = $('#slide-nav')
        , nav_height = nav.outerHeight();

    $(window).on('scroll', function () {
        var cur_pos = $(this).scrollTop();

        sections.each(function() {
            var top = $(this).offset().top ,
                bottom = top + $(this).outerHeight();

            if (cur_pos >= top && cur_pos <= bottom) {
                //console.log(nav.find('li'));
                $('#slide-nav li').removeClass('active');

                sections.removeClass('active');

                $(this).addClass('active');
                nav.find('a[href="#'+$(this).attr('id')+'"]').addClass('active');
                $('a[href="#'+$(this).attr('id')+'"]').closest('li').addClass('active');

                //start video
                console.log($(this).attr('id'));
                if($(this).attr('id') == 'mainpage4'){
                    $('#video').attr('autoplay', '1');
                }
            }

        });
    });

    $('#slide-nav li a').on('click', function (e) {
        e.preventDefault();
        var id = $(this).attr('href');

        var replaced = id.replace('#', '');

        console.log(replaced);
        $('html, body').animate({

            scrollTop: $('section[id="'+replaced+'"]').offset().top

        }, 800);

        return false;
    });

    $('#video').attr('height', $(window).height());

});