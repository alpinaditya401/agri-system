<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar – Agrilink Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%; height: 100%;
            font-family: 'Outfit', sans-serif;
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
        
        /* Custom scrollbar for form container if needed */
        .form-wrapper::-webkit-scrollbar {
            width: 6px;
        }
        .form-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- Full-screen farm background (Same as Login) -->
    <div class="farm-bg"></div>

    <!-- Centered register card -->
    <div style="position:fixed;inset:0;z-index:10;display:flex;align-items:center;justify-content:center;padding:1rem;">
        <div class="form-wrapper" style="background:white;border-radius:1.5rem;box-shadow:0 25px 60px rgba(0,0,0,0.35);width:100%;max-width:420px;padding:2rem; max-height: 90vh; overflow-y: auto;">

            <!-- Logo icon -->
            <div style="display:flex;justify-content:center;align-items:center;margin-bottom:1rem;">
                <img src="{{ asset('images/agrilink_logo.png') }}" alt="Agrilink Logo" style="width:115px;height:115px;object-fit:contain;">
            </div>

            <!-- Title -->
            <div style="text-align:center;margin-bottom:1.25rem;">
                <h1 style="font-size:1.4rem;font-weight:700;color:#2d6a4f;">Pendaftaran Anggota</h1>
                <p style="font-size:0.8rem;font-weight:600;color:#666;letter-spacing:0.02em;margin-top:4px;">Pendaftaran untuk Jual Hasil Tani</p>
            </div>

            @if ($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.75rem;padding:0.75rem;margin-bottom:1rem;font-size:0.8rem;color:#dc2626;">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nama Lengkap -->
                <div style="margin-bottom:0.75rem;">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap" required autofocus
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='white';" onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                </div>

                <!-- Email -->
                <div style="margin-bottom:0.75rem;">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='white';" onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                </div>

                <!-- No. HP -->
                <div style="margin-bottom:0.75rem;">
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="No. HP"
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='white';" onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                </div>

                <!-- Password -->
                <div style="margin-bottom:0.75rem;position:relative;">
                    <input type="password" name="password" id="regPassword" placeholder="Password" required
                        style="width:100%;padding:0.75rem 2.5rem 0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='white';" onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                    <button type="button" onclick="toggleRegPass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Konfirmasi Password -->
                <div style="margin-bottom:0.75rem;">
                    <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background:#f9fafb;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='white';" onblur="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb';">
                </div>

                <!-- Jenis Akun -->
                <div style="margin-bottom:0.75rem;">
                    <select name="role" id="role-select" required
                        style="width:100%;padding:0.75rem 1rem;border:1px solid #d1d5db;border-radius:0.75rem;font-size:0.9rem;color:#374151;outline:none;font-family:inherit;transition:border-color .2s;background-color:#f9fafb; appearance:none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%20%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%236B7280%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center;"
                        onfocus="this.style.borderColor='#2d6a4f'; this.style.background='#fff url(...) no-repeat right 0.75rem center';" onblur="this.style.borderColor='#d1d5db'; this.style.backgroundColor='#f9fafb';">
                        <option value="" disabled selected>Pilih Jenis Akun</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                        @if(empty($roles) || count($roles) === 0)
                            <option value="buyer">Pembeli Umum / Penjual Hasil Tani</option>
                            <option value="farmer">Penjual Hasil Tani</option>
                            <option value="distributor">Distributor Pupuk</option>
                        @endif
                    </select>
                </div>

                <!-- Farmer Fields -->
                <div id="farmer-fields" style="display: {{ old('role') == 'farmer' ? 'block' : 'none' }}; margin-bottom: 0.75rem; padding: 0.85rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.75rem;">
                    <div style="font-size:0.75rem; font-weight:700; color:#047857; margin-bottom:0.5rem; text-transform:uppercase;">Verifikasi Petani</div>
                    <input type="text" name="nik" value="{{ old('nik') }}" placeholder="NIK (16 digit)" maxlength="16" 
                        style="width:100%;padding:0.6rem 0.85rem;border:1px solid #6ee7b7;border-radius:0.5rem;font-size:0.85rem;color:#374151;outline:none;font-family:inherit;margin-bottom:0.5rem;background:white;">
                    <input type="text" name="farmer_group_id" value="{{ old('farmer_group_id') }}" placeholder="ID Kelompok Tani" 
                        style="width:100%;padding:0.6rem 0.85rem;border:1px solid #6ee7b7;border-radius:0.5rem;font-size:0.85rem;color:#374151;outline:none;font-family:inherit;background:white;">
                </div>

                <!-- Submit -->
                <button type="submit"
                    style="width:100%;padding:0.85rem;background:#2d6a4f;color:white;font-weight:700;font-size:1rem;border:none;border-radius:0.75rem;cursor:pointer;font-family:inherit;transition:background .2s;letter-spacing:0.02em;margin-top:0.5rem;"
                    onmouseover="this.style.background='#1b4332'" onmouseout="this.style.background='#2d6a4f'">
                    Daftar Sekarang
                </button>

                <!-- Login link -->
                <p style="text-align:center;font-size:0.85rem;color:#6b7280;margin-top:1.25rem;">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" style="color:#2d6a4f;font-weight:600;text-decoration:none;">Masuk</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('role-select').addEventListener('change', function() {
            const farmerFields = document.getElementById('farmer-fields');
            farmerFields.style.display = (this.value === 'farmer') ? 'block' : 'none';
        });

        function toggleRegPass() {
            const input = document.getElementById('regPassword');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
