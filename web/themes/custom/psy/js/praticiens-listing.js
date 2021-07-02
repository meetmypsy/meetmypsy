(function ($) {
  "use strict";
  var limit_shown = 4;
  Drupal.behaviors.optionsDisplay = {
    attach: function (context) {
      $(".view-praticiens .views-col", context).each(function (index) {
        let elements = $(this).find('.field--type-list-string .field__item');
        if (elements.length > limit_shown) {
          elements.each(function (index) {
            if (index <= limit_shown) {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
        }
      });
    }
  };

  Drupal.behaviors.seemorelinkAdd = {
    attach: function (context) {
      $(".view-praticiens .views-col", context).once('link-add').each(function (index) {
        let elements = $(this).find('.field--type-list-string .field__item');
        if (elements.length > limit_shown) {
          let seemore = $(this).find('.praticien-data');
          seemore.append("<div class='morelessoptions reduced'>" + Drupal.t('See more') + '</div>');
        }
      });
    }
  };

  Drupal.behaviors.moreoptionsclick = {
    attach: function (context) {
      $(".view-praticiens .views-col .morelessoptions", context).once('morelessoptions-click').click(function () {
        // Toggle class
        $(this).toggleClass('reduced');

        if ($(this).hasClass('reduced')) {
          $(this).text(Drupal.t('See more'));
          // Get all items to show.
          let elements = $(this).parent().find('.field--type-list-string .field__item');

          let sumHeightToMinus = 30;
          if (elements.length > limit_shown) {
            elements.each(function (index) {
              if (index <= limit_shown) {
                $(this).show();
              } else {
                // Assuming each element will be alone in a line even if it is not true all the time.
                sumHeightToMinus += elements[index].scrollHeight;
                $(this).hide();
              }
            });
          }

          // get current views col.
          let viewsCol = $(this).parent().parent().parent();
          let currentHeight = viewsCol.height();
          viewsCol.height(currentHeight - sumHeightToMinus);
        } else {
          $(this).text(Drupal.t('See less'));
          // Get all items to show.
          let elements = $(this).parent().find('.field--type-list-string .field__item');
          elements.show();
          let sumHeightToAdd = 30;
          // Assuming each element will be alone in a line even if it is not true all the time.
          for (var i = limit_shown + 1, l = elements.length; i < l; i++) {
            sumHeightToAdd += elements[i].scrollHeight;
          }
          // get current views col.
          let viewsCol = $(this).parent().parent().parent();
          let currentHeight = viewsCol.height();
          viewsCol.height(currentHeight + sumHeightToAdd);

        }
      });
    }
  };
})(jQuery);
