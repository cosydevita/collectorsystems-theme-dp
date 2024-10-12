(function ($, Drupal) {
    Drupal.behaviors.customApiIntegration = {
      attach: function (context, settings) {
        $('.filter-keywords-select:not(.customApiIntegration-processed)').on('click', 'option', function () {
          $(this).prop('selected', !$(this).prop('selected'));

          // Reorder selected options to the top
          var selectedOptions = $(this).parent().find('option:selected');
          $(this).parent().prepend(selectedOptions);

        }).addClass('customApiIntegration-processed');


        //select2
        $('.filter-keywords-select:not(.customApiIntegration-processed-select2)').select2({
          closeOnSelect : false,
          placeholder : "Select Keywords",
          //allowHtml: true,
          allowClear: true,
          tags: true,
          width: '659px'
        }).addClass('customApiIntegration-processed-select2');;

      }
    };
  })(jQuery, Drupal);
