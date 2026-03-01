<?php

namespace CCK\FilamentQrcodeScannerHtml5\Tests;

use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Share a ViewErrorBag with all views to prevent null error bag issues
        view()->share('errors', app(\Illuminate\Support\ViewErrorBag::class));
    }

    protected function getPackageProviders($app): array
    {
        $providers = [
            LivewireServiceProvider::class,
            BarcodeScannerServiceProvider::class,
        ];

        // Filament 4 registers these separately; Filament 5 may consolidate
        foreach ([
            \Filament\Support\SupportServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
        ] as $provider) {
            if (class_exists($provider)) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('session.driver', 'array');
        $app['config']->set('session.encrypt', false);
    }
}
