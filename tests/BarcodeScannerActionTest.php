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
