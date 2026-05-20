/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 * ======================================================================== */

(function($) {

  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var Sage = {
    // All pages
    'common': {
      init: function() {
        // JavaScript to be fired on all pages

        // PRELOADER
        $(window).on('load', function() {
            $('.loader').fadeOut();
            $('.loader-mask').delay(350).fadeOut('slow');
        });

        // PRIMARY NAVIGATION
        (function () {
            $('#jsPrimaryMenu').on('show.bs.collapse', function() {
                $('body').addClass('nav-is-visible').append('<div class="modal-backdrop"></div>');
                $('#jsSearchBar').removeClass('show');
            });
            $('#jsPrimaryMenu').on('hide.bs.collapse', function() {
                $('body').removeClass('nav-is-visible');
                $('.modal-backdrop').fadeOut("slow", function() {
                    $(this).remove();
                });
            });
            $('#jsSearchBar').on('show.bs.collapse', function() {
                $('#jsPrimaryMenu').removeClass('show');
            });
        })();


        // SMOOTH HASH SCROLL
        (function () {
            // Select all links with hashes
            $('a[href*="#"]')
              // Remove links that don't actually link to anything
              .not('[href="#"]')
              .not('[href="#0"]')
              .click(function(event) {
                // On-page links
                if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                  // Figure out element to scroll to
                  var target = $(this.hash);
                  target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                  // Does a scroll target exist?
                  if (target.length) {
                    // Only prevent default if animation is actually gonna happen
                    event.preventDefault();
                    $('html, body').animate({
                      scrollTop: target.offset().top
                    }, 500, function() {
                      // Callback after animation
                      // Must change focus!
                      var $target = $(target);
                      $target.focus();
                      if ($target.is(":focus")) { // Checking if the target was focused
                        return false;
                      } else {
                        $target.attr('tabindex','-1').css('outline', '0'); // Adding tabindex for elements not focusable
                        $target.focus(); // Set focus again
                      }
                    });
                  }
                }
              });
        })();

        // INITIALIZE HEADROOM
        (function() {
            var header = document.querySelector(".site-header");
            if(window.location.hash) {
              header.classList.add("headroom--unpinned");
            }
            var headroom = new Headroom(header, {
                tolerance: {
                  down : 0,
                  up : 0
                },
                offset : 100,
                classes: {
                  initial: "animated",
                  pinned: "fadeInDown",
                  unpinned: "fadeOutUp",
                  onUnpin : function() {
                    console.log("unpinned");
                  }
                }
            });
            headroom.init();
        }());

      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    },
    // Home page
    'home': {
      init: function() {
        // JavaScript to be fired on the home page
      },
      finalize: function() {
        // JavaScript to be fired on the home page, after the init JS
      }
    },
    // About us page, note the change from about-us to about_us.
    'about_us': {
      init: function() {
        // JavaScript to be fired on the about us page
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = Sage;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      UTIL.fire('common');

      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
        UTIL.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      UTIL.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.