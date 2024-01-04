(function ($, Drupal) {
  Drupal.behaviors.myCustomBehavior = {
    attached: false,

    attach: function (context, settings) {
      var sessionToken = '';

      function getSessionToken() {
        $.ajax({
          type: 'GET',
          // url: '/rest/session/token',
          url: '/session/token',
          cache: false,
          success: function(token) {
            sessionToken = token;
            fetch_group_ajax(sessionToken)
          }
        });
      }
        $('.loading-indicator').hide();
        $('.loading-indicator-image').hide();
        $('.loading-indicator-image-object').hide();



        // $('#create-table-button').on('click', function () {
        $(once('createTableButton', '#create-table-button')).on('click.createTableButton', function () {

          if (sessionToken == '') return;

            $('.loading-indicator').show();
            $('.wrapper').hide();

            var button = $(this);

            console.log('Error: ' + button);
                button.prop('hidden', true);
             $.ajax({
                type: 'POST',
                url: '/v1/cs-create-tables?_format=json',
                contentType: 'application/json',
                data: JSON.stringify({
                }),
                headers: {
                  'Accept': 'application/json',
                  'X-CSRF-Token': sessionToken // Include the CSRF token
                },
                success: function (response) {
                    debugger;
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                    button.prop('hidden', false);
                    $('.wrapper').show();
                },
                complete: function ()
                {
                    $('.loading-indicator').hide();
                    $('.wrapper').show();
                    alert("All the tables are created successfully!");
                    button.prop('hidden', false);
                }
            });
        });

          // $('#custom-database-save').on('click', function () {
          $(once('customDatabaseSaveButton', '#custom-database-save')).on('click.customDatabaseSaveButton', function () {

              $('.loading-indicator-image').show();
              $('#image-saved').hide();

            $.ajax({
                type: 'POST',
                // url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/save_image_database_action.php',
                url: '/v1/cs-save-image-database-ajax?_format=json',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'save_image_database_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                }),
                headers: {
                  'Accept': 'application/json',
                  'X-CSRF-Token': sessionToken // Include the CSRF token
                },
                success: function (response) {
                    debugger;
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    $('#image-saved').show();
                    $('.loading-indicator-image').hide();
                    console.log('Error: ' + error);

                },
                complete: function ()
                {
                    $('#image-saved').show();
                    $('.loading-indicator-image').hide();
                    alert("All the images are saved in the database successfully!");

                }
            });
        });

        // $('#custom-directory-save').on('click', function () {
        $(once('customDirectorySaveButton', '#custom-directory-save')).on('click.customDirectorySaveButton', function () {


            $('.loading-indicator-image').show();
              $('#image-saved').hide();

            var button = $(this);
                button.prop('disabled', true);
            $.ajax({
                type: 'POST',
                // url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/save_image_directory_action.php',
                url: '/v1/cs-save-image-directory-ajax?_format=json',
                contentType: 'application/json',
                data: JSON.stringify({

                    action: 'save_image_directory_ajax'
                }),
                headers: {
                  'Accept': 'application/json',
                  'X-CSRF-Token': sessionToken // Include the CSRF token
                },

                success: function (response){
                    debugger;
                    console.log(response);
                },

                error: function (xhr, status, error) {
                    $('#image-saved').show();
                    $('.loading-indicator-image').hide();
                    console.log('Error: ' + error);
                    button.prop('disabled', false);
                },

                complete: function ()
                {
                    $('#image-saved').show();
                    $('.loading-indicator-image').hide();
                    alert("All the images are saved in their respective directories successfully!");
                    button.prop('disabled', false);
                }
            });
          });
        // $('#custom-object-database-save').on('click', function () {
        $(once('customObjectDatabaseSaveButton', '#custom-object-database-save')).on('click.customObjectDatabaseSaveButton', function () {

          $('.loading-indicator-image-object').show();
          $('#image-saved-object').hide();

          var button = $(this);
            button.prop('disabled', true);
          $.ajax({
              type: 'POST',
              // url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/save_object_image_database_action.php',
              url: '/v1/cs-save-object-image-database-ajax?_format=json',
              contentType: 'application/json',
              data: JSON.stringify({
                action: 'save_object_image_database_ajax'
              }),
              headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': sessionToken // Include the CSRF token
              },
              success: function (response) {
                  debugger;
                  console.log(response);
              },
              error: function (xhr, status, error) {
                  $('.loading-indicator-image-object').hide();
            $('#image-saved-object').show();
                  console.log('Error: ' + error);
                  button.prop('disabled', false);
              },
              complete: function ()
              {
                  $('.loading-indicator-image-object').hide();
            $('#image-saved-object').show();
                  alert("All Object Images are stored in the database successfully!");
                  button.prop('disabled', false);
              }
          });
        });
        // $('#custom-object-directory-save').on('click', function () {
        $(once('customObjectDirectorySaveButton', '#custom-object-directory-save')).on('click.customObjectDirectorySaveButton', function () {

            $('.loading-indicator-image-object').show();
              $('#image-saved-object').hide();

            $.ajax({
                type: 'POST',
                // url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/save_object_image_directory_action.php',
                url: '/v1/cs-save-object-image-directory-ajax?_format=json',
                contentType: 'application/json',
                data: JSON.stringify({
                  action: 'save_object_image_directory_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                }),
                headers: {
                  'Accept': 'application/json',
                  'X-CSRF-Token': sessionToken // Include the CSRF token
                },
                success: function (response) {
                    debugger;
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    $('.loading-indicator-image-object').hide();
              $('#image-saved-object').show();
                    console.log('Error: ' + error);

                },
                complete: function ()
                {
                    $('.loading-indicator-image-object').hide();
              $('#image-saved-object').show();
                    alert("All Object Images are stored in the directory successfully!");

                }
            });
        });
        $('#create-image-detail-button').on('click', function () {
            var button = $(this);
                button.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/create_image_detail.php',
                data: {
                    action: 'create_image_detail_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                },
                success: function (response) {
                    debugger;
                    console.log(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                    button.prop('disabled', false);
                },
                complete: function ()
                {
                    $('.loading-indicator').hide();
                    alert("All Object Images are stored in the directory successfully!");
                    button.prop('disabled', false);
                }
            });
        });

         $('#fetch-exhibition-button').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/fetch_exhibition_action.php',
                data: {
                    action: 'fetch_exhibition_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                },
                success: function (response) {
                    debugger;
                    alert(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                }
            });
        });
         $('#fetch-collection-button').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/fetch_collection_action.php',
                data: {
                    action: 'fetch_collection_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                },
                success: function (response) {
                    debugger;
                     alert(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                }
            });
        });
          $('#fetch-group-button').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/fetch_group_action.php',
                data: {
                    action: 'fetch_group_ajax'
                    // Add any other data you need to pass to the AJAX handler (if required)
                },
                success: function (response) {
                    debugger;
                    alert(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                }
            });
        });

        if (!this.attached) {
          getSessionToken();
          this.attached = true;
        }


        function fetch_group_ajax(sessionToken){
          $.ajax({
            type: 'POST',
            // url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/fetch_group_action.php',
            url: '/v1/cs-fetch-group-ajax?_format=json',
            contentType: 'application/json',
            data: JSON.stringify({
              action: 'fetch_group_ajax'

            }),
            headers: {
              'Accept': 'application/json',
              'X-CSRF-Token': sessionToken // Include the CSRF token
            },
            success: function (response) {

                var data = response;
                console.log(data)
                // Display the first word in the #response-data element
                $('#response-data').append('<span class="count-number">'+data.DbCountForGroup+'</span>');
                $('#response-data1').append('<span class="count-number">'+data.apiCountForGroup+'</span>');

                $('#resp-data').append('<span class="count-number">'+data.DbCountForObject+'</span>');
                $('#resp-data1').append('<span class="count-number">'+data.apiCountForObject+'</span>');

                $('#resp-data6').append('<span class="count-number">'+data.DbcollectionCount+'</span>');
                $('#resp-data7').append('<span class="count-number">'+data.ApicollectionCount+'</span>');

                $('#resp-data2').append('<span class="count-number">'+data.DbExhibitionsCount+'</span>');
                $('#resp-data3').append('<span class="count-number">'+data.ApiExhibitionsCount+'</span>');

                $('#resp-data4').append('<span class="count-number">'+data.DbCountForArtist+'</span>');
                $('#resp-data5').append('<span class="count-number">'+data.apiCountForArtist+'</span>');

                $('.count-wrapper .spinner').hide()
                $('.count-wrapper .count-data').show()
            },
            error: function (xhr, status, error) {
              $('.count-wrapper .spinner').hide()
              $('.count-wrapper .count-data').show()
                // Handle errors if the AJAX request fails
                console.log('Error: ' + error);
            }
          });
        }

      }
      };
      })(jQuery, Drupal);


