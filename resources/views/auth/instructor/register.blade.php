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
    <!-- Bootstrap-Min-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Font-Awesome--Min-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <!--Bootstrap-Icon-Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <!--Style--Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!--Responsive--Css-Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">


    <style>
        #togglePassword {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        #toggleConfirmPassword {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        #loading {
            height: 100vh;
            width: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            background: hsl(220deg 29% 90% / 50%);
        }

        .pl {
            width: 6em;
            height: 6em;
        }

        .pl__ring {
            animation: ringA 2s linear infinite;
        }

        .pl__ring--a {
            stroke: #f42f25;
        }

        .pl__ring--b {
            animation-name: ringB;
            stroke: #f49725;
        }

        .pl__ring--c {
            animation-name: ringC;
            stroke: #255ff4;
        }

        .pl__ring--d {
            animation-name: ringD;
            stroke: #f42582;
        }

        /* Animations */
        @keyframes ringA {

            from,
            4% {
                stroke-dasharray: 0 660;
                stroke-width: 20;
                stroke-dashoffset: -330;
            }

            12% {
                stroke-dasharray: 60 600;
                stroke-width: 30;
                stroke-dashoffset: -335;
            }

            32% {
                stroke-dasharray: 60 600;
                stroke-width: 30;
                stroke-dashoffset: -595;
            }

            40%,
            54% {
                stroke-dasharray: 0 660;
                stroke-width: 20;
                stroke-dashoffset: -660;
            }

            62% {
                stroke-dasharray: 60 600;
                stroke-width: 30;
                stroke-dashoffset: -665;
            }

            82% {
                stroke-dasharray: 60 600;
                stroke-width: 30;
                stroke-dashoffset: -925;
            }

            90%,
            to {
                stroke-dasharray: 0 660;
                stroke-width: 20;
                stroke-dashoffset: -990;
            }
        }

        @keyframes ringB {

            from,
            12% {
                stroke-dasharray: 0 220;
                stroke-width: 20;
                stroke-dashoffset: -110;
            }

            20% {
                stroke-dasharray: 20 200;
                stroke-width: 30;
                stroke-dashoffset: -115;
            }

            40% {
                stroke-dasharray: 20 200;
                stroke-width: 30;
                stroke-dashoffset: -195;
            }

            48%,
            62% {
                stroke-dasharray: 0 220;
                stroke-width: 20;
                stroke-dashoffset: -220;
            }

            70% {
                stroke-dasharray: 20 200;
                stroke-width: 30;
                stroke-dashoffset: -225;
            }

            90% {
                stroke-dasharray: 20 200;
                stroke-width: 30;
                stroke-dashoffset: -305;
            }

            98%,
            to {
                stroke-dasharray: 0 220;
                stroke-width: 20;
                stroke-dashoffset: -330;
            }
        }

        @keyframes ringC {
            from {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: 0;
            }

            8% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -5;
            }

            28% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -175;
            }

            36%,
            58% {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: -220;
            }

            66% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -225;
            }

            86% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -395;
            }

            94%,
            to {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: -440;
            }
        }

        @keyframes ringD {

            from,
            8% {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: 0;
            }

            16% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -5;
            }

            36% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -175;
            }

            44%,
            50% {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: -220;
            }

            58% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -225;
            }

            78% {
                stroke-dasharray: 40 400;
                stroke-width: 30;
                stroke-dashoffset: -395;
            }

            86%,
            to {
                stroke-dasharray: 0 440;
                stroke-width: 20;
                stroke-dashoffset: -440;
            }
        }
    </style>


</head>

