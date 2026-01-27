<?php

namespace CCK\FilamentQrcodeScannerHtml5;

use CCK\FilamentQrcodeScannerHtml5\Concerns\HasScannerConfiguration;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Closure;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class BarcodeScannerHeaderAction extends Action
{
    use HasScannerConfiguration;

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
                        'scannerConfig' => $this->getScannerConfig(),
                        'labels' => $this->getLabels(),
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
}
