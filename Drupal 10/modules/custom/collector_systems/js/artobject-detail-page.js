(function ($, Drupal) {
  $("body").append($("#cs-lightbox-custom-draggable"));

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
          $("#cs-lightbox-custom-draggable").fadeIn();
          $("#lightbox-image").draggable();
          fitImageToScreen();
          resetImageTransform();
          current_image_index = parseInt($("#lightbox-image").attr('image_index'));
        });

        // Close the lightbox
        $("#lightbox-close", context).on("click", function () {
          $("#cs-lightbox-custom-draggable").fadeOut();
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
            'transform': 'scale(1)', // Reset scale
            'max-width': '100%'
          });


          // // Scale to fit image to screen.
          // const scale = Math.min(viewportWidth / img.width());
          // $("#lightbox-image").css("transform", `scale(${scale})`);
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
        $("#cs-lightbox-custom-draggable", context).on("wheel", function (event) {
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
      feature_custom_image_lightbox_draggable();

    }
  };

  function feature_cs_custom_carousel() {

    let items = document.querySelectorAll('#carouselB .carousel-item')

    // If there are less than 4 items, hide the next and previous buttons
    if( items.length < 4) {
      $('.carousel-control-next').hide();
      $('.carousel-control-prev').hide();
    }

    items.forEach((el) => {
      const minPerSlide = items.length > 4 ? 4 : items.length;
      let next = el.nextElementSibling

      for (var i=1; i<minPerSlide; i++) {
        if (!next) {
            // wrap carousel by using first child
          next = items[0]
        }
        let cloneChild = next.cloneNode(true)
        el.appendChild(cloneChild.children[0])
        next = next.nextElementSibling
      }
    })
    let carouselAItems = document.querySelectorAll('#carouselA .carousel-item')

    const slideShowUrlsA =[];

    for(var k=0;k<carouselAItems.length;k++)
    {
        if($(carouselAItems[k]).length > 0)
        {
            if($(carouselAItems[k]).find("a.carouselAToggle").length > 0)
            {
                slideShowUrlsA.push($(carouselAItems[k]).find("a.carouselAToggle").attr("data-big"));
            }
        }
    }

    // Handle the carousel thumbnails
    $(document).on("click", ".thumb", function (e) {      
      // remove 'selected' class from all thumbnails
      $('#carouselB').find('.thumb').removeClass('selected');
      if($(this).addClass('selected').closest('div').length > 0)
      {
        var selectorIdx = $(this).attr('data-slide-to');
        // Update Carousel A
        $('#carouselA .carousel-item').removeClass('active');
        $('#carouselA .carousel-item').eq(selectorIdx).addClass('active');
      }
    });

    // Handles the carousel next and previous buttons
    $(document).on("click", ".carousel-control-next, .carousel-control-prev", function (ev) {
        ev.preventDefault();

        var $carouselB = $('#carouselB');
        var $activeItem = $carouselB.find('.carousel-item.active');  
        var $nextItem;

        if ($(this).hasClass('carousel-control-next')) {
            // Next button clicked
            $nextItem = $activeItem.next('.carousel-item');
            if (!$nextItem.length) {
                $nextItem = $carouselB.find('.carousel-item').first();
            }
        } else {
            // Prev button clicked
            $nextItem = $activeItem.prev('.carousel-item');
            if (!$nextItem.length) {
                $nextItem = $carouselB.find('.carousel-item').last();
            }
        }

        // Update carouselB
        $activeItem.removeClass('active');
        $nextItem.addClass('active');

        $('#carouselB').find('.thumb').removeClass('selected');
        $nextItem.find('.thumb').first().addClass('selected');

        // Sync carouselA
        var $carouselA = $('#carouselA');
        $carouselA.find('.carousel-item').removeClass('active');
        $carouselA.find('.carousel-item').eq($nextItem.index()).addClass('active');
    });
  }
  
  feature_cs_custom_carousel();
})(jQuery, Drupal);
