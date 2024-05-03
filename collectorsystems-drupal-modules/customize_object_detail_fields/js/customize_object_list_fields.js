(function ($) {
  $(document).ready(function () {
    // Replace 'edit-select-field1' and 'edit-select-field2' with the actual IDs or classes of your select fields.
    const $selectField1 = $('#select-field1');
    const $selectField2 = $('#select-field2');
    const $curerentSelectField1Options = $('#current-select-field1-options');
    const $curerentSelectField2Options = $('#current-select-field2-options');


    $('#select-field1 option').removeAttr("selected");
    $('#select-field2 option').removeAttr("selected");


    // Initialize the hidden field with the initial state of select_field1.
    $curerentSelectField1Options.val($selectField1.val());

    // Initialize the hidden field with the initial state of select_field2.
    $curerentSelectField2Options.val($selectField2.val());

    // Track changes in select_field1.

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


    });

    $('#move-to-select1').on('click', function() {
      // Move selected option(s) from select2 to select1
      $('#select-field2 option:selected').appendTo('#select-field1');
      $selectField1.val('')
      $selectField2.val('')
    });

    $('#move-up').on('click', function() {
      moveOption(-1);
    });
    $('#move-down').on('click', function() {
      moveOption(1);
    });


    //Form submit
    $('#customize-object-list-fields-settings-form').submit( function(event){

      update_curerentSelectField1Options()
      update_curerentSelectField2Options()

      var selectedOptsForDisplay = $('#select-field2  option');
      if(selectedOptsForDisplay.length == 0)
      {
          alert("Please select field(s) to save!");
          return false;
      }
      if(selectedOptsForDisplay.length > 5)
      {
        alert("You can not save more than 5 fields!");
          return false;
      }

    })

    function moveOption(direction) {
      var select = document.getElementById("select-field2");
      var selectedOptions = Array.from(select.selectedOptions);

      if (direction === -1) {
          // Move up
          selectedOptions.forEach(function(option) {
              var index = option.index;
              if (index > 0) {
                  select.insertBefore(option, select.options[index - 1]);
              }
          });
      } else if (direction === 1) {
          // Move down
          for (var i = selectedOptions.length - 1; i >= 0; i--) {
              var option = selectedOptions[i];
              var index = option.index;
              if (index < select.options.length - 1) {
                  select.insertBefore(option, select.options[index + 2]);
              }
          }
      }
    }
  });
})(jQuery);
