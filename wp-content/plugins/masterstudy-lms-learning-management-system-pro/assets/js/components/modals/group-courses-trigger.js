(function ($) {
  $(document).ready(function (e) {
    $('body').on('click', '.masterstudy-group-courses-modal__header-title-close, .masterstudy-group-courses-modal__close', function () {
      $(this).closest('.masterstudy-group-courses-modal').removeClass('active');
      $('body').removeClass('masterstudy-group-courses-modal-active');
      $(this).closest('.masterstudy-group-courses-modal').find('.masterstudy-group-courses-modal__wrapper').removeClass('active');
      $(this).parents().find('.masterstudy-group-courses__error > div').hide();
      $(this).parents().find('.masterstudy-group-courses__addition-list input').removeClass('invalid-email');
    });

    $('[data-masterstudy-modal]').on('click', function (e) {
      e.preventDefault();
      const modalName = $(this).data('masterstudy-modal');
      $('body').addClass('masterstudy-group-courses-modal-active');
      $(`.${modalName}`).addClass('active');

      const $group_courses_list = $('body').find('.masterstudy-group-courses__list-wrap');

      if ($group_courses_list.height() >= 300) {
        $group_courses_list.addClass('scrolled');
      }

      setTimeout(function() {
        $(`.${modalName} .masterstudy-group-courses-modal__wrapper`).addClass('active');
      }, 30);
    });
  });
})(jQuery);
