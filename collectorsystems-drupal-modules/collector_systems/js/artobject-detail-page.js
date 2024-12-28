(function ($, Drupal) {
  Drupal.behaviors.customImageLightbox = {
    attach: function (context, settings) {
      function feature_custom_image_lightbox_draggable() {
        let scale = 1; // Initial scale factor
        let current_image_index  = 0;

        // Show lightbox when image is clicked
        // $("body", context).on("click", "#draggable-image-link", function (event) {
        $(once('draggable-image-link', '#draggable-image-link', context)).on('click', function () {
          current_image_src = $(this).children('img').attr('src-slideshow');
          $("#lightbox-image").attr('src', current_image_src);
          $("#lightbox-custom-draggable").fadeIn();
          $("#lightbox-image").draggable();
          fitImageToScreen();
          resetImageTransform();
          current_image_index = parseInt($("#lightbox-image").attr('image_index'));
        });

        // Close the lightbox
        $("#lightbox-close", context).on("click", function () {
          $("#lightbox-custom-draggable").fadeOut();
        });

        // Zoom in functionality
        // $("#zoom-in", context).on("click", function () {
        $(once('zoom-in', '#zoom-in', context)).on('click', function () {
          scale *= 1.2; // Increase scale factor
          $("#lightbox-image").css("transform", `scale(${scale})`);
        });

        // Zoom out functionality
        // $("#zoom-out", context).on("click", function () {
        $(once('zoom-out', '#zoom-out', context)).on('click', function () {

          let currentImageWidth = $("#lightbox-image").width() * scale;
          if (currentImageWidth > 200) {
            scale /= 1.2; // Decrease scale factor
            $("#lightbox-image").css("transform", `scale(${scale})`);
          }
        });

        // Function to fit the image to the screen
        function fitImageToScreen() {
          const img = $("#lightbox-image");
          const viewportWidth = $(window).width();
          const viewportHeight = $(window).height();

          img.css({
            'width': 'auto',
            'height': 'auto',
            'transform': 'scale(1)' // Reset scale
          });

          const scale = Math.min(viewportWidth / img.width(), viewportHeight / img.height());
          img.css({
            'width': img.width() * scale,
            'height': img.height() * scale
          });
        }

        // Function to reset image transform
        function resetImageTransform() {
          scale = 1; // Reset scale factor
          $("#lightbox-image").css({
            'transform': `scale(${scale})`,
            'left': '0',
            'top': '0',
            'right': '0',
            'bottom': '0',
          });
        }

        // Adjust image size on window resize
        $(window, context).on('resize', fitImageToScreen);


        // Mouse scroll zoom
        $("#lightbox-custom-draggable", context).on("wheel", function (event) {
          let currentImageWidth = $("#lightbox-image").width() * scale;
          event.preventDefault();

          if (event.originalEvent.deltaY < 0) {
            scale *= 1.2; // Zoom in
          } else if (currentImageWidth > 200) {
            scale /= 1.2; // Zoom out
          }
          $("#lightbox-image").css("transform", `scale(${scale})`);
        });



        // $('#btn-next-draggable', context).on('click', function () {
        $(once('btn-next-draggable', '#btn-next-draggable', context)).on('click', function () {

          let next_image_index = parseInt(current_image_index + 1);
          next_image_src = $(`#carouselA div[data-slide-number="${next_image_index}"] img`).attr('src-slideshow');
          if (next_image_src) {
            $("#lightbox-image").attr('src', next_image_src);
            current_image_index = next_image_index;
            fitImageToScreen();
            resetImageTransform();
          }
        });

        // $('#btn-prev-draggable', context).on('click', function () {
        $(once('btn-prev-draggable', '#btn-prev-draggable', context)).on('click', function () {

          let prev_image_index = parseInt(current_image_index - 1);
          prev_image_src = $(`#carouselA div[data-slide-number="${prev_image_index}"] img`).attr('src-slideshow');
          if (prev_image_src) {
            $("#lightbox-image").attr('src', prev_image_src);
            current_image_index = prev_image_index;
            fitImageToScreen();
            resetImageTransform();
          }
        });
      }
      $('#lightbox-custom-draggable').appendTo('body');
      feature_custom_image_lightbox_draggable();

    }
  };
})(jQuery, Drupal);
