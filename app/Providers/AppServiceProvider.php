<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Filament\Support\Colors\Color;
use Illuminate\Support\Stringable;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;

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
        Stringable::macro('initials', function(){
            $words = preg_split("/\s+/", $this);
            $initials = "";

            foreach ($words as $w) {
              $initials .= $w[0];
            }

            return new static($initials);
        });
        Str::macro('initials', function(string $string){
            return (string) (new Stringable($string))->initials();
        });

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => [
                '50'=> '237, 243, 255',
                '100'=> '222, 232, 255',
                '200'=> '196, 212, 255',
                '300'=> '161, 183, 255',
                '400'=> '123, 144, 254',
                '500'=> '92, 105, 248',
                '600'=> '62, 64, 237',
                '700'=> '50, 49, 209',
                '800'=> '42, 42, 169',
                '900'=> '46, 49, 146',
                '950'=> '25, 25, 77',
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
    }
}
