<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $app_setting['name'] }}</title>
    <link rel="shortcut icon" href="{{ $app_setting['favicon'] }}" type="image/x-icon">
    @vite('resources/js/app.js')
</head>

<body>
    <div id="app"></div>
</body>

</html>
