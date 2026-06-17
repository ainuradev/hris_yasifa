<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>HRIS Sirojul Falah — Sistem Manajemen SDM Terpadu</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Palette Opsi 2: Nature & Growth */
            --primary-teal: #0D9488;
            --primary-light: #F0FDF4; /* Mint soft background */
            --text-dark: #134E4A; /* Dark teal untuk teks */
            --text-muted: #64748B;
            --white: #ffffff;
            --glass-white: rgba(255, 255, 255, 0.8);
        }

        body {
            background-color: var(--primary-light);
            background-image: 
                radial-gradient(at 0% 0%, rgba(13, 148, 136, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.1) 0px, transparent 50%);
            color: var(--text-dark);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* ── Intro Section ── */
        .section-view {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .intro-content {
            text-align: center;
            max-width: 850px;
            z-index: 10;
        }

        .tag {
            background: var(--white);
            color: var(--primary-teal);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.08);
            margin-bottom: 24px;
        }

        .tag-dot {
            width: 8px;
            height: 8px;
            background: var(--primary-teal);
            border-radius: 50%;
        }

        .hero-title-large {
            font-size: clamp(32px, 5vw, 64px);
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 24px;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .hero-title-large span {
            color: var(--primary-teal);
        }

        .hero-desc {
            max-width: 600px;
            margin: 0 auto 40px;
            font-size: 18px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .btn-entry {
            padding: 18px 44px;
            background: var(--primary-teal);
            color: var(--white);
            border-radius: 16px; /* Lebih rounded tapi tetap kokoh */
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(13, 148, 136, 0.25);
        }

        .btn-entry:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(13, 148, 136, 0.35);
            background: #0B7A6F;
        }

        /* ── Glassmorphism Form (Light Version) ── */
        .glass-card {
            background: var(--glass-white);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            padding: 48px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 450px;
        }

        .form-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-teal);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        /* Hide Scrollbar */
        body::-webkit-scrollbar { display: none; }
    </style>
</head>
<body x-data="{ page: '{{ $errors->any() || old('login') || session('error') ? 'login' : 'intro' }}' }" x-cloak>

    <main class="min-h-screen relative flex items-center justify-center overflow-hidden">
        
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-teal-500/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none"></div>

        <template x-if="page === 'intro'">
    <section class="container mx-auto px-6 relative z-10">
        <div class="grid lg:grid-cols-12 gap-8 items-center">
            
        <div class="lg:col-span-6 flex flex-col items-center lg:items-start text-center lg:text-left">
    
    <div class="tag inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-teal-50 mb-6">
        <span class="tag-dot w-2 h-2 bg-teal-600 rounded-full animate-pulse"></span>
        <span class="text-sm font-semibold text-teal-900 uppercase tracking-wider">Portal SDM Terintegrasi</span>
    </div>
    
    <h1 class="hero-title-large text-5xl lg:text-7xl font-extrabold text-teal-950 leading-[1.1] mb-6">
        Transformasi Digital <br>
        <span class="text-teal-600 font-black">Sirojul Falah</span>
    </h1>
    
    <p class="text-lg text-slate-600 leading-relaxed mb-8 lg:max-w-[90%]">
        Kelola data guru, absensi, dan payroll secara efisien dalam satu platform yang dirancang khusus untuk kemajuan pendidikan yayasan.
    </p>
    
    <div class="flex justify-center lg:justify-start w-full">
        <button @click="page = 'login'" class="btn-entry group">
            <span>Mulai Sekarang</span>
            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </div>
</div>

            <div class="hidden lg:block lg:col-span-5 relative">
                <div class="absolute -top-6 -left-6 w-64 h-64 bg-teal-100 rounded-3xl -z-10 rotate-3"></div>
                
                <div class="relative z-10 bg-white/40 backdrop-blur-md p-4 rounded-[2rem] border border-white/60 shadow-2xl transform hover:-rotate-1 transition-all duration-500">
                    <img src="https://illustrations.popsy.co/teal/work-from-home.svg" alt="Illustration" class="w-full h-auto drop-shadow-lg">
                    
                    <div class="absolute bottom-10 -right-8 z-20 bg-white p-4 rounded-2xl shadow-[0_20px_50px_rgba(13,148,136,0.2)] border border-teal-50 flex items-center gap-4 animate-bounce-slow">
                        <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="whitespace-nowrap">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Status Kehadiran</p>
                            <p class="text-sm font-bold text-teal-900">98% Terverifikasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

        <template x-if="page === 'login'">
            <div class="glass-card relative z-10 w-full max-w-[480px] mx-4 transition-all duration-500 animate-in fade-in zoom-in-95">
                <button @click="page = 'intro'" class="group mb-8 flex items-center gap-2 text-sm font-semibold text-teal-800 hover:text-teal-600 transition">
                    <div class="p-2 bg-teal-50 rounded-lg group-hover:bg-teal-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </div>
                    Kembali ke Beranda
                </button>

                <div class="mb-10">
                    <h2 class="text-3xl font-extrabold text-teal-950 mb-2">Selamat Datang</h2>
                    <p class="text-slate-500 font-medium">Masuk untuk mengelola dashboard SDM</p>
                </div>

                @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-xl flex items-center gap-3 text-rose-700 text-sm font-medium animate-shake">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-teal-900 mb-2 ml-1">Email / NIK / NUPTK</label>
                        <input type="text" name="login" value="{{ old('login') }}" 
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Masukkan email, NIK, atau NUPTK" required>
                        @error('login') <p class="mt-2 text-xs text-rose-600 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-bold text-teal-900 ml-1">Password</label>
                            <a href="{{ route('password.request') }}" class="text-sm font-bold text-teal-600 hover:text-teal-800 transition-colors">Lupa Password?</a>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" 
                                class="w-full px-5 py-4 pr-14 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                                placeholder="••••••••" required>
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-teal-600 transition-colors rounded-lg hover:bg-teal-50"
                                :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                {{-- Eye open (show) --}}
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{-- Eye closed (hide) --}}
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <p class="mt-2 text-xs text-rose-600 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-submit !py-5 !text-lg !rounded-2xl shadow-xl shadow-teal-600/20 active:scale-95 transition-transform">
                        Masuk Sekarang
                    </button>
                </form>
            </div>
        </template>
    </main>

    <style>
        [x-cloak] { display: none !important; }
        .animate-bounce-slow { animation: bounce 3s infinite; }
        @keyframes bounce {
            0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
            50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
        }
        .animate-shake { animation: shake 0.5s; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</body>
</html>