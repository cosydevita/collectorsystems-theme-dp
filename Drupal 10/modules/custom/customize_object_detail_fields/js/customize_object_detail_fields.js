(function ($) {
  $(document).ready(function () {
    // Replace 'edit-select-field1' and 'edit-select-field2' with the actual IDs or classes of your select fields.
    const $selectField1 = $('#select-field1');
    const $selectField2 = $('#select-field2');
    const $curerentSelectField1Options = $('#current-select-field1-options');
    const $curerentSelectField2Options = $('#current-select-field2-options');


    // Initialize the hidden field with the initial state of select_field1.
    $curerentSelectField1Options.val($selectField1.val());

    // Initialize the hidden field with the initial state of select_field2.
    $curerentSelectField2Options.val($selectField2.val());

    // Track changes in select_field1.

    $selectField1.on('change', function () {

      update_curerentSelectField1Options();

    });

    $selectField2.on('change', function () {
      update_curerentSelectField2Options();

    });


    function update_curerentSelectField1Options(){
      const curerentSelectField1OptionsObject = {};
      $('#' + 'select-field1' + ' option').each(function() {
          var value = $(this).val();
          var text = $(this).text();
          curerentSelectField1OptionsObject[value] = text;

      });

      // console.log(curerentSelectField1OptionsObject);
      var jsonOptions = JSON.stringify(curerentSelectField1OptionsObject);

      $curerentSelectField1Options.val(jsonOptions);
      // console.log($curerentSelectField1Options.val())
    }


    function update_curerentSelectField2Options(){
      const curerentSelectField2OptionsObject = {};
      $('#' + 'select-field2' + ' option').each(function() {
          var value = $(this).val();
          var text = $(this).text();
          curerentSelectField2OptionsObject[value] = text;
      });
      // console.log(curerentSelectField2OptionsObject);
      var jsonOptions = JSON.stringify(curerentSelectField2OptionsObject);

      $curerentSelectField2Options.val(jsonOptions);
      console.log($curerentSelectField2Options.val())
    }

    // // Handle the drag-and-drop functionality (assuming you're using some UI library).
    // // When options are moved from select_field1 to select_field2 or vice versa, update the hidden field.

    // // For example, if you're using jQuery UI Sortable:
    // $selectField1.sortable({
    //   connectWith: $selectField2,
    //   update: function (event, ui) {
    //     const selectedOptions = $selectField1.val();
    //     $hiddenField.val(selectedOptions.join(','));
    //   },
    // });

    // Repeat a similar setup for select_field2 if needed.

    // Finally, when the form is submitted, the hidden field will contain the updated options.


    $('#move-to-select2').on('click', function() {
      // Move selected option(s) from select1 to select2
      $('#select-field1 option:selected').appendTo('#select-field2');
      $selectField1.val('')
      $selectField2.val('')
      update_curerentSelectField1Options()
      update_curerentSelectField2Options()

    });

    $('#move-to-select1').on('click', function() {
      // Move selected option(s) from select2 to select1
      $('#select-field2 option:selected').appendTo('#select-field1');
      $selectField1.val('')
      $selectField2.val('')
      update_curerentSelectField1Options()
      update_curerentSelectField2Options()
    });
  });
})(jQuery);
