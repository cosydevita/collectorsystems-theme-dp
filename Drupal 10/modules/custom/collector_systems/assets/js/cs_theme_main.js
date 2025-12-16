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

    
    $(document).ready(function(){
        // apply customizations on document ready
        applyCsCustomizations();
    })

    $( document ).ajaxComplete(function() {
        // also call on ajax complete to handle dynamic content loading
        applyCsCustomizations();
    });

    function applyCsCustomizations() {

        // Add background color dynamically to the image wrapper link in the center aligned images.
        let cs_bg_image_color = $('body').data('cs-image-bg');

        if(cs_bg_image_color){
            $('body.cs-center-align-images .card-body a.image-wrapper-link').css('background', cs_bg_image_color);
        }

        // Apply body font size customization
        let cs_body_font_size = $('body').data('cs-body-font-size');
        if(cs_body_font_size){
            // Apply font size to gallery block cards
            $("body .collector-systems-wrapper  #gallery-block .card *").css("font-size", cs_body_font_size);
            
            // Apply font size to Artists cards
            $("body .collector-systems-wrapper  .artists-container .card *").css("font-size", cs_body_font_size);

            // Apply font size to collections cards
            $("body .collector-systems-wrapper  .collections-container .card *").css("font-size", cs_body_font_size);

            // Apply font size to exhibitions cards
            $("body .collector-systems-wrapper  .exhibitions-container .card *").css("font-size", cs_body_font_size);

            // Apply font size to Groups cards
            $("body .collector-systems-wrapper  .groups-container .card *").css("font-size", cs_body_font_size);
        }

        // Apply object detail page title font size customization
        let cs_object_detail_page_title_font_size = $('body').data('cs-object-detail-page-title-font-size');
        if(cs_object_detail_page_title_font_size){
            // Apply font size to Object Detail Page Title
            $(".collector-systems-wrapper .cs-object-details > p:first-child").css("font-size", cs_object_detail_page_title_font_size);
        }

    }


})(jQuery);



