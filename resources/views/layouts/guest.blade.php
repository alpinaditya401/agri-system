<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'AgroSystem') }}</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: 'Outfit', sans-serif;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .auth-page {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #052e16; /* solid dark green fallback */
            background-image: url('/images/agri_background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow-y: auto;
        }
        .auth-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.45) 100%);
            z-index: 0;
            pointer-events: none;
        }
        .auth-content {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 1rem;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-overlay"></div>
        <div class="auth-content">
            @yield('content')
        </div>
    </div>
</body>
</html>
