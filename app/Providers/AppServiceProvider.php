<?php

namespace App\Providers;

use App\Services\Ai\Contracts\NlpDriver;
use App\Services\Ai\LocalNlpDriver;
use App\Services\Ai\OpenAiNlpDriver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocalNlpDriver::class);

        $this->app->bind(NlpDriver::class, function ($app) {
            $driver = config('services.ai.driver', 'local');
            $key = config('services.ai.openai_key');

            if ($driver === 'openai' && !empty($key)) {
                return new OpenAiNlpDriver(
                    fallback: $app->make(LocalNlpDriver::class),
                    apiKey: $key,
                    model: config('services.ai.openai_model', 'gpt-4o-mini'),
                );
            }

            return $app->make(LocalNlpDriver::class);
        });
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
