{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $loginFavicon = null;
        if(auth()->check() && auth()->user()->institution_id) {
            $loginInst = \App\Models\Institution::find(auth()->user()->institution_id);
            $loginFavicon = $loginInst?->favicon;
        }
    @endphp
    @if($loginFavicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $loginFavicon) }}">
    @endif
    <title>Masuk — Agenda Kelas Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background-color: #f4f6fc;
            background-image:
                radial-gradient(circle at 85% 10%, rgba(34,81,211,0.07) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(99,102,241,0.05) 0%, transparent 40%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 16px;
            color: #17181a;
            position: relative;
            overflow-x: hidden;
        }

        /* Large decorative circles */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
        }
        body::before {
            width: 700px;
            height: 700px;
            background: rgba(34,81,211,0.045);
            top: -280px;
            right: -200px;
        }
        body::after {
            width: 500px;
            height: 500px;
            background: rgba(99,102,241,0.04);
            bottom: -180px;
            left: -160px;
        }

        /* Dot grid — bottom-right corner */
        .dot-grid {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 220px;
            height: 220px;
            background-image: radial-gradient(circle, rgba(34,81,211,0.12) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
            mask-image: radial-gradient(ellipse 100% 100% at 100% 100%, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse 100% 100% at 100% 100%, black 30%, transparent 80%);
        }

        [x-cloak] { display: none !important; }

        /* ── Wrapper ── */
        .login-wrap {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow:
                0 2px 4px rgba(0,0,0,0.04),
                0 8px 32px rgba(0,0,0,0.07),
                0 24px 64px rgba(34,81,211,0.06);
            padding: 32px 28px 28px;
        }

        /* ── Brand icon ── */
        .brand-icon {
            width: 44px;
            height: 44px;
            background: #EEF4FF;
            border: 1px solid #C7D9FF;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            flex-shrink: 0;
        }

        /* ── Heading ── */
        .card-heading {
            margin-bottom: 22px;
            text-align: center;
        }
        .card-heading h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0D1535;
            letter-spacing: -0.04em;
            line-height: 1.2;
            margin-bottom: 5px;
        }
        .card-heading p {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* ── Alerts ── */
        .alert {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 9px;
            margin-bottom: 14px;
            font-size: 13px;
            line-height: 1.45;
            font-weight: 500;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-err { background: #fff2f2; border: 1px solid #fbbaba; color: #b91c1c; }
        .alert-ok  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        /* ── Form ── */
        .form { display: flex; flex-direction: column; gap: 14px; }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
            letter-spacing: -0.01em;
        }

        .input-wrap { position: relative; }

        .input-ico {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #c4c9d4;
            pointer-events: none;
            display: flex;
            transition: color 0.15s;
        }
        .input-wrap:focus-within .input-ico { color: #2251d3; }

        .inp {
            display: block;
            width: 100%;
            height: 44px;
            padding: 0 44px 0 40px;
            background: #f8f9fc;
            border: 1.5px solid #e2e5ee;
            border-radius: 10px;
            font-size: 13.5px;
            font-family: inherit;
            color: #17181a;
            outline: none;
            transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
        }
        .inp:focus {
            background: #ffffff;
            border-color: #2251d3;
            box-shadow: 0 0 0 3.5px rgba(34,81,211,0.1);
        }
        .inp::placeholder { color: #c4c9d4; font-size: 13.5px; }
        .inp-no-right-pad { padding-right: 14px; }

        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #c4c9d4;
            display: flex;
            align-items: center;
            transition: color 0.12s;
        }
        .eye-btn:hover { color: #6b7280; }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: #edf0f7;
            margin: 2px 0;
        }

        /* ── Remember + Forgot ── */
        .row-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chk-label {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
            font-size: 13.5px;
            color: #4b5563;
        }
        .chk-label input {
            width: 15px;
            height: 15px;
            accent-color: #2251d3;
            cursor: pointer;
        }
        .forgot {
            font-size: 13.5px;
            font-weight: 600;
            color: #2251d3;
            text-decoration: none;
            transition: color 0.1s;
        }
        .forgot:hover { color: #1a3fa8; }

        /* ── Submit ── */
        .submit-btn {
            width: 100%;
            height: 44px;
            margin-top: 2px;
            background: #2251d3;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            letter-spacing: -0.01em;
            box-shadow: 0 4px 14px rgba(34,81,211,0.28), 0 1px 3px rgba(34,81,211,0.15);
            transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
        }
        .submit-btn:hover {
            background: #1a3fa8;
            box-shadow: 0 6px 20px rgba(34,81,211,0.38), 0 2px 6px rgba(34,81,211,0.18);
            transform: translateY(-1px);
        }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.75s linear infinite; display: inline-block; vertical-align: middle; }

        /* ── Footer ── */
        .login-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 12px;
            color: #b0b7c3;
        }

        /* ── Mobile ── */
        @media (max-width: 540px) {
            body { padding: 12px 16px; }
            .card { padding: 28px 20px 24px; }
            .card-heading { margin-bottom: 22px; }
            .card-heading h1 { font-size: 20px; }
            .form { gap: 14px; }
            .inp { height: 44px; font-size: 13px; }
            .submit-btn { height: 44px; font-size: 14px; }
            .brand-icon { width: 42px; height: 42px; margin-bottom: 16px; }
        }
    </style>
</head>
<body>

<div class="dot-grid"></div>

<div class="login-wrap">
    <div class="card">
        <div x-data="{
            showPass: false,
            loading: false,
            modal: {{ session()->has('error') ? 'true' : 'false' }}
        }">

            {{-- System modal --}}
            @if(session('error'))
            <div x-show="modal" x-cloak
                 style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;
                        justify-content:center;padding:20px;background:rgba(0,0,0,0.4);"
                 x-transition:enter="transition ease-out duration-180"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-130"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div style="background:#fff;border-radius:14px;padding:28px 24px;
                            max-width:360px;width:100%;
                            box-shadow:0 24px 64px rgba(0,0,0,0.16);
                            border:1px solid #e5e7eb;"
                     @click.away="modal = false"
                     x-transition:enter="transition ease-out duration-180"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div style="display:flex;gap:14px;align-items:flex-start;">
                        <div style="width:36px;height:36px;background:#fef9c3;border-radius:9px;
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="16" height="16" fill="none" stroke="#ca8a04" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <p style="font-size:14px;font-weight:700;color:#0D1117;margin-bottom:5px;">Informasi Sistem</p>
                            <p style="font-size:13.5px;color:#6b7280;line-height:1.6;margin-bottom:18px;">{{ session('error') }}</p>
                            <button @click="modal = false"
                                    style="padding:8px 18px;background:#0D1117;color:#fff;border:none;
                                           border-radius:8px;font-size:13px;font-weight:600;
                                           font-family:inherit;cursor:pointer;">
                                Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Calendar icon --}}
            <div class="brand-icon">
                <svg width="22" height="22" fill="none" stroke="#2251d3" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2.5" stroke-linejoin="round"/>
                    <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                    <path stroke-linecap="round" stroke-width="1.75" d="M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01"/>
                </svg>
            </div>

            {{-- Heading --}}
            <div class="card-heading">
                <h1>Selamat datang kembali</h1>
                <p>Masuk untuk melanjutkan ke akun Anda.</p>
            </div>

            {{-- Validation error --}}
            @if ($errors->any())
            <div class="alert alert-err">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-ok">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- Form --}}
            <form class="form" method="POST" action="{{ route('login') }}"
                  @submit="loading = true">
                @csrf

                {{-- Email / NIS --}}
                <div class="field">
                    <label for="email">Email atau NIS</label>
                    <div class="input-wrap">
                        <span class="input-ico">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                            </svg>
                        </span>
                        <input id="email" name="email" type="text"
                               class="inp inp-no-right-pad"
                               required autocomplete="username"
                               value="{{ old('email') }}"
                               placeholder="email@sekolah.id atau NIS">
                    </div>
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">Kata Sandi</label>
                    <div class="input-wrap">
                        <span class="input-ico">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="password" name="password"
                               :type="showPass ? 'text' : 'password'"
                               class="inp"
                               required autocomplete="current-password"
                               placeholder="Kata sandi Anda">
                        <button type="button" class="eye-btn"
                                @click="showPass = !showPass"
                                :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                            <svg x-show="!showPass" width="15" height="15" fill="none"
                                 stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" x-cloak width="15" height="15" fill="none"
                                 stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.355-3.355M9.878 9.878a3 3 0 014.243 4.243M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Remember + Forgot --}}
                <div class="row-extras">
                    <label class="chk-label">
                        <input type="checkbox" name="remember" id="remember">
                        Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot">Lupa password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="submit-btn" :disabled="loading">
                    <span x-show="!loading">Masuk</span>
                    <span x-show="loading" x-cloak style="display:flex;align-items:center;gap:8px;">
                        <svg class="spin" width="15" height="15" fill="none"
                             stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Memproses...
                    </span>
                </button>

            </form>
        </div>
    </div>

    <p class="login-footer">&copy; 2026 Agenda Kelas Digital</p>
</div>

</body>
</html>
