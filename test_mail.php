<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "=== Testing email delivery to adtyanugraha654@gmail.com ===\n\n";

// Show current config
echo "Mail config:\n";
echo "  Mailer: " . config('mail.default') . "\n";
echo "  Host: " . config('mail.mailers.smtp.host') . "\n";
echo "  Port: " . config('mail.mailers.smtp.port') . "\n";
echo "  Scheme: " . (config('mail.mailers.smtp.scheme') ?: '(empty)') . "\n";
echo "  Username: " . config('mail.mailers.smtp.username') . "\n";
echo "  From: " . config('mail.from.address') . "\n\n";

try {
    Mail::raw('Ini adalah test email dari HRIS Sirojul Falah. Jika kamu menerima ini berarti SMTP berfungsi dengan baik. Waktu kirim: ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('adtyanugraha654@gmail.com')
                ->subject('[TEST] Email HRIS - ' . date('H:i:s'));
    });
    echo "SUCCESS: Email berhasil dikirim ke adtyanugraha654@gmail.com!\n";
    echo "Cek inbox, Spam, dan semua tab Gmail.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
