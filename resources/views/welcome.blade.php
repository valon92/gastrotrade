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

        <!-- Styles / Scripts: server = manifest base_path; localhost = manifest public_path ose vite dev -->
        @php
            $serverManifestPath = base_path('build/manifest.json');
            $serverManifest = (file_exists($serverManifestPath)) ? json_decode(file_get_contents($serverManifestPath), true) : null;
            $publicManifest = !$serverManifest && file_exists(public_path('build/manifest.json')) ? json_decode(file_get_contents(public_path('build/manifest.json')), true) : null;
        @endphp
        @if($serverManifest && isset($serverManifest['resources/js/app.js']['file']))
            @foreach($serverManifest['resources/js/app.js']['css'] ?? [] as $cssFile)
                <link rel="stylesheet" href="/build/{{ $cssFile }}" />
            @endforeach
            @if(isset($serverManifest['resources/css/app.css']['file']) && !in_array($serverManifest['resources/css/app.css']['file'], $serverManifest['resources/js/app.js']['css'] ?? []))
                <link rel="stylesheet" href="/build/{{ $serverManifest['resources/css/app.css']['file'] }}" />
            @endif
            <script type="module" src="/build/{{ $serverManifest['resources/js/app.js']['file'] }}"></script>
        @elseif($publicManifest && isset($publicManifest['resources/js/app.js']['file']))
            @foreach($publicManifest['resources/js/app.js']['css'] ?? [] as $cssFile)
                <link rel="stylesheet" href="/build/{{ $cssFile }}" />
            @endforeach
            @if(isset($publicManifest['resources/css/app.css']['file']) && !in_array($publicManifest['resources/css/app.css']['file'], $publicManifest['resources/js/app.js']['css'] ?? []))
                <link rel="stylesheet" href="/build/{{ $publicManifest['resources/css/app.css']['file'] }}" />
            @endif
            <script type="module" src="/build/{{ $publicManifest['resources/js/app.js']['file'] }}"></script>
        @else
            @include('partials.vite')
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