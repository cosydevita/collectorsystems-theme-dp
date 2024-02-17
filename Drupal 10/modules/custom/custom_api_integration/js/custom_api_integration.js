(function ($, Drupal) {
    Drupal.behaviors.customApiIntegration = {
      attach: function (context, settings) {
        $('.filter-keywords-select:not(.customApiIntegration-processed)').on('click', 'option', function () {
          $(this).prop('selected', !$(this).prop('selected'));

          // Reorder selected options to the top
          var selectedOptions = $(this).parent().find('option:selected');
          $(this).parent().prepend(selectedOptions);
      }).addClass('customApiIntegration-processed');

      }
    };
  })(jQuery, Drupal);
