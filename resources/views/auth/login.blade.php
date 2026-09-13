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


        .swal2-html-container {
            font-size: 0.95rem !important;
            line-height: 28px !important;
        }

        .version-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #5864ff;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .powerBy {
            position: absolute;
            bottom: 10px;
            left: 10px;
            color: #5864ff;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 20px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="d-flex vh-100">
        @if (config('app.env') == 'local')
            <div class="powerBy">Powered by RazinSoft ©{{ now()->format('Y') }}</div>
            <div class="version-badge">v2.0.0</div>
        @endif
        <div class="container mx-auto my-auto">
            <div class="main-card card h-100 d-flex flex-column overflow-hidden shadow rounded-4">
                <div class="row">
                    <div class="col-lg-6 my-auto ">
                        <div class="card-body">
                            <div class="text-center mb-5 rounded-4" style="background: #F9FBFF ; padding: 16px 0px;">
                                @if ($app_setting['logo'])
                                    <img src="{{ $app_setting['logo'] }}" alt="" width="200">
                                @else
                                    <img src="{{ asset('assets/images/auth/logo.png') }}" alt="" width="200">
                                @endif
                            </div>
                            <form action="{{ route('admin.authenticate') }}" method="POST">
                                @csrf
                                <!-- Email input -->
                                <div class="form-outline mb-2">
                                    <label class="form-label">{{ __('Email address') }}</label>
                                    <input type="email" id="email" name="email" class="form-control rounded-4"
                                        placeholder="example@domain.com" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password input -->
                                <div class="form-outline mb-4">
                                    <label class="form-label">{{ __('Password') }}</label>
                                    <div class="position-relative">
                                        <input type="password" id="passwordInput" name="password"
                                            class="form-control rounded-4"
                                            placeholder="{{ __('Enter your password') }}">
                                        <i class="fa-solid fa-eye-slash" id="togglePassword"
                                            onclick="myPasswordView()"></i>
                                    </div>
                                    @error('password')
                                        <p class="text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Submit button -->
                                <div class="text-center"></div>
                                <button type="submit" id="loginBtn"
                                    class="btn btn-primary rounded-4 fw-bold py-3 mb-3 w-100">{{ __('Login') }}</button>
                            </form>

                            <div class="row mb-5 align-items-center">
                                <div class="col-6">
                                    <p class="m-0 fw-bold text-dark fs-5">{{ __('Are you an Instructor') }}?</p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('instructor.register') }}"
                                        class="btn btn-outline-primary rounded-pill py-2 px-3">
                                        {{ __('Join as an Instructor') }}
                                    </a>
                                </div>
                            </div>

                            @if (config('app.env') == 'local')
                                <div class="test-user d-flex justify-content-between mb-2 p-2 pb-0">
                                    <p class="mb-0 text-start">
                                    <strong>{{ __('Email') }}:</strong> admin@hexalms.local <br>
                                    <strong>{{ __('Password') }}:</strong> HexaLMS-Root-2026!
                                    </p>
                                    <button onclick="email.value = 'admin@hexalms.local'; passwordInput.value = 'HexaLMS-Root-2026!'"
                                        type="button" class="btn btn-sm hite-icon copyBtn" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-title="Copy admin credentials"> <i
                                            class="bi bi-copy"></i>
                                    </button>
                                </div>
                                <div class="test-user d-flex justify-content-between mb-2 p-2 pb-0">
                                    <p class="mb-0 text-start">
                                    <strong>Email:</strong> instructor@hexalms.local <br>
                                    <strong>Password:</strong> secret
                                    </p>
                                    <button onclick="email.value = 'instructor@hexalms.local'; passwordInput.value = 'secret'"
                                        type="button" class="btn btn-sm hite-icon copyBtn" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-title="Copy instructor credentials"> <i
                                            class="bi bi-copy"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <img src="{{ asset('assets/images/auth/login.png') }}" alt="auth-login"
                            class="h-100 w-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    </script>

    @if (session('verification-error'))
        <script>
            Swal.fire({
                icon: "error",
                title: "{{ session('verification-error') }}",
                showConfirmButton: false,
                timer: 3500
            });
        </script>
    @endif

    @if (session('account-created'))
        <script>
            Swal.fire({
                icon: "success",
                title: "{{ session('account-created') }}",
                showConfirmButton: false,
                timer: 3500,
                customClass: {
                    title: "swal-title",
                },
            });
        </script>
    @endif

    @if (session('account-suspended'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Account Has Been Suspended",
                html: "{!! session('account-suspended') !!} ",
                footer: '<a href="{{ url('/contact-us') }}">contact with support team?</a>',
            });
        </script>
    @endif

</body>

</html>
