<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>LMS MAN 1 Brebes</title>
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#10b981">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="LMS MAN 1 Brebes">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        
        <!-- Icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icon-512x512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Quill Editor (Word-like) -->
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            window.Laravel = {
                baseUrl: '{{ url("/") }}',
                assetUrl: '{{ asset("/") }}'
            }
        </script>
    </head>
    <body>
        <div id="app"></div>
        
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('{{ asset("service-worker.js") }}')
                        .then(registration => {
                            console.log('Service Worker registered successfully:', registration);
                        })
                        .catch(error => {
                            console.log('Service Worker registration failed:', error);
                        });
                });
            }
        </script>
    </body>
</html>
