$('.slider').slick({
    infinite: true,
    dots: true,
    autoplay: true,
    autoplaySpeed: 3000,
    fade: true,
    arrows: true,
    fadeSpeed: 1000
});

$('.recommendslider').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    infinite: true,
    autoplay: true,
    speed: 1000,
    autoplaySpeed: 2000,
    responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
            },
        },

    ],
});
$(".top").click(function() {
    $("body,html").animate({
        scrollTop: 0
    }, 1000);

    return false;
});

$(".novel a").on('click', function(event) {
    console.log("click00");
    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
        // Prevent default anchor click behavior
        event.preventDefault();

        // Store hash
        var hash = this.hash;

        // Using jQuery's animate() method to add smooth page scroll
        // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
        $('html, body').animate({
            scrollTop: $(hash).offset().top
        }, 800, function() {

            // Add hash (#) to URL when done scrolling (default click behavior)
            window.location.hash = hash;
        });
    } // End if
});

$(".menu-trigger").on("click", function() {
    $(this).toggleClass("active");
    $(".g-nav").toggleClass("active");
});
$(".g-nav ul li a").on("click", function() {
    $(".menu-trigger").toggleClass("active");
    $(".g-nav").toggleClass("active");
});

$(window).scroll(function() {
    if ($(window).scrollTop() > 10) {
        $("header .header").css("background", "#333");
    } else {
        $("header .header").css("background", "transparent");

    }
})