<body>

    {{-- loadder --}}

    <div id="loading">
        <svg class="pl" width="240" height="240" viewBox="0 0 240 240">
            <circle class="pl__ring pl__ring--a" cx="120" cy="120" r="105" fill="none" stroke="#000"
                stroke-width="20" stroke-dasharray="0 660" stroke-dashoffset="-330" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--b" cx="120" cy="120" r="35" fill="none" stroke="#000"
                stroke-width="20" stroke-dasharray="0 220" stroke-dashoffset="-110" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--c" cx="85" cy="120" r="70" fill="none" stroke="#000"
                stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
            <circle class="pl__ring pl__ring--d" cx="155" cy="120" r="70" fill="none" stroke="#000"
                stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
        </svg>
    </div>
    {{-- loadder --}}



    <div class="d-flex vh-100" style="background: #FAF6FE;">
        <div class="container mx-auto my-auto">
            <div class="main-card card h-100 d-flex flex-column overflow-hidden shadow rounded-4">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="px-4 py-5 m-0">
                            <div class="text-center mb-5 rounded-4" style="background: #F9FBFF ; padding: 16px 0px;">
                                @if ($app_setting['logo'])
                                    <img src="{{ $app_setting['logo'] }}" alt="" width="200">
                                @else
                                    <img src="{{ asset('assets/images/auth/logo.png') }}" alt="" width="200">
                                @endif
                            </div>
                            <form action="{{ route('instructor.authenticate') }}" method="POST">
                                @csrf
                                <!-- Name input -->
                                <div class="form-outline mb-2">
                                    <label class="form-label">{{ __('Full Name') }}</label>
                                    <input type="text" id="email" name="name" class="form-control rounded-4"
                                        placeholder="{{ __('Enter your full name') }}" value="{{ old('name') }}">
                                    @error('name')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email input -->
                                <div class="form-outline mb-2">
                                    <label class="form-label">{{ __('Email address') }}</label>
                                    <input type="email" id="email" name="email" class="form-control rounded-4"
                                        placeholder="example@domain.com" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone input -->
                                <div class="form-outline mb-2">
                                    <label class="form-label">{{  __('Phone') }}</label>
                                    <input type="tel" id="email" name="phone"
                                        class="form-control rounded-4" placeholder="+100 0000 0000"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label">{{ __('Password') }}</label>
                                    <div class="position-relative">
                                        <input type="password" id="passwordInput" name="password"
                                            class="form-control rounded-4" placeholder="{{ __('Enter your password') }}">
                                        <i class="fa-solid fa-eye-slash" id="togglePassword"
                                            onclick="myPasswordView()"></i>
                                    </div>
                                    @error('password')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label">{{ __('Confirm Password') }}</label>
                                    <div class="position-relative">
                                        <input type="password" id="confirmPasswordInput" name="password_confirmation"
                                            class="form-control rounded-4" placeholder="{{ __('Confirm your password') }}">
                                        <i class="fa-solid fa-eye-slash" id="toggleConfirmPassword"
                                            onclick="myConfirmPasswordView()"></i>
                                    </div>
                                </div>

                                {{-- terms & conditions --}}
                                <div class="form-outline mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value=""
                                            id="flexCheckDefault" required>
                                        <label class="form-check-label" for="flexCheckDefault"
                                            style="cursor: pointer">
                                            {{ __('I accept and agree to the') }}
                                             <a href="/page/terms_and_conditions" target="_blank">
                                                <span class="text-primary fw-bold">
                                                    {{ __('Terms & Condition') }}
                                                </span></a> &
                                            <a href="/page/privacy_policy" target="_blank">
                                                <span class="text-primary fw-bold">
                                                    {{ __('Privacy Policy') }}
                                                </span></a> {{ __('of') }} <a href="/" target="_blank"><span
                                                    class="text-primary fw-bold">{{ config('app.name') }}</span></a>
                                            </a>
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit button -->
                                <div class="text-center"></div>
                                <button type="submit" class="btn btn-primary rounded-4 fw-bold py-3 mb-3 w-100"
                                    onclick="loadder()">{{  __('Register') }}</button>
                            </form>

                            <div class="row align-items-center">
                                <div class="col-12">
                                    <p class="m-0 fw-bold text-dark">{{ __('Already have an account') }}? <a
                                            href="{{ route('admin.login') }}" class="text-primary"
                                            style="text-decoration: underline !important">{{ __('Log in') }}</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <img src="{{ asset('assets/images/auth/register2.png') }}" alt="auth-login"
                            class="h-100 w-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function myPasswordView() {
            var icon = document.getElementById("togglePassword");
            var x = document.getElementById("passwordInput");
            if (x.type === "password") {
                x.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                x.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function myConfirmPasswordView() {
            var icon = document.getElementById("toggleConfirmPassword");
            var x = document.getElementById("confirmPasswordInput");
            if (x.type === "password") {
                x.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                x.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function loadder() {
            document.getElementById('loading').style.display = 'flex';
            document.getElementById('loading').style.display = 'flex';
            document.getElementById('loading').style.position = 'fixed';
            document.getElementById('loading').style.top = '0';
            document.getElementById('loading').style.left = '0';
            document.getElementById('loading').style.width = '100%';
            document.getElementById('loading').style.height = '100%';
            document.getElementById('loading').style.background = 'rgba(255, 255, 255, 0.7)';
            document.getElementById('loading').style.zIndex = '9999';
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none'
            }, 5000);
        }
    </script>

</body>

</html>
