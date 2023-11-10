(function ($, Drupal) {
    Drupal.behaviors.customApiIntegration = {
      attach: function (context, settings) {
        $('.filter-keywords-select').once('customApiIntegration').on('click', 'option', function () {
          $(this).prop('selected', !$(this).prop('selected'));
          
          // Reorder selected options to the top
          var selectedOptions = $(this).parent().find('option:selected');
          $(this).parent().prepend(selectedOptions);
        });
      }
    };
  })(jQuery, Drupal);