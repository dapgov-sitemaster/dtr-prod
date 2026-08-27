<?php

namespace App\Providers;

use App\Models\Event;
use App\View\Components\Custom\Calendar;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login-api', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('authenticated-api', function (Request $request) {
            return Limit::perMinute(120)->by((string) ($request->user()?->id ?? $request->ip()));
        });

        JsonResource::withoutWrapping();

        Gate::define('special-events', function (\App\Models\User $user) {
            return $user->role == \App\Enums\Role::SUPERADMIN || $user->role == \App\Enums\Role::HRADMIN;
        });

        Gate::define('view-dapcc', function (\App\Models\User $user) {
            return $user->employee->department->center == 'DAPCC' || $user->employee->department->office == 'ICTD';
        });

        Gate::define('view-pasig', function (\App\Models\User $user) {
            return $user->role == \App\Enums\Role::SUPERADMIN || $user->employee->department->center != 'DAPCC';
        });

        Gate::define('has-wfh-schedule', function (\App\Models\User $user) {
            $event = $user->employee->event()->whereDate('start', now()->format('Y-m-d'))->where('status', 'approved')->first();
            if ($event?->tag == \App\Enums\Events::WFH) {
                return true;
            }

            return false;
            // return $user->employee->event()->whereDate('time_start', now()->format('Y-m-d'))->first()->tag == \App\Enums\Events::WFH;
        });

        Gate::define('isAdminCoordinator', function (\App\Models\User $user) {
            return $user->role == \App\Enums\Role::SUPERADMIN || $user->role == \App\Enums\Role::ADMINCOORD || $user->role == \App\Enums\Role::CENTERADMINCOORD || $user->role == \App\Enums\Role::GROUPADMINCOORD;
        });

        Gate::define('isHrAdmin', function (\App\Models\User $user) {
            return $user->role == \App\Enums\Role::SUPERADMIN || $user->role == \App\Enums\Role::HRADMIN;
        });

        Gate::define('isHrAdminRsp', function (\App\Models\User $user) {
            return $user->role == \App\Enums\Role::SUPERADMIN || $user->role == \App\Enums\Role::HRADMIN;
        });

        Stringable::macro('initials', function () {
            $words = preg_split("/\s+/", $this);
            $initials = '';

            foreach ($words as $w) {
                $initials .= (strlen($w) > 0) ? $w[0] : '';
            }

            return new static($initials);
        });
        Str::macro('initials', function (string $string) {
            return (string) (new Stringable($string))->initials();
        });

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => [
                '50' => '237, 243, 255',
                '100' => '222, 232, 255',
                '200' => '196, 212, 255',
                '300' => '161, 183, 255',
                '400' => '123, 144, 254',
                '500' => '92, 105, 248',
                '600' => '62, 64, 237',
                '700' => '50, 49, 209',
                '800' => '42, 42, 169',
                '900' => '46, 49, 146',
                '950' => '25, 25, 77',
            ],
            'secondary' => [
                '50' => '254, 254, 232',
                '100' => '255, 254, 194',
                '200' => '255, 250, 135',
                '300' => '255, 240, 67',
                '400' => '255, 224, 16',
                '500' => '233, 193, 3',
                '600' => '206, 154, 0',
                '700' => '164, 110, 4',
                '800' => '136, 86, 11',
                '900' => '115, 70, 16',
                '950' => '67, 36, 5',
            ],
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        Blade::component('calendar', Calendar::class);
    }
}
