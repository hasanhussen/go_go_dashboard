<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; 
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\CheckUserStatus;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'email.verified'=> EnsureEmailIsVerified::class,
            'CheckUserStatus' => CheckUserStatus::class,

        ]);
    })
    ->withSchedule(function (Schedule $schedule) { // 👈 أضف هذا القسم
        $schedule->command('app:delete-expired-trashed')
                 ->dailyAt('12:00')
                 ->runInBackground();

                 $schedule->command('app:unban')
             ->daily()
             ->runInBackground();

   $schedule->command('app:is-orderediting')
             ->everyMinute()
             ->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
