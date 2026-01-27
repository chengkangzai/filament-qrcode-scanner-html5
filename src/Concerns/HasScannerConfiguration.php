<?php

namespace CCK\FilamentQrcodeScannerHtml5\Concerns;

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

trait HasScannerConfiguration
{
    /** @var array<BarcodeFormat> */
    protected array $supportedFormats = [];

    protected int $fps = 10;

    protected ?int $qrboxWidth = null;

    protected ?int $qrboxHeight = null;

    protected ?float $aspectRatio = null;

    protected ?string $facingMode = null;

    protected string $switchCameraLabel = 'Switch Camera';

    protected string $cameraUnavailableMessage = 'Camera is not available. Please check your device settings.';

    protected string $permissionDeniedMessage = 'Camera permission was denied. Please allow camera access to scan barcodes.';

    /**
     * Set the frames per second for scanning.
     *
     * @param  int  $fps  Frames per second (1-30)
     */
    public function fps(int $fps): static
    {
        if ($fps < 1 || $fps > 30) {
            throw new \InvalidArgumentException('FPS must be between 1 and 30');
        }

        $this->fps = $fps;

        return $this;
    }

    /**
     * Set the qrbox (focus area) dimensions.
     *
     * @param  int  $width  Width of the qrbox
     * @param  int|null  $height  Height of the qrbox (defaults to width for square)
     */
    public function qrbox(int $width, ?int $height = null): static
    {
        if ($width < 1) {
            throw new \InvalidArgumentException('Qrbox width must be positive');
        }

        if ($height !== null && $height < 1) {
            throw new \InvalidArgumentException('Qrbox height must be positive');
        }

        $this->qrboxWidth = $width;
        $this->qrboxHeight = $height ?? $width;

        return $this;
    }

    /**
     * Set the aspect ratio for the camera feed.
     *
     * @param  float  $ratio  Aspect ratio (e.g., 1.777778 for 16:9)
     */
    public function aspectRatio(float $ratio): static
    {
        if ($ratio <= 0) {
            throw new \InvalidArgumentException('Aspect ratio must be positive');
        }

        $this->aspectRatio = $ratio;

        return $this;
    }

    /**
     * Set the camera facing mode.
     *
     * @param  string  $mode  'user' for front camera or 'environment' for back camera
     */
    public function facingMode(string $mode): static
    {
        if (! in_array($mode, ['user', 'environment'])) {
            throw new \InvalidArgumentException("Facing mode must be 'user' or 'environment'");
        }

        $this->facingMode = $mode;

        return $this;
    }

    /**
     * Prefer the back camera (environment facing).
     */
    public function preferBackCamera(): static
    {
        return $this->facingMode('environment');
    }

    /**
     * Prefer the front camera (user facing).
     */
    public function preferFrontCamera(): static
    {
        return $this->facingMode('user');
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
     * Get scanner configuration for html5-qrcode library.
     *
     * @return array{
     *     fps: int,
     *     qrbox?: array{width: int, height: int},
     *     aspectRatio?: float,
     *     facingMode?: string
     * }
     */
    protected function getScannerConfig(): array
    {
        $config = [
            'fps' => $this->fps,
        ];

        if ($this->qrboxWidth !== null && $this->qrboxHeight !== null) {
            $config['qrbox'] = [
                'width' => $this->qrboxWidth,
                'height' => $this->qrboxHeight,
            ];
        }

        if ($this->aspectRatio !== null) {
            $config['aspectRatio'] = $this->aspectRatio;
        }

        if ($this->facingMode !== null) {
            $config['facingMode'] = $this->facingMode;
        }

        return $config;
    }

    /**
     * Get UI labels for the scanner.
     *
     * @return array{
     *     switchCamera: string,
     *     cameraUnavailable: string,
     *     permissionDenied: string
     * }
     */
    protected function getLabels(): array
    {
        return [
            'switchCamera' => __($this->switchCameraLabel),
            'cameraUnavailable' => __($this->cameraUnavailableMessage),
            'permissionDenied' => __($this->permissionDeniedMessage),
        ];
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
