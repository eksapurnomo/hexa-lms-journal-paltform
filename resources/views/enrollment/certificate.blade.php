<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificate</title>
    <style>
        @page {
            padding: 0;
            margin: 0;
        }

        @font-face {
            font-family: 'GoodVibes';
            src: url('./fonts/GreatVibes-Regular.ttf') format("truetype");
            font-weight: 400;
            font-style: normal;
        }

        body {
            font-family: 'GoodVibes', cursive;
            font-size: 16px;
            color: #5864FF;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 810px;
            margin: 0 auto;
            padding: 20px;
        }

        .position-relative {
            position: relative;
        }

        .logo-section {
            margin-top: 80px;
            width: 100%;
            display: table;
        }

        .logo-section .table-cell1 {
            display: table-cell;
            width: 175px;
        }

        .logo-section .table-cell2 {
            display: table-cell;
            width: 175px;
            text-align: right;
        }

        .certificate-header {
            text-align: center;
            margin-top: -15px;
        }

        .certificate-header h3 {
            font-size: 60px;
            color: #5864FF;
            margin: 0;
            font-family: "Cambria", serif;
            font-weight: 400;
            margin-bottom: 8px;
        }

        .certificate-header h5 {
            font-size: 28px;
            color: #24262D;
            margin: 0;
            font-family: "Inter", serif;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .certificate-header p {
            margin: 0 !important;
            font-size: 22px;
            color: #24262D;
            font-family: "Inter", serif;
            font-weight: 500;
            line-height: 32px;
        }

        .student-name h1 {
            margin: 0 !important;
            color: #5864FF;
            text-align: center;
            font-family: "GoodVibes", cursive;
            font-size: 50px;
            font-style: normal;
            font-weight: 400;
            border-bottom: 2px solid #D7DAE0;
            padding-bottom: 16px;
        }

        .description {
            color: #24262D;
            text-align: center;
            margin-top: 1rem;
        }

        .description p {
            margin: 0 !important;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 28px;
        }

        .description p span {
            font-weight: 600;
        }

        input {
            padding: 10px;
            border-radius: 20px;
            border: 2px solid steelblue;
            font-size: 1.5rem;
            letter-spacing: 2px;
            outline: none;
        }
    </style>
</head>


<body
    style="background-image: url({{ $certificate?->framePath ?? './enrollment/themeone.png' }}); background-size: cover;"">

    <div class="container">
        <div>
            <div class="logo-section">
                <div class="table-cell1">
                    <img src="{{ $certificate?->siteLogoPath ?? './assets/images/auth/logo.png' }}" alt=""
                        style="width:236px; height: 70px; object-fit:contain;">
                </div>
                <div class="table-cell2">
                    <img src="{{ $qrCodeImage }}" alt="qrCode" style="width:80px; height: 80px; object-fit:contain;">
                </div>
            </div>
            <div class="certificate-header">
                <h3>Certificate</h3>
                <h5>Of {{ $certificate?->certificate_title ?? 'Achievement' }}</h5>
                <div class="space-between" style="display: table; padding: 8px 0; width: 100%;">
                    <div style="display: table-cell; vertical-align: middle;">
                        <img src="./enrollment/line.svg" alt="" style="width: 150px; margin-top:12px">
                    </div>
                    <div style="display: table-cell; vertical-align: middle;">
                        <p>{{ $certificate?->certificate_short_text ?? 'This Certificate is Proudly Presented to' }}
                        </p>

                    </div>
                    <div style="display: table-cell; vertical-align: middle;">
                        <img src="./enrollment/line.svg" alt="" style="width: 150px; margin-top:12px">
                    </div>
                </div>
            </div>

            <div class="student-name">
                <h1>{{ $studentName }}`</h1>
            </div>
            @php
                $styledCourseTitle = "<strong style='font-size: 20px;'>$courseTitle</strong>";
                $description = str_replace('{course_name}', $styledCourseTitle, $certificate?->certificate_text);
            @endphp
            <div class="description">
                <p>
                    {!! $description ?? 'You have successfully completed the course' !!}
                </p>
            </div>

            <div class="signature" style="display: table; width: 100%; margin-top: 1rem;">
                <div style="display: table-cell; vertical-align: middle; width: 50%;">
                    <div style="width:50%; text-align:center">
                        @if ($instructor->signature_id)
                            <img src="{{ $instructor?->signaturePath ?? $instructorSignature }}" alt=""
                                style="width: 100px; height: 40px; object-fit:contain; padding-bottom:8px">
                        @else
                            <h3>
                                {{ $instructor?->name ?? 'Fahim Hossain Munna' }}
                            </h3>
                        @endif
                        <p
                            style="border-top: 2px solid #D7DAE0; font-family: 'Inter', sans-serif; font-size: 14px; color: #24262D; line-height: 24px; height: 80px; margin:0; padding:10px 0px">
                            {{ $instructorTitle ?? 'Honorable Mentor' }}
                        </p>
                    </div>
                </div>
                <div style="display: table-cell; vertical-align: middle; width: 50%; text-align:right">
                    <div style="width:50%; text-align:center; display:inline-block">
                        @if ($certificate?->author_signature_id != null)
                            <img src="{{ $certificate?->authSignaturePath }}" alt=""
                                style="width: 100px; height: 40px; object-fit:contain; padding-bottom:8px">
                        @else
                            <h3>
                                {{ $certificate?->author_name ?? $appName }}
                            </h3>
                        @endif
                        <p
                            style="border-top: 2px solid #D7DAE0; font-family: 'Inter', sans-serif; font-size: 14px; color: #24262D; height: 80px; margin:0; padding:10px 0px">
                            <span style="text-transform:capitalize">{{ $certificate?->author_name ?? $appName }}
                                ,
                                {{ $certificate?->author_designation }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
