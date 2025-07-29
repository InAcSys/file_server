<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Swoole\Handlers\OnWorkerStart;
use Laravel\Octane\Swoole\SwooleExtension;
use Laravel\Octane\Swoole\WorkerState;
use Swoole\Http\Server;

class OctaneServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the default OnWorkerStart handler to fix the config issue
        $this->app->bind(OnWorkerStart::class, function ($app) {
            return new class extends OnWorkerStart {
                public function __construct(
                    protected SwooleExtension $extension,
                    protected $basePath,
                    protected array $serverState,
                    protected WorkerState $workerState,
                    protected bool $shouldSetProcessName = true
                ) {
                    parent::__construct($extension, $basePath, $serverState, $workerState, $shouldSetProcessName);
                }

                /**
                 * Determine if the opcode cache should be cleared.
                 * 
                 * Fixed version that doesn't call config() before app is bootstrapped.
                 */
                protected function shouldClearOpcodeCache(): bool
                {
                    // Always return false to avoid calling config() during worker startup
                    // This prevents the "Class 'config' does not exist" error
                    return false;
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
