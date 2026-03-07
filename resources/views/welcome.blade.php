<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Aron Trade') }}</title>
    <!-- Favicon AT logo - Aron Trade -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40' fill='none'%3E%3Crect width='40' height='40' rx='8' fill='%230d9488'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='system-ui,sans-serif' font-weight='700' font-size='16'%3EAT%3C/text%3E%3C/svg%3E">

        <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles / Scripts: në server (manifest në public_html/build/) nxjerr tag-et nga manifest; përndryshe @vite -->
        @php
            $manifestPath = base_path('build/manifest.json');
            $manifest = (file_exists($manifestPath)) ? json_decode(file_get_contents($manifestPath), true) : null;
        @endphp
        @if($manifest && isset($manifest['resources/js/app.js']['file']))
            @foreach($manifest['resources/js/app.js']['css'] ?? [] as $cssFile)
                <link rel="stylesheet" href="/build/{{ $cssFile }}" />
            @endforeach
            @if(isset($manifest['resources/css/app.css']['file']) && !in_array($manifest['resources/css/app.css']['file'], $manifest['resources/js/app.js']['css'] ?? []))
                <link rel="stylesheet" href="/build/{{ $manifest['resources/css/app.css']['file'] }}" />
            @endif
            <script type="module" src="/build/{{ $manifest['resources/js/app.js']['file'] }}"></script>
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <!-- Footer: ngjyrë e zezë + layout mobile (garantuar në production) -->
        <style>
          footer{background-color:#000!important;color:#fff!important;}
          footer a{color:#9ca3af;}
          footer a:hover{color:#fff;}
          @media (max-width:767px){
            footer ul{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:.5rem .75rem!important;font-size:12px!important;}
            footer ul li{white-space:nowrap!important;}
            footer .space-y-2>div{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:.5rem .75rem!important;font-size:12px!important;}
            footer .space-y-2>div span{white-space:nowrap!important;}
          }
        </style>
    </head>
<body class="font-sans antialiased">
    <div id="app"></div>
    </body>
</html>