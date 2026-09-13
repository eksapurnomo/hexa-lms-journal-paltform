function setThemeMode(name) {
    const appContent = document.getElementById("appContent");
    appContent.classList.remove("app-theme-white");
    appContent.classList.remove("app-theme-dark");
    appContent.classList.remove("app-theme-gradien");
    appContent.classList.add(name);
    localStorage.setItem("themeMode", name);
}
function toggleFooter() {
    var fixedFooter = localStorage.getItem("fixedFooter");
    if (fixedFooter) {
        localStorage.removeItem("fixedFooter");
    } else {
        localStorage.setItem("fixedFooter", "fixed-footer");
    }
}
function getThemeMode() {
    const appContent = document.getElementById("appContent");
    appContent.classList.remove("app-theme-white");
    appContent.classList.remove("app-theme-dark");
    appContent.classList.remove("app-theme-gradien");
    var selectedTheme = localStorage.getItem("themeMode");
    if (!selectedTheme) {
        appContent.classList.add("app-theme-white");
    } else {
        appContent.classList.add(selectedTheme);
    }
    // if (selectedTheme == "app-theme-white" || !selectedTheme) {
    //     document.getElementById("defoltChangeMode").classList.add("active");
    // }
    if (selectedTheme == "app-theme-dark") {
        document.getElementById("themeDark").classList.add("active");
    }
    var fixedFooter = localStorage.getItem("fixedFooter");
    if (fixedFooter) {
        appContent.classList.add(fixedFooter);
        document.getElementById("footerCheck").checked = true;
    }
    const colorScheme = localStorage.getItem("colorScheme");
    if (colorScheme) {
        document
            .querySelector(".app-header")
            .classList.add(colorScheme, "header-text-light");
        document
            .querySelector(".app-sidebar")
            .classList.add(colorScheme, "sidebar-text-light");
        document.getElementById(colorScheme).classList.add("active");
    }
}
getThemeMode();

$(".change-mode").click(function () {
    $(".change-mode").removeClass("active");
    $(this).addClass("active");
});

$("#resetModeBtn").click(function () {
    $("#appContent").removeClass("app-theme-white");
    $("#appContent").removeClass("app-theme-dark");
    $("#appContent").removeClass("app-theme-gradien");
    $("#appContent").addClass("app-theme-white");
});

function storeColorScheme(colorScheme) {
    localStorage.setItem("colorScheme", colorScheme);
}
$(".theme-color-holder").click(function () {
    $(".theme-color-holder").removeClass("active");
    $(this).addClass("active");
});

$("#resetColor").click(function () {
    localStorage.removeItem("colorScheme");
    $(".theme-color-holder").removeClass("active");
    $("#colorDefault").addClass("activeColor");
});

$("#deleteTable").click(function () {
    $("#deleteTableItem").remove();
});

function MixinExample() {
    Toast.fire({
        icon: "success",
        title: "Saved Successfully!",
    });
}
//  Tooltip
const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

// Save-Button
$(".saveBtn").click(function () {
    Toast.fire({
        icon: "success",
        title: "Saved Successfully",
    });
});

function showSuccessMessage(message) {
    Toast.fire({
        icon: "success",
        title: message,
    });
}

async function copy(id) {
    try {
        const text = document.getElementById(id).value;
        await navigator.clipboard.writeText(text);
        toastr["success"]("Copied to clipboard");
    } catch (err) {
        console.error("Failed to copy: ", err);
    }
}

let table = new DataTable("#dataTable", {
    responsive: true,
    lengthMenu: [
        [15, 25, 50, 100],
        [15, 25, 50, 100],
    ],
});
// Password Hide And Show Js

function showHidePassword() {
    const toggle = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    // toggle the type attribute
    const type =
        password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    // toggle the icon
    toggle.classList.toggle("fa-eye");
}
// Change Password Js
function showHidePassword(id) {
    const toggle = document.getElementById("togglePassword" + id);
    const password = document.getElementById("password" + id);

    // toggle the type attribute
    const type =
        password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    // toggle the icon
    toggle.classList.toggle("fa-eye-slash");
}
// // Tab alert
function deleteContent(id) {
    const content = document.getElementById(id);
    content.style.display = "none";

    Toast.fire({
        icon: "success",
        title: "Deleted Successfully",
    });
}

