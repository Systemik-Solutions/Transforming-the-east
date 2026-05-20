// parent dropdown menu
jQuery(function($){
    var isMobile = window.matchMedia("only screen and (max-width: 768px)").matches;

    $('.navbar-nav .dropdown-toggle').each(function() {
    	// desktop view - remove data-toggle which is for tablet-mobile view only
        if (!isMobile) {
        	$(this)[0].removeAttribute('data-toggle');
        }
    })
    .click(function() {
        // tablet-mobile need to click to expand and click again to navigate
        if (isMobile && $(this).next().is(':visible')) {
            location.href = $(this).attr('href');
        }
    });
});