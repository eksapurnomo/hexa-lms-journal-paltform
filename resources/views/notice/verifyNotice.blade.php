<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Email Not Verified') }}</title>
    <link rel="icon" href="{{ $app_setting['favicon'] }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .header {
            background: #5864ff;
            padding: 20px;
            color: #ffffff;
            border-radius: 10px 10px 0 0;
        }

        .header h3 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            margin: 20px 0;
        }

        .content p {
            font-size: 16px;
            color: #555555;
            line-height: 1.6;
        }

        .content .btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #0056b3;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
        }

        .content .btn:hover {
            background-color: #003d80;
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #777777;
        }

        .footer a {
            color: #0056b3;
            text-decoration: none;
        }

        h3 {
            color: #000000;
        }

        .otp-input {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .otp-input input {
            width: 40px;
            height: 40px;
            margin: 0 5px;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: #252525;
        }

        .otp-input input::-webkit-outer-spin-button,
        .otp-input input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .otp-input input[type=number] {
            -moz-appearance: textfield;
        }

        button {
            background-color: #5864FF;
            color: white;
            border: 1px solid transparent;
            padding: 10px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s ease-in-out;
        }

        button:hover {
            background-color: transparent;
            border: 1px solid #5864FF;
            color: #5864FF;
        }

        button:disabled {
            background-color: #cccccc;
            color: #666666;
            cursor: not-allowed;
        }

        #timer {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #ff9800;
        }
    </style>
</head>

<body>
    <div class="container">
        <div>
            <img src="{{ $app_setting['logo'] }}" alt="logo" width="200" class="mb-5">
        </div>
        <div class="header">
            <h3 class="fw-bolder text-white">{{ __('Action Required') }}: {{ __('Verify Your Email') }}</h3>
        </div>
        <div class="content">
            <form action="{{ route('verification.verify', auth()->user()->email) }}" method="POST">
                @csrf
                <h3>{{ __('OTP Verification') }}</h3>
                <p class="fw-bold fs-6">{{ __('Hello') }} {{ Auth::user()?->name }}</p>
                <p class="m-0">{{ __('Enter the 6-digit code') }}</p>
                <div id="timer">{{ __('Time remaining') }}: 0:00</div>
                <div class="otp-input">
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1"
                        autofocus>
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1">
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1">
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1">
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1">
                    <input type="number" min="0" max="9" required name="otp[]" maxlength="1">
                </div>
                <button type="submit">{{ __('Verify') }}</button>
            </form>
            <p class="text-muted mt-3">{{ __('If you didnt receive the OTP code, please re-request the OTP code') }} <a
                    onclick="localStorage.removeItem('otp_expiration_time')" id="resendButton" class="text-primary"
                    style="cursor: pointer; display:inline-block"
                    href="{{ route('verification.resend.otp', Auth::user()?->email) }}">
                    {{ __('Resend OTP') }}
                </a>
            </p>
        </div>
        <div class="footer">
            <p>{{ __('If you have any questions, feel free to') }} <a
                    href="#">{{ __('contact our support team') }}</a>.</p>
            <p>{{ __('Thank you') }},<br>The {{ config('app.name') }} Team</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- <script>
        const inputs = document.querySelectorAll('.otp-input input');
        const timerDisplay = document.getElementById('timer');
        const resendButton = document.getElementById('resendButton');
        resendButton.style.display = 'none';
        let timeLeft = 60; // 1 minutes in seconds
        let timerId;

        const OTP_EXPIRATION_KEY = 'otp_expiration_time';
        const OTP_VALIDITY_DURATION = 60; // seconds

        function startTimer() {
            timerId = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    timerDisplay.textContent = "Please re-request the OTP code";
                    resendButton.style.display = 'inline-block';
                    resendButton.disabled = false;
                    inputs.forEach(input => input.disabled = true);
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerDisplay.textContent = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    timeLeft--;
                }
            }, 1000);
        }
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length > 1) {
                    e.target.value = e.target.value.slice(0, 1);
                }
                if (e.target.value.length === 1) {
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value) {
                    if (index > 0) {
                        inputs[index - 1].focus();
                    }
                }
                if (e.key === 'e') {
                    e.preventDefault();
                }
            });
        });

        startTimer();
    </script> --}}

    <script>
        const inputs = document.querySelectorAll('.otp-input input');
        const timerDisplay = document.getElementById('timer');
        const resendButton = document.getElementById('resendButton');
        const OTP_EXPIRATION_KEY = 'otp_expiration_time';
        const OTP_VALIDITY_DURATION = 60; // seconds

        resendButton.style.display = 'none';

        function startTimer(expirationTimestamp) {
            const intervalId = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const remaining = expirationTimestamp - now;

                if (remaining <= 0) {
                    clearInterval(intervalId);
                    timerDisplay.textContent = "Please re-request the OTP code";
                    resendButton.style.display = 'inline-block';
                    resendButton.disabled = false;
                    inputs.forEach(input => input.disabled = true);
                    localStorage.removeItem(OTP_EXPIRATION_KEY);
                } else {
                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;
                    timerDisplay.textContent = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        function initializeTimer() {
            const now = Math.floor(Date.now() / 1000);
            let expiration = localStorage.getItem(OTP_EXPIRATION_KEY);

            if (!expiration) {
                expiration = now + OTP_VALIDITY_DURATION;
                localStorage.setItem(OTP_EXPIRATION_KEY, expiration);
            } else {
                expiration = parseInt(expiration);
            }

            if (expiration > now) {
                startTimer(expiration);
            } else {
                timerDisplay.textContent = "Please re-request the OTP code";
                resendButton.style.display = 'inline-block';
                resendButton.disabled = false;
                inputs.forEach(input => input.disabled = true);
            }
        }

        // OTP Input Behavior
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length > 1) {
                    e.target.value = e.target.value.slice(0, 1);
                }
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
                if (e.key === 'e') {
                    e.preventDefault();
                }
            });
        });

        // Remove expiration time manually on Resend
        resendButton.addEventListener('click', () => {
            localStorage.removeItem(OTP_EXPIRATION_KEY);
        });

        initializeTimer();
    </script>

    @if (session('otp_sent'))
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
                title: "{{ session('otp_sent') }}"
            });
        </script>
    @endif

    @if (session('verify_error'))
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
                icon: "error",
                title: "{{ session('verify_error') }}"
            });
        </script>
    @endif
</body>

</html>
