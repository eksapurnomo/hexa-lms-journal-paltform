$(function () {
  toastr.options.onclick = function (e) {
    alert(this.data.Message);
  };

  $("#success").click(function () {
    toastr.options = {
      positionClass: "toast-top-left",
      progressBar: true,
    };
    toastr.success("Lorem Ipsum is simply dummy text of industry.", "Default");
  });
  $("#info").click(function () {
    toastr.options = {
      positionClass: "toast-bottom-left",
      progressBar: true,
    };
    toastr.info("Lorem Ipsum is simply dummy text of industry.", "Info");
  });
  $("#warning").click(function () {
    toastr["warning"]("Please Flow This Warning ", "Warning");
    toastr.options = {
      positionClass: "toast-bottom-right",
      preventDuplicates: false,
    };
  });
  $("#danger").click(function () {
    toastr["error"]("Please Flow This Warning ", "Warning");

    toastr.options = {
      positionClass: "toast-top-left",
      preventDuplicates: false,
    };
  });
  $("#success2").click(function () {
    Command: toastr["success"](
      "Hello Sir Your Massege Is Successfull",
      "Successfull"
    );
    toastr.options = {
      positionClass: "toast-top-full-width",
      preventDuplicates: false,
    };
  });
  $("#success3").click(function () {
    toastr["success"]("Hello Sir Your Massege Is Successfull", "Successfull");

    toastr.options = {
      positionClass: "toast-top-center",
      preventDuplicates: false,
    };
  });

  $("#info2").click(function () {
    toastr["info"]("Hello Sir Your Massege Is Successfull", "Successfull");

    toastr.options = {
      positionClass: "toast-bottom-center",
      preventDuplicates: false,
    };
  });
  $("#warning2").click(function () {
    toastr["warning"]("Hello Sir Your Massege Is Successfull", "Successfull");
    toastr.options = {
      positionClass: "toast-bottom-full-width",
      preventDuplicates: false,
    };
  });
  $("#danger2").click(function () {
    toastr["error"]("Hello Sir Your Massege Is Successfull", "Successfull");
    toastr.options = {
      positionClass: "toast-bottom-left",
      preventDuplicates: false,
    };
  });
  $("#success4").click(function () {
    toastr["success"]("Hello Sir Your Massege Is Successfull", "Successfull");
    toastr.options = {
      positionClass: "toast-bottom-center",
      preventDuplicates: false,
    };
  });
});
