<?php

namespace CCK\FilamentQrcodeScannerHtml5\Livewire;

use CCK\FilamentQrcodeScannerHtml5\Concerns\HasScannerConfiguration;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Closure;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class BarcodeScanner extends Component
{
    use HasScannerConfiguration;

    public ?string $value = null;

    public ?int $formatId = null;

    /** @var Closure(string, ?BarcodeFormat): mixed|null */
    protected ?Closure $onScanCallback = null;

    /** @var Closure(string, string): mixed|null */
    protected ?Closure $onErrorCallback = null;

    protected string $scannerId = '';

    /**
     * Override getErrorBag to ensure it always returns a MessageBag.
     * Fixes Laravel 12 compatibility issue where error bag might be null during tests.
     */
    public function getErrorBag(): MessageBag
    {
        $bag = parent::getErrorBag();

        // If parent returns null (Laravel 12 test environment issue), return empty bag
        if (! $bag instanceof MessageBag) {
            $bag = new MessageBag();
            $this->setErrorBag($bag);
        }

        return $bag;
    }

    public function mount(): void
    {
        if (! $this->scannerId) {
            $this->scannerId = 'livewire-scanner-' . uniqid();
        }
    }

    /**
     * Set the callback to execute when a barcode is scanned.
     *
     * @param  Closure(string, ?BarcodeFormat): mixed  $callback
     */
    public function onScan(?Closure $callback): static
    {
        $this->onScanCallback = $callback;

        return $this;
    }

    /**
     * Set the callback to execute when an error occurs.
     *
     * @param  Closure(string, string): mixed  $callback
     */
    public function onError(?Closure $callback): static
    {
        $this->onErrorCallback = $callback;

        return $this;
    }

    /**
     * Handle the scanned barcode value from the browser event.
     *
     * Note: Browser events are already dispatched by the Alpine.js component.
     * This method primarily handles server-side callbacks.
     */
    public function handleScan(string $value, int $formatId): void
    {
        $this->value = $value;
        $this->formatId = $formatId;

        $format = BarcodeFormat::fromHtml5QrcodeFormat($formatId);

        if ($this->onScanCallback) {
            ($this->onScanCallback)($value, $format);
        }
    }

    /**
     * Handle scanner errors from the browser event.
     *
     * Note: Browser events are already dispatched by the Alpine.js component.
     * This method primarily handles server-side callbacks.
     */
    public function handleError(string $error, string $errorType): void
    {
        if ($this->onErrorCallback) {
            ($this->onErrorCallback)($error, $errorType);
        }
    }

    public function getId(): string
    {
        if (! $this->scannerId) {
            $this->scannerId = 'livewire-scanner-' . uniqid();
        }

        return $this->scannerId;
    }

    public function render()
    {
        return view('filament-qrcode-scanner-html5::livewire.barcode-scanner');
    }
}
