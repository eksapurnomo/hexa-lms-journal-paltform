<!doctype html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <!-- Meta-Link -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="">
    <meta name="mlapplication-tap-highlight" content="no">
    <!-- Title -->
    <title>@yield('title')</title>
    <!-- FaveIcon-Link -->
    <link rel="shortcut icon" href="{{ $app_setting['favicon'] }}" type="image/x-icon">


    {{-- google fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lexend:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    {{-- date picker --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    {{-- select 2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Bootstrap-Min-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Font-Awesome--Min-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <!--Bootstrap-Icon-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <!--ApexChart-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/apexcharts.css') }}">
    <!--DataTables--Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/datatables.min.css') }}">
    {{-- new style link --}}
    <link rel="stylesheet" href="{{ asset('assets/css/newstyle.css') }}">
    <!--quill--Css-Link -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <!--Style--Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!--Responsive--Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    @stack('styles')
</head>

<body>
    <!-- App-Container-Section -->
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header" id="appContent">

        <!-- storage link -->
        @if ($storageLink)
            <div class="w-100" style="z-index: 99; position: fixed; top: 0;">

                <div class="alert alert-primary alert-dismissible fade show mb-0 w-100 text-center rounded-0 text-black"
                    role="alert" style="padding: 10px">
                    <strong><i class="fa fa-exclamation-circle" data-toggle="tooltip" data-placement="bottom"
                            title='If you can not install storage link, then image not found.'></i>
                        {{ __('Storage link dose not exist or image not found then') }} </strong>
                    {{ __('please run') }} <code class="text-danger">php
                        artisan
                        storage:link</code> {{ __('or') }} <a href="{{ route('link.storage') }}"
                        class="btn btn-sm btn-dark">
                        {{ __('Click Here') }}</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                        id="closeAlert"></button>
                </div>

            </div>
        @endif
        @include('layouts.header')
        <div class="app-main">
            @include('layouts.sidebar')
            @yield('content')
        </div>
        @include('layouts.footer')
    </div>
    <!-- End-App-Container-Section  -->

    <!-- Jquery-link -->
    <script src="{{ asset('assets/scripts/jquery-3.6.3.min.js') }}"></script>
    {{-- select trick --}}
    {{-- date picker --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Main-Script-Js-Link -->
    <script src="{{ asset('assets/scripts/main.js') }}"></script>
    <!-- Bootstrap-Min-Bundil-Link -->
    <script src="{{ asset('assets/scripts/bootstrap.bundle.min.js') }}"></script>
    <!-- Font-Awesome-Min-Js-Link-->
    <script src="{{ asset('assets/scripts/font-awesome.min.js') }}"></script>
    <!-- Full-Screen-Js-Link -->
    <script src="{{ asset('assets/scripts/full-screen.js') }}"></script>
    <!-- Sweet-Alert-link -->
    <script src="{{ asset('assets/scripts/sweetalert2.min.js') }}"></script>
    {{-- apexchart --}}
    <script src="{{ asset('assets/scripts/apexcharts.min.js') }}"></script>
    <!-- DataTables-Js-Link -->
    <script src="{{ asset('assets/scripts/datatables.min.js') }}"></script>
    <!-- quill-Js-Link -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    {{-- sortable js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <!--Script-Js-Link -->
    <script src="{{ asset('assets/scripts/scripts.js') }}"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var themeColor = "#5864ff";

            // Handle menu and downIcon SVGs
            var activeMenuIcons = document.querySelectorAll(".menu.active .menu-icon");
            var downIcons = document.querySelectorAll(".menu.active .downIcon");

            // Change colors for both menu icons and downIcons
            changeSvgImageColor(activeMenuIcons, themeColor);
            changeSvgImageColor(downIcons, themeColor);
        });

        function changeSvgImageColor(svgImages, svgColor, defaultColor = "#25314C") {
            svgImages.forEach(function(svgImage) {
                var svgPath = svgImage.getAttribute("src");
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var svgContent = xhr.responseText;

                        const strokeRegex = new RegExp(`stroke="${defaultColor}"`, "g");
                        const fillRegex = new RegExp(`fill="${defaultColor}"`, "g");

                        svgContent = svgContent.replace(strokeRegex, `stroke="${svgColor}"`);
                        svgContent = svgContent.replace(fillRegex, `fill="${svgColor}"`);

                        svgImage.src = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svgContent);
                    }
                };
                xhr.open("GET", svgPath, true);
                xhr.send();
            });
        }
    </script>


    {{-- Sweet Alert --}}
    <script>
        function deleteAction(deleteUrl) {
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('The instence and its realted data will be deleted!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.replace(deleteUrl);
                }
            });
        }
    </script>

    <script>
        function sureAction(submitUrl) {
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('Please confirm that you understand this course will be offered free of charge') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Confirm!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.replace(submitUrl);
                }
            });
        }
    </script>
    @if (session('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "success",
                title: "{{ session('success') }}"
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon: "error",
                title: "{{ session('error') }}"
            });
        </script>
    @endif

    <script>
        $('#dataTable').DataTable().destroy();
        $('#dataTable').DataTable({
            language: {
                paginate: {
                    previous: '<i class="bi bi-arrow-left-circle"></i>',
                    next: '<i class="bi bi-arrow-right-circle"></i>', // Custom icon for "Next"
                }
            },
            responsive: true,
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>

    @stack('scripts')

</body>

</html>
