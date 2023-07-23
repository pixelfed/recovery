<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pixelfed Recovery</title>
        <link rel="shortcut icon" type="image/png" href="/img/favicon.png">
        <link rel="apple-touch-icon" type="image/png" href="/img/favicon.png">
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