// Sweet Alert Coll Code
function basic() {
    Swal.fire("Any fool can use a computer");
}
function titleText() {
    Swal.fire("The Internet?", "That thing is still around?", "question");
}
function footertitle() {
    Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Something went wrong!",
        footer: '<a href="">Why do I have this issue?</a>',
    });
}
function longContent() {
    Swal.fire({
        imageUrl: "https://placeholder.pics/svg/300x1500",
        imageHeight: 1500,
        imageAlt: "A tall image",
    });
}
function MixinExample() {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    Toast.fire({
        icon: "success",
        title: "Signed in successfully",
    });
}
function customButton() {
    Swal.fire({
        title: "<strong>HTML <u>example</u></strong>",
        icon: "info",
        html:
            "You can use <b>bold text</b>, " +
            '<a href="//sweetalert2.github.io">links</a> ' +
            "and other HTML tags",
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: '<i class="fa fa-thumbs-up"></i> Great!',
        confirmButtonAriaLabel: "Thumbs up, great!",
        cancelButtonText: '<i class="fa fa-thumbs-down"></i>',
        cancelButtonAriaLabel: "Thumbs down",
    });
}
function threeButton() {
    Swal.fire({
        title: "Do you want to save the changes?",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Save",
        denyButtonText: `Don't save`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            Swal.fire("Saved!", "", "success");
        } else if (result.isDenied) {
            Swal.fire("Changes are not saved", "", "info");
        }
    });
}
function positioned() {
    Swal.fire({
        position: "top-end",
        icon: "success",
        title: "Your work has been saved",
        showConfirmButton: false,
        timer: 1500,
    });
}
function animation() {
    Swal.fire({
        title: "Custom animation with Animate.css",
        showClass: {
            popup: "animate-animated animate-fadeInDown",
        },
        hideClass: {
            popup: "animate-animated animate-fadeOutUp",
        },
    });
}
function ConfirmBtn() {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire("Deleted!", "Your file has been deleted.", "success");
        }
    });
}
function somethingEls() {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger",
        },
        buttonsStyling: false,
    });

    swalWithBootstrapButtons
        .fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true,
        })
        .then((result) => {
            if (result.isConfirmed) {
                swalWithBootstrapButtons.fire(
                    "Deleted!",
                    "Your file has been deleted.",
                    "success"
                );
            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                    "Cancelled",
                    "Your imaginary file is safe :)",
                    "error"
                );
            }
        });
}
function customImg() {
    Swal.fire({
        title: "Sweet!",
        text: "Modal with a custom image.",
        imageUrl: "https://unsplash.it/400/200",
        imageWidth: 400,
        imageHeight: 200,
        imageAlt: "Custom image",
    });
}
function backgroundAni() {
    Swal.fire({
        title: "Custom width, padding, color, background.",
        width: 600,
        padding: "3em",
        color: "#716add",
        background: "#fff url(/images/trees.png)",
        backdrop: `
rgba(0,0,123,0.4)
url("/images/nyan-cat.gif")
left top
no-repeat
`,
    });
}
function autoClose() {
    let timerInterval;
    Swal.fire({
        title: "Auto close alert!",
        html: "I will close in <b></b> milliseconds.",
        timer: 2000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            const b = Swal.getHtmlContainer().querySelector("b");
            timerInterval = setInterval(() => {
                b.textContent = Swal.getTimerLeft();
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        },
    }).then((result) => {
        /* Read more about handling dismissals below */
        if (result.dismiss === Swal.DismissReason.timer) {
            console.log("I was closed by the timer");
        }
    });
}
function RTL() {
    Swal.fire({
        title: "هل تريد الاستمرار؟",
        icon: "question",
        iconHtml: "؟",
        confirmButtonText: "نعم",
        cancelButtonText: "لا",
        showCancelButton: true,
        showCloseButton: true,
    });
}
function requestExampul() {
    Swal.fire({
        title: "Submit your Github username",
        input: "text",
        inputAttributes: {
            autocapitalize: "off",
        },
        showCancelButton: true,
        confirmButtonText: "Look up",
        showLoaderOnConfirm: true,
        preConfirm: (login) => {
            return fetch(`//api.github.com/users/${login}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }
                    return response.json();
                })
                .catch((error) => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: `${result.value.login}'s avatar`,
                imageUrl: result.value.avatar_url,
            });
        }
    });
}

// Scroll Bar Sweet Alert
function saveMassege() {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    Toast.fire({
        icon: "success",
        title: "Saved Successfully",
    });
}

$("#deleteButton").click(function () {
    $("#scrollBox").remove();
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });
    Toast.fire({
        icon: "success",
        title: "Deleted Successfully",
    });
});
