(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();


    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').css('top', '0px');
        } else {
            $('.sticky-top').css('top', '-100px');
        }
    });


    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";

    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });




// var carouselA = document.getElementById('carouselA')
// var carouselB = document.getElementById('carouselB')

// if(carouselA != null)
// {
// carouselA.addEventListener('slide.bs.carousel', function(e) {
//     var bsCarouselB = bootstrap.Carousel.getInstance(carouselB)
//     bsCarouselB.to(e.to)

// 	var id = parseInt($(e.relatedTarget).attr("data-slide-number"));

// 	var thumbNum = parseInt(
// 		$("[id=carousel-selector-" + id + "]")
// 			.parent()
// 			.attr("data-slide-number")
// 	);
// 	$("[id^=carousel-selector-]").removeClass("selected");
// 	$("[id=carousel-selector-" + id + "]").addClass("selected");
// 	$("#carouselB").carousel(thumbNum);
// })
// }

// handles the carousel thumbnails

$(document).on("click", ".thumb", function (ev) {

    $('#carouselB').find('.thumb').removeClass('selected');
    if($(this).addClass('selected').closest('div').length > 0)
    {
        var selectorIdx = $(this).addClass('selected').closest('div').index();
        $('#carouselB').find('.thumb').removeClass('selected')
            .eq(selectorIdx).addClass('selected');

        $('#carouselA').carousel(selectorIdx);
    }
});

$('.carousel-inner.thumb-carousel').each(function() {
    if ($(this).children('div').length <= 4)
    {
        $('.prev-part').hide();
        $('.next-part').hide();
        $(this).parent().parent().css("background-color","transparent");
    }
    else{
        $(this).parent().parent().css("background-color","rgb(240, 240, 240)");
    }
});

//code
let items = document.querySelectorAll('#carouselB .carousel-item')

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

// const { BootstrapLightbox } = BootstrapLightboxModule;
// if($("a.carouselAToggle").length > 0)
// {
//     document.addEventListener('DOMContentLoaded', () => {
//         const bootstrapLightboxA = new BootstrapLightbox('.carouselAToggle', {
//             name: 'carouselAGallery',
//             data: slideShowUrlsA,
//             drag: true
//         });
//         bootstrapLightboxA.createGallery();
//     });
// }

$(document).ready(function(){
  // Add background color dynamically to the image wrapper link in the center aligned images.
  let cs_bg_image_color = $('body').data('cs-image-bg');

  if(cs_bg_image_color){
    $('body.cs-center-align-images .card-body a.image-wrapper-link').css('background', cs_bg_image_color);
}
})


})(jQuery);



