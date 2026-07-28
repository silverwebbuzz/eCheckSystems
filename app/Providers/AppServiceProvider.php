<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    // URL::forceScheme('https');
    DB::listen(function ($query) {
    if (stripos($query->sql, 'update `qbo_companies`') !== false) {
        Log::info('QBO Company Update SQL', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
                ->map(fn($t) => ($t['file'] ?? '').':'.($t['line'] ?? ''))
                ->take(10) // just the top 10 frames
        ]);
    }
});

    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' :
                    (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });
  }
}