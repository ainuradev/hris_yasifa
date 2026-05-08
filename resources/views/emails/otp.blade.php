<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            color: #0f172a;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .otp-box {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #0284c7;
            margin: 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Sirojul Falah HRIS</h1>
        </div>
        
        <p>Halo, <strong>{{ $name }}</strong></p>
        <p>Kami menerima permintaan reset password untuk akun Anda di sistem HRIS. Berikut adalah kode OTP Anda:</p>
        
        <div class="otp-box">
            <p class="otp-code">{{ $otp }}</p>
        </div>
        
        <p>Kode OTP ini hanya berlaku selama <strong>10 menit</strong>. Jangan berikan kode ini kepada siapapun.</p>
        
        <p>Jika Anda tidak meminta reset password, abaikan saja email ini.</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Yayasan Sirojul Falah. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>
</html>
