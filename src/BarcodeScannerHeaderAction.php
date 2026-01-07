<?php

namespace CCK\FilamentQrcodeScannerHtml5;

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Closure;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class BarcodeScannerHeaderAction extends Action
{
    /** @var array<BarcodeFormat> */
    protected array $supportedFormats = [];

    protected string $switchCameraLabel = 'Switch Camera';

    protected string $cameraUnavailableMessage = 'Camera is not available. Please check your device settings.';

    protected string $permissionDeniedMessage = 'Camera permission was denied. Please allow camera access to scan barcodes.';

    /** @var Closure(string, ?BarcodeFormat): mixed|null */
    protected ?Closure $afterScanCallback = null;

    public static function getDefaultName(): ?string
    {
        return 'barcode-scanner-header';
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
                return new HtmlString(
                    view('filament-qrcode-scanner-html5::barcode-scanner-modal', [
                        'statePath' => null,
                        'isHeaderAction' => true,
                        'hasPhpModifier' => $this->afterScanCallback !== null,
                        'stateModifierJs' => null,
                        'supportedFormats' => $this->getHtml5QrcodeFormatIds(),
                        'switchCameraLabel' => __($this->switchCameraLabel),
                        'cameraUnavailableMessage' => __($this->cameraUnavailableMessage),
                        'permissionDeniedMessage' => __($this->permissionDeniedMessage),
                    ])->render()
                );
            });
    }

    /**
     * Set the callback to execute after a barcode is scanned.
     *
     * The callback receives the scanned value and the BarcodeFormat enum.
     * It can return:
     * - A RedirectResponse: redirect('/users/1') or redirect()->route('users.show', $user)
     * - A string URL: '/users/1'
     * - An array: ['success' => true, 'redirect' => '/users/1']
     * - null/void: just closes the modal
     *
     * Example:
     * ->afterScan(function (string $value, ?BarcodeFormat $format) {
     *     $user = User::where('qr_code', $value)->first();
     *     return redirect()->route('users.show', $user);
     * })
     *
     * @param  Closure(string, ?BarcodeFormat): mixed|null  $callback
     */
    public function afterScan(?Closure $callback): static
    {
        $this->afterScanCallback = $callback;

        return $this;
    }

    /**
     * @return Closure(string, ?BarcodeFormat): mixed|null
     */
    public function getAfterScanCallback(): ?Closure
    {
        return $this->afterScanCallback;
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
