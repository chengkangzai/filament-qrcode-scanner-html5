<?php

use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

it('has correct default name', function () {
    expect(BarcodeScannerAction::getDefaultName())->toBe('barcode-scanner');
});

it('returns all formats when none specified', function () {
    $action = BarcodeScannerAction::make();

    expect($action->getSupportedFormats())->toBe(BarcodeFormat::cases());
});

it('can set supported formats', function () {
    $action = BarcodeScannerAction::make()
        ->supportedFormats([BarcodeFormat::QRCode, BarcodeFormat::Code128]);

    expect($action->getSupportedFormats())
        ->toHaveCount(2)
        ->toContain(BarcodeFormat::QRCode)
        ->toContain(BarcodeFormat::Code128);
});

it('can set single supported format', function () {
    $action = BarcodeScannerAction::make()
        ->supportedFormats([BarcodeFormat::ITF]);

    expect($action->getSupportedFormats())
        ->toHaveCount(1)
        ->toContain(BarcodeFormat::ITF);
});

it('can chain configuration methods', function () {
    $action = BarcodeScannerAction::make()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Permission denied');

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set javascript state modifier', function () {
    $action = BarcodeScannerAction::make()
        ->modifyStateUsingJs("(value, formatId) => value.toUpperCase()");

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set php state modifier closure', function () {
    $action = BarcodeScannerAction::make()
        ->modifyStateUsing(fn (string $value, ?BarcodeFormat $format) => strtoupper($value));

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set null for state modifiers', function () {
    $action = BarcodeScannerAction::make()
        ->modifyStateUsingJs(null)
        ->modifyStateUsing(null);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('supports all common barcode formats', function () {
    $commonFormats = [
        BarcodeFormat::QRCode,
        BarcodeFormat::Code128,
        BarcodeFormat::Code39,
        BarcodeFormat::Ean13,
        BarcodeFormat::UpcA,
        BarcodeFormat::ITF,
        BarcodeFormat::Pdf417,
        BarcodeFormat::DataMatrix,
    ];

    $action = BarcodeScannerAction::make()
        ->supportedFormats($commonFormats);

    expect($action->getSupportedFormats())->toBe($commonFormats);
});

it('can set fps', function () {
    $action = BarcodeScannerAction::make()
        ->fps(20);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set qrbox', function () {
    $action = BarcodeScannerAction::make()
        ->qrbox(250);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set rectangular qrbox', function () {
    $action = BarcodeScannerAction::make()
        ->qrbox(300, 200);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set aspect ratio', function () {
    $action = BarcodeScannerAction::make()
        ->aspectRatio(1.777778);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can prefer back camera', function () {
    $action = BarcodeScannerAction::make()
        ->preferBackCamera();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can prefer front camera', function () {
    $action = BarcodeScannerAction::make()
        ->preferFrontCamera();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set facing mode', function () {
    $action = BarcodeScannerAction::make()
        ->facingMode('environment');

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can chain all new configuration methods', function () {
    $action = BarcodeScannerAction::make()
        ->fps(15)
        ->qrbox(250, 250)
        ->aspectRatio(1.5)
        ->preferBackCamera()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Denied');

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set control button style', function () {
    $action = BarcodeScannerAction::make()
        ->controlButtonStyle('icon');

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can use iconOnly convenience method', function () {
    $action = BarcodeScannerAction::make()
        ->iconOnly();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can use iconWithText convenience method', function () {
    $action = BarcodeScannerAction::make()
        ->iconWithText();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can set control position', function () {
    $action = BarcodeScannerAction::make()
        ->controlPosition('center');

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can toggle camera name visibility', function () {
    $action = BarcodeScannerAction::make()
        ->showCameraName(false);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can use hideCameraName convenience method', function () {
    $action = BarcodeScannerAction::make()
        ->hideCameraName();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can chain UI configuration methods', function () {
    $action = BarcodeScannerAction::make()
        ->iconOnly()
        ->controlPosition('center')
        ->hideCameraName();

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});

it('can chain all configuration methods including UI options', function () {
    $action = BarcodeScannerAction::make()
        ->fps(15)
        ->qrbox(250, 250)
        ->aspectRatio(1.5)
        ->preferBackCamera()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Denied')
        ->iconOnly()
        ->controlPosition('right')
        ->showCameraName(true);

    expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
});
