<?php

use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerHeaderAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

it('has correct default name', function () {
    expect(BarcodeScannerHeaderAction::getDefaultName())->toBe('barcode-scanner-header');
});

it('returns all formats when none specified', function () {
    $action = BarcodeScannerHeaderAction::make();

    expect($action->getSupportedFormats())->toBe(BarcodeFormat::cases());
});

it('can set supported formats', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->supportedFormats([BarcodeFormat::QRCode, BarcodeFormat::Code128]);

    expect($action->getSupportedFormats())
        ->toHaveCount(2)
        ->toContain(BarcodeFormat::QRCode)
        ->toContain(BarcodeFormat::Code128);
});

it('can set single supported format', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->supportedFormats([BarcodeFormat::ITF]);

    expect($action->getSupportedFormats())
        ->toHaveCount(1)
        ->toContain(BarcodeFormat::ITF);
});

it('can chain configuration methods', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Permission denied');

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can set afterScan callback', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->afterScan(fn (string $value, ?BarcodeFormat $format) => [
            'success' => true,
            'redirect' => '/users/' . $value,
        ]);

    expect($action->getAfterScanCallback())->toBeInstanceOf(Closure::class);
});

it('can set null for afterScan callback', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->afterScan(null);

    expect($action->getAfterScanCallback())->toBeNull();
});

it('afterScan callback receives value and format', function () {
    $receivedValue = null;
    $receivedFormat = null;

    $action = BarcodeScannerHeaderAction::make()
        ->afterScan(function (string $value, ?BarcodeFormat $format) use (&$receivedValue, &$receivedFormat) {
            $receivedValue = $value;
            $receivedFormat = $format;

            return ['success' => true];
        });

    $callback = $action->getAfterScanCallback();
    $callback('TEST-123', BarcodeFormat::QRCode);

    expect($receivedValue)->toBe('TEST-123');
    expect($receivedFormat)->toBe(BarcodeFormat::QRCode);
});

it('afterScan callback can return redirect', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->afterScan(fn (string $value, ?BarcodeFormat $format) => [
            'success' => true,
            'redirect' => '/dashboard',
        ]);

    $callback = $action->getAfterScanCallback();
    $result = $callback('TEST', null);

    expect($result)->toHaveKey('redirect', '/dashboard');
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

    $action = BarcodeScannerHeaderAction::make()
        ->supportedFormats($commonFormats);

    expect($action->getSupportedFormats())->toBe($commonFormats);
});

it('can set fps', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->fps(20);

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can set qrbox', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->qrbox(250);

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can set rectangular qrbox', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->qrbox(300, 200);

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can set aspect ratio', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->aspectRatio(1.777778);

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can prefer back camera', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->preferBackCamera();

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can prefer front camera', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->preferFrontCamera();

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can set facing mode', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->facingMode('environment');

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});

it('can chain all configuration methods with afterScan', function () {
    $action = BarcodeScannerHeaderAction::make()
        ->fps(15)
        ->qrbox(250, 250)
        ->aspectRatio(1.5)
        ->preferBackCamera()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->afterScan(fn (string $value) => $value)
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Denied');

    expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
});
