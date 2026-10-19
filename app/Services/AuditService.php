<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log($action, $module, $description, $oldValues = null, $newValues = null)
    {
        AuditTrail::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'ip_address'  => Request::ip(),
        ]);
    }
}