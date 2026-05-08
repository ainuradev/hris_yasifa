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
            
            <div>
                <label class="block text-sm font-bold text-teal-900 mb-2 ml-1">Password Baru</label>
                <input type="password" name="password" 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                    placeholder="Minimal 8 karakter" required>
                @error('password') <p class="mt-2 text-xs text-rose-600 font-bold ml-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-teal-900 mb-2 ml-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                    placeholder="Ulangi password baru" required>
            </div>

            <button type="submit" class="btn-submit mt-4">
                Simpan & Login
            </button>
        </form>
    </div>
</body>
</html>
