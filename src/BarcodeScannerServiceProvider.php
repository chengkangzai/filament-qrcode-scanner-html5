<?php

namespace CCK\FilamentQrcodeScannerHtml5;

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Filament\Forms\Components\Component as FormComponent;
use Illuminate\Support\ServiceProvider;

use function Livewire\on;

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
        on('call', function ($component, $method, $params, $addEffect, $returnEarly) {
            if ($method !== 'processBarcodeScan') {
                return;
            }

            [$statePath, $value, $formatId] = $params;

            if (! method_exists($component, 'getCachedForms')) {
                $returnEarly($value);

                return;
            }

            foreach ($component->getCachedForms() as $form) {
                $formComponent = $form->getComponent(
                    fn (FormComponent $c) => $c->getStatePath() === $statePath
                );

                if (! $formComponent) {
                    continue;
                }

                $action = $formComponent->getAction('barcode-scanner');

                if (! $action instanceof BarcodeScannerAction) {
                    continue;
                }

                $callback = $action->getStateModifierPhp();

                if (! $callback) {
                    $returnEarly($value);

                    return;
                }

                $format = BarcodeFormat::fromHtml5QrcodeFormat((int) $formatId);
                $returnEarly($callback((string) $value, $format));

                return;
            }

            $returnEarly($value);
        });
    }
}
