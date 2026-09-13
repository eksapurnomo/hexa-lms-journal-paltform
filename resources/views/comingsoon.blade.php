<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="http://robotoy.test/assets/favicon.png" type="image/x-icon">
    <style>
        body,
        html {
            height: 100svh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: "http://robotoy.test/assets/images/admin-bg.png";
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .coming-soon-container {
            text-align: center;
            max-width: 800px;
            padding: 50px;
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
        }

        .coming-soon-container h3 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
            color: #333;
        }

        .coming-soon-container p {
            font-size: 20px;
            color: #666;
            margin-bottom: 30px;
        }

        .coming-soon-container .logo {
            width: 220px;
            margin-bottom: 30px;
        }

        .coming-soon-container .btn {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 50px;
        }

        @media (max-width: 768px) {
            .coming-soon-container h1 {
                font-size: 48px;
            }

            .coming-soon-container p {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="coming-soon-container">
        <img class="logo" src="{{ asset('assets/images/logo-new.png') }}" alt="Logo">
        <h3>Development in Process</h3>
        <p>Our website is currently under development. We are working hard to bring you an exciting new experience.
            Please check back soon.</p>
        <button class="btn btn-primary">Notify Me</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