jQuery(document).ready(function( $ ) {
 console.log("Javascipt start");
 var selectedValues = [];
    $(function () {

     $('#addFields').click(function () {
          var itemsToAdd = [];

          $("#SelectBoxForThemeObjectList option:selected").each(function () {
              var optionVal = $(this).val();
              var key = $(this).data('key');
              selectedValues.push(optionVal);
              if ($('#SelectedItemsForList option[value="' + optionVal + '"][data-key="' + key + '"]').length == 0) {
                  itemsToAdd.push($(this));
              }
          });
          $("#SelectedItemsForList").append(itemsToAdd);
      });
      $('#sync').click(function() {
             $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/create_object_field_list.php',
                data: {
                    action: 'create_object_ajax',
                    selectedValues: selectedValues
                },
                success: function (response) {
                    debugger;
                    alert(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                }
            });



      });
  });


  $('#removeFields').click(function () {
       //alert("Check this one");
       var itemsToAddDelete = [];
       var selectedItems = [];
       var inHTML = "";
       $("#SelectedItemsForList option:selected").each(function () {
              var optionVal = $(this).val();
              var optionText = $(this).text();
              var key = $(this).data('key');
              selectedItems.push(optionVal);
              inHTML += '<option onmouseover="this.style.color=&quot;#2271b1&quot;" onmouseout="this.style.color=&quot;#2c3338&quot;" value="' + optionVal + '" data-key="' + key + '">' + optionText + '</option>';

       });
       $("#SelectBoxForThemeObjectList").append(inHTML);
       $("#SelectedItemsForList option:selected").remove();
        $.ajax({
                type: 'POST',
                url: 'http://collectorsystems-wp.docksal/wp-content/themes/collectorsystems-theme-wp/TableCreation/remove_object_field_list.php',
                data: {
                    action: 'remove_object_ajax',
                    selectedItems: selectedItems
                },
                success: function (response) {
                    debugger;
                    alert(response);
                },
                error: function (xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log('Error: ' + error);
                }
            });
            return false;
  });

  $(".changeOrder").click(function(){

    var $op = $('#SelectedItemsForList option:selected'),
        $this = $(this);
    if($op.length){
        ($this.val() == 'Up') ?
            $op.first().prev().before($op) :
            $op.last().next().after($op);
    }
  });

 });

function savefieldopt() {
    var selectedOpts = $('#SelectedItemsForList option:selected');
    if (selectedOpts.length === 0) {
        alert("Please select field(s) to save!");
        return false; // Prevent form submission
    }
    if (selectedOpts.length > 5) {
        alert("You cannot save more than 5 fields!");
        return false; // Prevent form submission
    }
    return true; // Allow form submission
}









