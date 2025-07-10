<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AdminAuthentication
{
    protected function checkAdminAuth()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'You must be logged in to access the admin area.');
        }
        
        return null; // Continue execution
    }
}
