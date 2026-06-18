<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk – Agrilink Portal</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%; height: 100%;
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
        }
        .farm-bg {
            position: fixed;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            z-index: 0;
        }
        .farm-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>

    <!-- Full-screen farm background -->
    <div class="farm-bg"></div>

    <!-- Centered login card -->
    <div style="position:fixed;inset:0;z-index:10;display:flex;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:white;border-radius:1.5rem;box-shadow:0 25px 60px rgba(0,0,0,0.35);width:100%;max-width:380px;padding:2.5rem 2rem;">

            <!-- Logo icon -->
            <div style="display:flex;justify-content:center;align-items:center;margin-bottom:1rem;">
                <img src="{{ asset('images/agrilink_logo.png') }}" alt="Agrilink Logo" style="width:110px;height:110px;object-fit:contain;">
            </div>

            <!-- Title -->
            <div style="text-align:center;margin-bottom:1.5rem;">
                <h1 style="font-size:1.4rem;font-weight:700;color:#2d6a4f;">Agrilink Portal</h1>
                <p style="font-size:0.8rem;font-weight:600;color:#666;letter-spacing:0.08em;text-transform:uppercase;margin-top:4px;">Masuk Portal Agrilink</p>
            </div>

            @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.75rem;padding:0.75rem;margin-bottom:1rem;font-size:0.8rem;color:#dc2626;">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div style="margin-bottom:0.85rem;">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#2d6a4f'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                <!-- Password -->
                <div style="margin-bottom:0.5rem;position:relative;">
                    <input type="password" name="password" id="passInput" placeholder="Password" required
                        style="width:100%;padding:0.75rem 3rem 0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#2d6a4f'" onblur="this.style.borderColor='#d1d5db'">
                    <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Lupa Password -->
                <div style="text-align:right;margin-bottom:1.25rem;">
                    <a href="#" style="font-size:0.8rem;color:#2d6a4f;font-weight:500;text-decoration:none;">Lupa Password?</a>
                </div>

                <!-- Submit -->
                <button type="submit"
                    style="width:100%;padding:0.85rem;background:#2d6a4f;color:white;font-weight:700;font-size:1rem;border:none;border-radius:0.75rem;cursor:pointer;font-family:inherit;transition:background .2s;letter-spacing:0.02em;"
                    onmouseover="this.style.background='#1b4332'" onmouseout="this.style.background='#2d6a4f'">
                    Masuk
                </button>

                <!-- Register link -->
                <p style="text-align:center;font-size:0.85rem;color:#6b7280;margin-top:1.25rem;">
                    Belum punya akun?
                    <a href="{{ route('register') }}" style="color:#2d6a4f;font-weight:600;text-decoration:none;">Daftar</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        function togglePass() {
            const p = document.getElementById('passInput');
            p.type = p.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
