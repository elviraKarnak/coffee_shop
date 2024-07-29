jQuery(document).ready(function($){
  jQuery(".btnToggle").click(function (e) {
    e.preventDefault(0);
    jQuery(".mobile_menu_wrap").toggleClass("dropNav");
    jQuery(this).toggleClass("active")
  });

  jQuery(window).scroll(function () {
    if (jQuery(this).scrollTop() > 50) {
      jQuery(".site_header").addClass("fixedHeader");
    } else {
      jQuery(".site_header").removeClass("fixedHeader");
    }
  });
  
});
