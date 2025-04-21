<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDepartmentSelected
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If both department_id and phone are missing, redirect to select-department-phone
        if ((is_null($user->department_id) || $user->department_id == 0) && empty($user->phone)) {
            return redirect()->route('select-department-phone');
        }

        // Redirect to select-department if only department_id is missing
        if (is_null($user->department_id) || $user->department_id == 0) {
            return redirect()->route('select-department');
        }

        // Redirect to enter-phone if only phone is missing
        if (empty($user->phone)) {
            return redirect()->route('enter-phone');
        }

        return $next($request);
    }
}
