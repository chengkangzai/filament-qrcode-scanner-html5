<?php

namespace Pixalink\FilamentQrcodeScannerHtml5;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\HtmlString;
use Pixalink\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

class BarcodeScannerAction extends Action
{
    /** @var array<BarcodeFormat> */
    protected array $supportedFormats = [];

    protected string $switchCameraLabel = 'Switch Camera';

    protected string $cameraUnavailableMessage = 'Camera is not available. Please check your device settings.';

    protected string $permissionDeniedMessage = 'Camera permission was denied. Please allow camera access to scan barcodes.';

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

                $callbackId = null;
                if ($this->stateModifierPhp) {
                    $callbackId = BarcodeScannerCallbackRegistry::register($this->stateModifierPhp);
                }

                return new HtmlString(
                    view('filament-qrcode-scanner-html5::barcode-scanner-modal', [
                        'statePath' => $statePath,
                        'callbackId' => $callbackId,
                        'stateModifierJs' => $this->stateModifierJs,
                        'supportedFormats' => $this->getHtml5QrcodeFormatIds(),
                        'switchCameraLabel' => __($this->switchCameraLabel),
                        'cameraUnavailableMessage' => __($this->cameraUnavailableMessage),
                        'permissionDeniedMessage' => __($this->permissionDeniedMessage),
                    ])->render()
                );
            });
    }

    /**
     * Set supported barcode formats for scanning.
     *
     * @param  array<BarcodeFormat>  $formats
     */
    public function supportedFormats(array $formats): static
    {
        $this->supportedFormats = $formats;

        return $this;
    }

    /**
     * @return array<BarcodeFormat>
     */
    public function getSupportedFormats(): array
    {
        if (empty($this->supportedFormats)) {
            return BarcodeFormat::cases();
        }

        return $this->supportedFormats;
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

    public function switchCameraLabel(string $label): static
    {
        $this->switchCameraLabel = $label;

        return $this;
    }

    public function cameraUnavailableMessage(string $message): static
    {
        $this->cameraUnavailableMessage = $message;

        return $this;
    }

    public function permissionDeniedMessage(string $message): static
    {
        $this->permissionDeniedMessage = $message;

        return $this;
    }

    /**
     * @return array<int>
     */
    protected function getHtml5QrcodeFormatIds(): array
    {
        return array_map(
            fn (BarcodeFormat $format) => $format->value,
            $this->getSupportedFormats()
        );
    }
}
