(function($) {
  var showOlderAnswers = false;
  $(document).ready(function() {
    $("div.question-wrapper>h2").click(function(event) {
      if ($(this).hasClass("clicked")) {
        $(this)
          .nextAll()
          .remove();
        $(this).removeClass("clicked");
        return;
      } else {
        $(this).addClass("clicked");
      }
      var question_div = $(this).closest("div.question-wrapper");
      var questionId = question_div.data("id");
      var userId = question_div.data("user-id");

      var data = {
        user_id: userId,
        question_id: questionId
      };

      console.log(data);
      console.log(config);

      jQuery.ajax({
        url: config.ajaxURL,
        type: "post",
        data: {
          action: config.ajaxActionURL,
          nonce: config.ajaxNonce,
          user_id: userId,
          question_id: questionId
        },
        success: function(response) {
          var resObj = JSON.parse(response);
          console.log(resObj);

          if (resObj.status === "success") {
            for (var i = 0; i < resObj.answers.length; i++) {
              var className = i == 0 ? "first" : "";
              // var appendString='';
              if (resObj.answers.length >= 2 && i == 0) {
                appendString = `<p class='q-response ${className}'> ${resObj.answers[i].answer} <button style='display:block;' class='show-previous-answers'>Show previous answers</button></p>`;
              } else if (resObj.answers.length === 1 && i == 0) {
                appendString = `<p class='q-response ${className}'> ${resObj.answers[i].answer}</p>`;
              } else {
                appendString = `<p class='q-response ${className}' style='display:none;'> ${resObj.answers[i].answer}</p>`;
              }

              question_div.append(appendString);
            }
          } else {
            alert(
              "There is a problem with the server.Please contact the administrator"
            );
          }
        },
        error: function(data) {
          console.log(data);

          alert("Error(s) received form server,View log console");
        }
      });
    });
  });

  $(document).on("click", "button.show-previous-answers", function(event) {
    event.stopPropagation();

    if (showOlderAnswers === false) {
      showOlderAnswers = true;
      btnText = "Hide previous answers";
    } else {
      showOlderAnswers = false;
      btnText = "Show previous answers";
    }
    $(this).text(btnText);

    $(this)
      .parent()
      .nextAll()
      .toggle();
  });
})(jQuery);
