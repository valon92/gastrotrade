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

        <!-- Styles / Scripts -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
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
          #fallback-cart-bar{display:none;}
          @media (max-width:767px){ #fallback-cart-bar{display:flex!important;} }
        </style>
    </head>
<body class="font-sans antialiased">
    <!-- Shporta në navbar për mobile: nga HTML që të shfaqet edhe në arontrade.net; Vue e fsheh kur ka shportë në navbar -->
    <div id="fallback-cart-bar" style="max-width:80rem;margin:0 auto;padding:0 1rem;height:4rem;align-items:center;justify-content:flex-end;background:#fff;box-shadow:0 1px 3px 0 rgba(0,0,0,.1);">
        <a href="/shporta" style="display:flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:9999px;color:#374151;text-decoration:none;" aria-label="Shporta">
            <svg style="width:1.5rem;height:1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </a>
    </div>
    <div id="app"></div>
    </body>
</html>