<?php

namespace App\Http;

use App\Http\Middleware\XssSanitization;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\RedirectToInstallerIfNotInstalled::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
//        XssSanitization::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\SetLocale::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],

        'install' => [ \App\Http\Middleware\canInstall::class ],
        'update' => [ \App\Http\Middleware\canUpdate::class ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'XssSanitizer' => XssSanitization::class,
        'check.department' => \App\Http\Middleware\CheckDepartmentSelected::class,
        'otp' => \App\Http\Middleware\OtpMiddleware::class,
    ];
    
   

    protected $middlewarePriority = [
        // ...
        \Illuminate\Session\Middleware\StartSession::class,
        \App\Http\Middleware\SetLocale::class,
        // ...
    ];
    
    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->command('tickets:send-reminders')->hourly();
    // }
    // protected function scheduleBackup(Schedule $schedule)
    // {
    //     // Schedule the command to run daily at 5 PM
    //      $schedule->command('email:sql-backup')->everyMinute();
    // }
}
