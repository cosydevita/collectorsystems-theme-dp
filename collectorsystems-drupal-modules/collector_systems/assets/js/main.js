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


    // Header carousel
    $(".header-carousel").owlCarousel({
        autoplay: false,
        smartSpeed: 1500,
        items: 1,
        dots: false,
        loop: false,
        nav : false,
        mouseDrag: false,
        touchDrag: false,
        navText : [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        center: true,
        margin: 24,
        dots: true,
        loop: true,
        nav : false,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });



var carouselA = document.getElementById('carouselA')
var carouselB = document.getElementById('carouselB')

if(carouselA != null)
{
carouselA.addEventListener('slide.bs.carousel', function(e) {
    var bsCarouselB = bootstrap.Carousel.getInstance(carouselB)
    bsCarouselB.to(e.to)

	var id = parseInt($(e.relatedTarget).attr("data-slide-number"));
    
	var thumbNum = parseInt(
		$("[id=carousel-selector-" + id + "]")
			.parent()
			.attr("data-slide-number")
	);
	$("[id^=carousel-selector-]").removeClass("selected");
	$("[id=carousel-selector-" + id + "]").addClass("selected");
	$("#carouselB").carousel(thumbNum);
})
}

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

//code
let items = document.querySelectorAll('#carouselB .carousel-item')

items.forEach((el) => {
    const minPerSlide = 4
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


// $('#carouselB').on('slide.bs.carousel', function (e) {

  
//     var $e = $(e.relatedTarget);
//     var idx = $e.index();
//     var itemsPerSlide = 4;
//     var totalItems = $('.thumb-carousel.carousel-item').length;
    
//     if (idx >= totalItems-(itemsPerSlide-1)) {
//         var it = itemsPerSlide - (totalItems - idx);
//         for (var i=0; i<it; i++) {
//             // append slides to end
//             if (e.direction=="left") {
//                 $('.thumb-carousel .carousel-item').eq(i).appendTo('.thumb-carousel.carousel-inner');
//             }
//             else {
//                 $('.thumb-carousel .carousel-item').eq(0).appendTo('.thumb-carousel.carousel-inner');
//             }
//         }
//     }
// });
    
    
    
})(jQuery);



