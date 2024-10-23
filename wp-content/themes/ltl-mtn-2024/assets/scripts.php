<script>

	jQuery( '.menu-toggle' ).on( 'click', function() {
			var element = document.getElementById("body");
			element.classList.toggle("menu-active");
	});

	// Child Menus Toggle
	jQuery( '.menu-item-has-children' ).on( 'click', function() {
			jQuery( this ).toggleClass( 'active' );
	});


  jQuery(document).ready(function() {
    jQuery(window).scroll(function() {
      // Get the current scroll position
      var scrollPosition = jQuery(window).scrollTop();

      // Check if the user has scrolled 400px past the top
      if (scrollPosition >= 200) {
        // Add the "scrolled" class to the #body element
        jQuery('#body').addClass('scrolled');
      } else {
        // Remove the "scrolled" class if not scrolled 400px past the top
        jQuery('#body').removeClass('scrolled');
      }
    });
  });

  jQuery(window).scroll(function() {
    jQuery('.scroll2me').each(function() {
      var elementTop = jQuery(this).offset().top;
      var elementBottom = elementTop + jQuery(this).outerHeight();
      var viewportTop = jQuery(window).scrollTop();
      var viewportBottom = viewportTop + jQuery(window).height();

      if (elementTop <= viewportTop && elementBottom >= viewportTop) {
        jQuery(this).addClass('active');
      } else {
        jQuery(this).removeClass('active');
      }
    });
  });

  // Add ".active" to the classlist of a .scroll2me element if it is the page target
  jQuery(document).ready(function() {
    var target = window.location.hash;
    if (target) {
      var targetElement = jQuery(target);
      if (targetElement.hasClass('scroll2me')) {
        jQuery('.scroll2me').removeClass('active');
        targetElement.addClass('active');
      }
    }
  });

</script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>