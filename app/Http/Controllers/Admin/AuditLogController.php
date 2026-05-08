<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditTrail::with('actor')
            ->latest()
            ->paginate(50);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
