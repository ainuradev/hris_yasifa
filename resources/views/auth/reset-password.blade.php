<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Buat Password Baru - HRIS Sirojul Falah</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #F0FDF4;
            color: #134E4A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 28px;
            padding: 48px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 480px;
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: #0D9488;
            color: white;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background: #0B7A6F;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13, 148, 136, 0.2);
        }
    </style>
</head>
<body>
    <div class="glass-card mx-4">
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-teal-950 mb-2">Buat Password Baru</h2>
            <p class="text-slate-500 font-medium leading-relaxed">Silakan masukkan password baru Anda. Pastikan kombinasi password aman dan mudah diingat.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-6">
            @csrf
            
            <div x-data="{ showPassword: false }">
                <label class="block text-sm font-bold text-teal-900 mb-2 ml-1">Password Baru</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password" 
                        class="w-full px-5 py-4 pr-14 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                        placeholder="Minimal 8 karakter" required>
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-teal-600 transition-colors rounded-lg hover:bg-teal-50"
                        :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="mt-2 text-xs text-rose-600 font-bold ml-1">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ showPassword: false }">
                <label class="block text-sm font-bold text-teal-900 mb-2 ml-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" 
                        class="w-full px-5 py-4 pr-14 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                        placeholder="Ulangi password baru" required>
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-teal-600 transition-colors rounded-lg hover:bg-teal-50"
                        :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit mt-4">
                Simpan & Login
            </button>
        </form>
    </div>
</body>
</html>
