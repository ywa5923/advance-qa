(function($) {
  $(document).ready(function() {
    var selectedAnswerType = $("select._answer_type_key")
      .find(":selected")
      .val();

    $("select._answer_type_key").change(function() {
      var selectedValue = $(this)
        .children("option:selected")
        .val();
      if (selectedValue === "last") {
        // $("#qa-range-number").val(10000);
        $(".range-wrapper").hide();
        $("div._last_answer_key").show();
      } else {
        $("div._last_answer_key").hide();
        //if the selected answer was "last" at page request moment, reset the input range number from 1000 to 1
        if (selectedAnswerType === "last") {
          $("#qa-range-number").val(1);
        }
        $(".range-wrapper").show();
      }
    });
  });
})(jQuery);
