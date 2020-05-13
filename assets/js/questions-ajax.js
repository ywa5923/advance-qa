(function($) {
  $(document).ready(function(ev) {
    //add datepicker

    if (document.getElementById("ywaaq_datepicker") !== null) {
      $("#ywaaq_datepicker").datepicker();
    }

    if (document.getElementById("qa-range") !== null) {
      $(".ywa-js-range-slider").ionRangeSlider({
        skin: "round",
        values: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        grid: true,
        onChange: function(data) {
          console.log(data.from_value);
          $("#qa-range").val(data.from_value);
        }
      });
    }

    $(".yw-next-question a").on("click", function(e) {
      e.preventDefault();
      // alert(config.ajaxURL);
      var nextPage = $(this).attr("href");
      var answerType = $(".qa-answer").data("type");
      var userId = $("input.qa-user-id").val();
      var questionId = $("input.qa-question-id").val();

      var answerValue;

      switch (answerType) {
        case "calendar":
        case "range":
        case "textarea":
          answerValue = $(".qa-answer").val();
      }

      var data = {
        user_id: userId,
        question_id: questionId,
        answer: answerValue
      };

      $.post(config.ajaxURL, {
        action: "ywa_save_qresponse",
        nonce: config.ajaxNonce,
        data: data
      }).done(function(response) {
        var resObj = JSON.parse(response);
        if (resObj.status === "success") {
          window.location.href = nextPage;
        } else {
          alert(
            "There is a problem with the server.Please contact the administrator"
          );
        }
      });
    });
  });
})(jQuery);

/*(function() {
  "use strict";

  var init = function() {
    var slider2 = new rSlider({
      target: "#range-number",
      values: [1, 2, 3, 4, 5],
      range: false,
      set: [0],
      tooltip: true,
      step: 1,
      onChange: function(vals) {
        console.log(vals);
      }
    });
  };
  window.onload = init;
})();*/
