(function($) {
    $(document).ready(function () {
        if (window.matchMedia('(max-width: 1023.98px)').matches) {
            if ($('.masterstudy-single-course-classic').length > 0) {
                $('.masterstudy-single-course-classic__sidebar').children().appendTo('.masterstudy-single-course-classic__main');
                $('.masterstudy-single-course-classic__sidebar').remove();
            } else if ($('.masterstudy-single-course-dynamic-sidebar').length > 0) {
                $('.masterstudy-single-course-dynamic-sidebar__sticky-wrapper').children().appendTo('.masterstudy-single-course-dynamic-sidebar__main');
                $('.masterstudy-single-course-dynamic-sidebar__sticky').remove();
            } else if ($('.masterstudy-single-course-dynamic').length > 0) {
                $('.masterstudy-single-course-dynamic__sidebar').children().appendTo('.masterstudy-single-course-dynamic__main');
                $('.masterstudy-single-course-dynamic__sidebar').remove();
            } else if ($('.masterstudy-single-course-full-width').length > 0) {
                $('.masterstudy-single-course-full-width__sidebar').children().appendTo('.masterstudy-single-course-full-width__main');
                $('.masterstudy-single-course-full-width__sidebar').remove();
            } else if ($('.masterstudy-single-course-modern-curriculum').length > 0) {
                $('.masterstudy-single-course-modern-curriculum__sidebar').children().appendTo('.masterstudy-single-course-modern-curriculum__main');
                $('.masterstudy-single-course-modern-curriculum__sidebar').remove();
            } else if ($('.masterstudy-single-course-modern').length > 0) {
                $('.masterstudy-single-course-modern__sidebar').children().appendTo('.masterstudy-single-course-modern__bottombar');
                $('.masterstudy-single-course-modern__sidebar').remove();
            } else if ($('.masterstudy-single-course-sleek-sidebar').length > 0) {
                $('.masterstudy-single-course-sleek-sidebar__sticky-wrapper').children().appendTo('.masterstudy-single-course-sleek-sidebar__main');
                $('.masterstudy-single-course-sleek-sidebar__sticky-block').children().appendTo('.masterstudy-single-course-sleek-sidebar__main');
                $('.masterstudy-single-course-tabs__content').insertAfter($('.masterstudy-single-course-tabs'));
                $('.masterstudy-single-course-sleek-sidebar__sticky').remove();
                $('.masterstudy-single-course-sleek-sidebar__sticky-block').remove();
            } else if ($('.masterstudy-single-course-timeless').length > 0) {
                $('.masterstudy-single-course-timeless__topbar-side').children().appendTo('.masterstudy-single-course-timeless__topbar-main');
                $('.masterstudy-single-course-timeless__sidebar').children().appendTo('.masterstudy-single-course-timeless__main');
                $('.masterstudy-single-course-timeless__sidebar').remove();
                $('.masterstudy-single-course-timeless__topbar-side').remove();
            }
        }

        $('.masterstudy-plyr-video-player').each(function(i, player) {
            new Plyr($(player));
            const overlay = $("<div>").addClass('plyr-overlay');
            $(player).find('iframe').before(overlay);
            $(player).find('iframe').after(overlay.clone());
        });
        $('.plyr__video-wrapper').one('click', function() {
            $('.masterstudy-single-course .masterstudy-single-course__main .masterstudy-single-course-tabs__container_active').css('width', 'auto');
        });
    });
})(jQuery);