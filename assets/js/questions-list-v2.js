(function($) {
  $(document).ready(function() {
    $("div.show-all-btn").click(function(event) {
      if ($(this).hasClass("clicked")) {
        $(this)
          .nextAll()
          .hide();
        $(this).removeClass("clicked");
        $(this).html("<span>+</span> Show all");
      } else {
        $(this)
          .nextAll()
          .show();
        $(this).addClass("clicked");
        $(this).html("<span>-</span> Hide all");
      }
    });
  });
})(jQuery);
