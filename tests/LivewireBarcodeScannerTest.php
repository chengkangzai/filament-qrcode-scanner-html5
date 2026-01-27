<?php

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use CCK\FilamentQrcodeScannerHtml5\Livewire\BarcodeScanner;
use Livewire\Livewire;

it('can render livewire component', function () {
    Livewire::test(BarcodeScanner::class)
        ->assertOk();
});

it('handles scan with value and format', function () {
    $component = Livewire::test(BarcodeScanner::class);

    $component->call('handleScan', 'TEST-123', 0);

    expect($component->get('value'))->toBe('TEST-123')
        ->and($component->get('formatId'))->toBe(0);
});

it('dispatches barcode-scanned event on scan', function () {
    Livewire::test(BarcodeScanner::class)
        ->call('handleScan', 'TEST-456', 6)
        ->assertDispatched('barcode-scanned');
});

it('executes onScan callback when set', function () {
    $callbackExecuted = false;
    $receivedValue = null;
    $receivedFormat = null;

    $scanner = new BarcodeScanner();
    $scanner->onScan(function ($value, $format) use (&$callbackExecuted, &$receivedValue, &$receivedFormat) {
        $callbackExecuted = true;
        $receivedValue = $value;
        $receivedFormat = $format;
    });

    $scanner->handleScan('QR-DATA', 0);

    expect($callbackExecuted)->toBeTrue()
        ->and($receivedValue)->toBe('QR-DATA')
        ->and($receivedFormat)->toBe(BarcodeFormat::QRCode);
});

it('handles error and dispatches event', function () {
    Livewire::test(BarcodeScanner::class)
        ->call('handleError', 'Permission denied', 'permission_denied')
        ->assertDispatched('barcode-scanner-error');
});

it('executes onError callback when set', function () {
    $callbackExecuted = false;
    $receivedError = null;
    $receivedType = null;

    $scanner = new BarcodeScanner();
    $scanner->onError(function ($error, $type) use (&$callbackExecuted, &$receivedError, &$receivedType) {
        $callbackExecuted = true;
        $receivedError = $error;
        $receivedType = $type;
    });

    $scanner->handleError('Camera unavailable', 'camera_unavailable');

    expect($callbackExecuted)->toBeTrue()
        ->and($receivedError)->toBe('Camera unavailable')
        ->and($receivedType)->toBe('camera_unavailable');
});

it('can chain configuration methods', function () {
    $scanner = (new BarcodeScanner())
        ->fps(20)
        ->qrbox(250)
        ->preferBackCamera()
        ->supportedFormats([BarcodeFormat::QRCode]);

    expect($scanner)->toBeInstanceOf(BarcodeScanner::class);
});

it('generates unique scanner id', function () {
    $scanner1 = new BarcodeScanner();
    $scanner1->mount();

    $scanner2 = new BarcodeScanner();
    $scanner2->mount();

    expect($scanner1->getId())->not->toBe($scanner2->getId());
});

it('can set fps', function () {
    $scanner = (new BarcodeScanner())->fps(15);

    expect($scanner)->toBeInstanceOf(BarcodeScanner::class);
});

it('can set aspect ratio', function () {
    $scanner = (new BarcodeScanner())->aspectRatio(1.777778);

    expect($scanner)->toBeInstanceOf(BarcodeScanner::class);
});

it('can prefer back camera', function () {
    $scanner = (new BarcodeScanner())->preferBackCamera();

    expect($scanner)->toBeInstanceOf(BarcodeScanner::class);
});
