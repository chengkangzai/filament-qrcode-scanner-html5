<?php

namespace CCK\FilamentQrcodeScannerHtml5;

use CCK\FilamentQrcodeScannerHtml5\Concerns\HasScannerConfiguration;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Closure;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class BarcodeScannerAction extends Action
{
    use HasScannerConfiguration;

    /**
     * JavaScript function string to transform the scanned value.
     * Function signature: (value: string, formatId: number) => string
     */
    protected ?string $stateModifierJs = null;

    /**
     * PHP closure to transform the scanned value.
     * Function signature: fn (string $value, ?BarcodeFormat $format) => string
     *
     * @var Closure(string, ?BarcodeFormat): string|null
     */
    protected ?Closure $stateModifierPhp = null;

    public static function getDefaultName(): ?string
    {
        return 'barcode-scanner';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-camera')
            ->color('gray')
            ->modalHeading(__('Scan Barcode'))
            ->modalWidth('md')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Close'))
            ->modalContent(function (): HtmlString {
                $component = $this->getComponent();
                $statePath = $component?->getStatePath();

                return new HtmlString(
                    view('filament-qrcode-scanner-html5::barcode-scanner-modal', [
                        'statePath' => $statePath,
                        'hasPhpModifier' => $this->stateModifierPhp !== null,
                        'stateModifierJs' => $this->stateModifierJs,
                        'supportedFormats' => $this->getHtml5QrcodeFormatIds(),
                        'scannerConfig' => $this->getScannerConfig(),
                        'labels' => $this->getLabels(),
                    ])->render()
                );
            });
    }

    /**
     * Set a JavaScript function to transform the scanned value before setting it.
     *
     * The function receives the scanned value and the html5-qrcode format ID.
     *
     * Example usage:
     * ->modifyStateUsingJs("(value, formatId) => value.replace(/^0+/, '')")
     * ->modifyStateUsingJs("(value, formatId) => formatId === 8 && value.startsWith('0') ? value.slice(1) : value")
     *
     * Format IDs: QR=0, PDF417=10, Code39=4, Code128=6, DataMatrix=12, ITF=8
     */
    public function modifyStateUsingJs(?string $jsFunction): static
    {
        $this->stateModifierJs = $jsFunction;

        return $this;
    }

    /**
     * Set a PHP closure to transform the scanned value before setting it.
     *
     * The closure receives the scanned value and the BarcodeFormat enum.
     *
     * Example usage:
     * ->modifyStateUsing(fn (string $value, ?BarcodeFormat $format) => ltrim($value, '0'))
     * ->modifyStateUsing(fn (string $value, ?BarcodeFormat $format) =>
     *     $format === BarcodeFormat::ITF && str_starts_with($value, '0')
     *         ? substr($value, 1)
     *         : $value
     * )
     *
     * @param  Closure(string, ?BarcodeFormat): string|null  $callback
     */
    public function modifyStateUsing(?Closure $callback): static
    {
        $this->stateModifierPhp = $callback;

        return $this;
    }

    /**
     * @return Closure(string, ?BarcodeFormat): string|null
     */
    public function getStateModifierPhp(): ?Closure
    {
        return $this->stateModifierPhp;
    }
}
