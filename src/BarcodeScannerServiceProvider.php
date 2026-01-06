<?php

namespace Pixalink\FilamentQrcodeScannerHtml5;

use Illuminate\Support\ServiceProvider;
use Pixalink\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

class BarcodeScannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-qrcode-scanner-html5');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-qrcode-scanner-html5'),
        ], 'filament-qrcode-scanner-html5-views');

        $this->registerLivewireHook();
    }

    protected function registerLivewireHook(): void
    {
        \Livewire\on('call', function ($component, $method, $params, $addEffect, $returnEarly) {
            if ($method !== 'processBarcodeScan') {
                return function () {};
            }

            [$callbackId, $value, $formatId] = $params;

            $format = BarcodeFormat::fromHtml5QrcodeFormat((int) $formatId);
            $result = BarcodeScannerCallbackRegistry::execute($callbackId, (string) $value, $format);

            $returnEarly($result);

            return function () {};
        });
    }
}
