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
        $this->registerFormActionHook();
        $this->registerHeaderActionHook();
    }

    protected function registerFormActionHook(): void
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

    protected function registerHeaderActionHook(): void
    {
        on('call', function ($component, $method, $params, $addEffect, $returnEarly) {
            if ($method !== 'processBarcodeScanHeader') {
                return;
            }

            [$value, $formatId] = $params;

            if (! method_exists($component, 'getMountedAction')) {
                $returnEarly(['success' => false]);

                return;
            }

            $action = $component->getMountedAction();

            if (! $action instanceof BarcodeScannerHeaderAction) {
                $returnEarly(['success' => false]);

                return;
            }

            $callback = $action->getAfterScanCallback();

            if (! $callback) {
                $returnEarly(['success' => true, 'value' => $value]);

                return;
            }

            $format = BarcodeFormat::fromHtml5QrcodeFormat((int) $formatId);
            $result = $callback((string) $value, $format);

            $returnEarly($this->normalizeHeaderActionResult($result));
        });
    }

    /**
     * Normalize the result from afterScan callback to a format JavaScript can handle.
     *
     * @return array{success: bool, redirect?: string}
     */
    protected function normalizeHeaderActionResult(mixed $result): array
    {
        // Handle RedirectResponse
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return [
                'success' => true,
                'redirect' => $result->getTargetUrl(),
            ];
        }

        // Handle Responsable (e.g., Filament's Redirect)
        if ($result instanceof \Illuminate\Contracts\Support\Responsable) {
            $response = $result->toResponse(request());

            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                return [
                    'success' => true,
                    'redirect' => $response->getTargetUrl(),
                ];
            }
        }

        // Handle string URL
        if (is_string($result)) {
            return [
                'success' => true,
                'redirect' => $result,
            ];
        }

        // Handle array (already in correct format)
        if (is_array($result)) {
            return $result;
        }

        // Default: just close modal
        return ['success' => true];
    }
}
