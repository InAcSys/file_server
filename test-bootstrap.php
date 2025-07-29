<?php

require_once __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    
    echo "✓ Application bootstrap successful\n";
    
    // Debug: List what's actually bound in the container
    echo "Debugging container bindings...\n";
    $bindings = $app->getBindings();
    echo "Number of bindings: " . count($bindings) . "\n";
    
    // Check if config is bound
    if ($app->bound('config')) {
        echo "✓ Config service is bound\n";
    } else {
        echo "✗ Config service is NOT bound\n";
        
        // Force bootstrap the application
        echo "Attempting to bootstrap the application...\n";
        $app->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ]);
        
        if ($app->bound('config')) {
            echo "✓ Config service bound after manual bootstrap\n";
        } else {
            echo "✗ Config service still not bound after manual bootstrap\n";
            exit(1);
        }
    }
    
    // Test config access
    $config = $app->make('config');
    echo "✓ Config service resolved successfully\n";
    
    // Test basic config values
    echo "App Name: " . $config->get('app.name') . "\n";
    echo "App Environment: " . $config->get('app.env') . "\n";
    
    echo "✓ All tests passed\n";
    
} catch (Exception $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
