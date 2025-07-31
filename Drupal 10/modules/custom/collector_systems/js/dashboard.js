(function ($, Drupal) {
  Drupal.behaviors.myCustomBehavior = {
    attached: false,

    attach: function (context, settings) {
      var sessionToken = "";
      const origin = window.location.origin;
      function getSessionToken() {
        $.ajax({
          type: "GET",
          // url: '/rest/session/token',
          url: origin + "/session/token",
          cache: false,
          success: function (token) {
            sessionToken = token;
            fetch_group_ajax(sessionToken);
            fetch_images_count_ajax(sessionToken);
          },
        });
      }
      $(".loading-indicator").hide();
      $(".loading-indicator-image").hide();
      $(".loading-indicator-image-object").hide();

      $(once("createTableButton", ".dataset-btn")).on(
        "click.createTableButton",
        function (event) {
          var btn_action = "";
          if (event.target.id === "btn-update-dataset") {
            btn_action = "update_dataset";
          } else if (event.target.id === "btn-reset-and-create-dataset") {
            btn_action = "reset_and_create_dataset";
          }

          window.location.href =
            "/admin/collector-systems/create-tables-form?btn_action=" +
            btn_action;

          // if (sessionToken == '') return;

          // $('.loading-indicator').show();
          // $('.wrapper').hide();

          // var button = $(this);
          // button.prop('disabled', true);
          //  $.ajax({
          //     type: 'POST',
          //     url: origin+'/v1/cs-create-tables?_format=json',
          //     contentType: 'application/json',
          //     data: JSON.stringify({
          //       btn_action: btn_action
          //     }),
          //     headers: {
          //       'Accept': 'application/json',
          //       'X-CSRF-Token': sessionToken // Include the CSRF token
          //     },
          //     success: function (response) {
          //         debugger;
          //         console.log(response);
          //     },
          //     error: function (xhr, status, error) {
          //         // Handle errors if the AJAX request fails
          //         console.log('Error: ' + error);
          //         button.prop('disabled', false);
          //         $('.wrapper').show();
          //     },
          //     complete: function ()
          //     {
          //         $('.loading-indicator').hide();
          //         $('.wrapper').show();
          //         if(btn_action == 'update-dataset'){
          //           alert("Dataset updated successfully!");
          //         }else{
          //           alert("All the tables are created successfully!");
          //         }
          //         button.prop('disabled', false);
          //         //reload the page
          //         location.reload();

          //     }
          // });
        }
      );

      $(once("syncImagesButton", "#btn-sync-images")).on(
        "click.syncImagesButton",
        function () {
          window.location.href =
            "/admin/collector-systems/other-images-import?save_option=database";
        }
      );

      $(
        once("customObjectDatabaseSaveButton", "#custom-object-database-save")
      ).on("click.customObjectDatabaseSaveButton", function () {
        //Redirect to Import Form page
        window.location.href =
          "/admin/collector-systems/object-images-import?save_option=database";
      });
      $(
        once("customObjectDirectorySaveButton", "#custom-object-directory-save")
      ).on("click.customObjectDirectorySaveButton", function () {
        //Redirect to Import Form page
        window.location.href =
          "/admin/collector-systems/object-images-import?save_option=directory";
      });

      if (!this.attached) {
        getSessionToken();
        this.attached = true;
      }

      function fetch_group_ajax(sessionToken) {
        $.ajax({
          type: "POST",
          url: origin + "/collector-systems/get-total-count-data?_format=json",
          contentType: "application/json",
          data: JSON.stringify({
            action: "fetch_group_ajax",
          }),
          headers: {
            Accept: "application/json",
            "X-CSRF-Token": sessionToken, // Include the CSRF token
          },
          success: function (response) {
            var data = response;
            console.log(data);
            // Display the first word in the #response-data element
            $("#response-data").append(
              '<span class="count-number">' + data.DbCountForGroup + "</span>"
            );
            $("#response-data1").append(
              '<span class="count-number">' + data.apiCountForGroup + "</span>"
            );

            $("#resp-data").append(
              '<span class="count-number">' + data.DbCountForObject + "</span>"
            );
            $("#resp-data1").append(
              '<span class="count-number">' + data.apiCountForObject + "</span>"
            );

            $("#resp-data2").append(
              '<span class="count-number">' + data.DbcollectionCount + "</span>"
            );
            $("#resp-data3").append(
              '<span class="count-number">' +
                data.ApicollectionCount +
                "</span>"
            );

            $("#resp-data4").append(
              '<span class="count-number">' +
                data.DbExhibitionsCount +
                "</span>"
            );
            $("#resp-data5").append(
              '<span class="count-number">' +
                data.ApiExhibitionsCount +
                "</span>"
            );

            $("#resp-data6").append(
              '<span class="count-number">' + data.DbCountForArtist + "</span>"
            );
            $("#resp-data7").append(
              '<span class="count-number">' + data.apiCountForArtist + "</span>"
            );

            $(".count-wrapper .spinner").hide();
            $(".count-wrapper .count-data").show();
          },
          error: function (xhr, status, error) {
            $(".count-wrapper .spinner").hide();
            $(".count-wrapper .count-data").show();
            // Handle errors if the AJAX request fails
            console.log("Error: " + error);
          },
        });
      }
      

      function fetch_images_count_ajax(sessionToken) {
        $.ajax({
          type: "POST",
          url: origin + "/collector-systems/get-images-count-data?_format=json",
          contentType: "application/json",
          data: JSON.stringify({
            action: "fetch_group_ajax",
          }),
          headers: {
            Accept: "application/json",
            "X-CSRF-Token": sessionToken, // Include the CSRF token
          },
          success: function (response) {
            object_images_db_count = response.object_images_db_count;
            object_images_api_count = response.object_images_api_count;
            other_images_api_count = response.other_images_api_count;
            other_images_db_count = response.other_images_db_count;

            $('#object-images-db-count').text(object_images_db_count);
            $('#object-images-api-count').text(object_images_api_count);
            $('#other-images-api-count').text(other_images_api_count);
            $('#other-images-db-count').text(other_images_db_count);

            $('.count-wrapper-images .spinner-count').hide()
            $('.count-wrapper-images .count-data').show()
          },
          error: function(xhr, status, error) {
            // Handle errors if the AJAX request fails
            console.log('Error: ' + error);
          }
        })

        $('.custom-checkbox input[type="checkbox"]').on('change', function() {
          $.ajax({
              url: origin + "/collector-systems/save-checkbox-options-data-type",
              type: 'POST',
              headers: {
                Accept: "application/json",
                "X-CSRF-Token": sessionToken, // Include the CSRF token
              },
              data: {
                checkboxes: {
                    groups: $('#checkbox-groups').is(':checked') ? 1 : 0,
                    collections: $('#checkbox-collections').is(':checked') ? 1 : 0,
                    exhibitions: $('#checkbox-exhibitions').is(':checked') ? 1 : 0,
                    artists: $('#checkbox-artists').is(':checked') ? 1 : 0,
                },
              },
              success: function(response) {
                  // Handle the response from the server
                  console.log('Checkbox options saved successfully:', response);
              },
              error: function(xhr, status, error) {
                  console.error('AJAX Error: ' + status + error);
              }
          });

        });
      }
    },
  };
})(jQuery, Drupal);
