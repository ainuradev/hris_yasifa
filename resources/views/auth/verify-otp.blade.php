<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Verifikasi OTP - HRIS Sirojul Falah</title>
    
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
        .otp-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="glass-card mx-4">
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-100 text-teal-600 mb-6">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-teal-950 mb-2">Cek Email Anda</h2>
            <p class="text-slate-500 font-medium leading-relaxed">Kami telah mengirimkan 6 digit kode OTP ke email <br><strong class="text-teal-800">{{ session('otp_email') }}</strong></p>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 text-sm font-medium text-center">
                {{ session('error') }}
            </div>
        @endif
        
        @if (session('success'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-100 rounded-xl text-teal-700 text-sm font-medium text-center">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.verify') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-bold text-teal-900 mb-2 text-center">Masukkan Kode OTP</label>
                <input type="text" name="otp" 
                    class="otp-input w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-bold text-teal-900"
                    placeholder="••••••" maxlength="6" required autocomplete="off">
                @error('otp') <p class="mt-2 text-xs text-rose-600 font-bold text-center">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-submit mt-4">
                Verifikasi OTP
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-sm text-slate-500 mb-2">Tidak menerima email?</p>
            <form method="POST" action="{{ route('password.otp.resend') }}">
                @csrf
                <button type="submit" class="text-teal-600 font-bold hover:text-teal-800 transition-colors text-sm">
                    Kirim Ulang OTP
                </button>
            </form>
        </div>
    </div>
</body>
</html>
