<?php

use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerCallbackRegistry;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('registers a callback and returns a uuid', function () {
    $callback = fn (string $value, ?BarcodeFormat $format) => $value;

    $id = BarcodeScannerCallbackRegistry::register($callback);

    expect($id)->toBeString()
        ->and($id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('stores callback in cache', function () {
    $callback = fn (string $value, ?BarcodeFormat $format) => $value;

    $id = BarcodeScannerCallbackRegistry::register($callback);

    expect(BarcodeScannerCallbackRegistry::has($id))->toBeTrue();
});

it('executes callback and returns modified value', function () {
    $callback = fn (string $value, ?BarcodeFormat $format) => strtoupper($value);

    $id = BarcodeScannerCallbackRegistry::register($callback);
    $result = BarcodeScannerCallbackRegistry::execute($id, 'test', null);

    expect($result)->toBe('TEST');
});

it('executes callback with barcode format', function () {
    $callback = fn (string $value, ?BarcodeFormat $format) => $format === BarcodeFormat::ITF
        ? ltrim($value, '0')
        : $value;

    $id = BarcodeScannerCallbackRegistry::register($callback);
    $result = BarcodeScannerCallbackRegistry::execute($id, '00123', BarcodeFormat::ITF);

    expect($result)->toBe('123');
});

it('returns original value when callback not found', function () {
    $result = BarcodeScannerCallbackRegistry::execute('non-existent-id', 'test', null);

    expect($result)->toBe('test');
});

it('removes callback from cache after execution', function () {
    $callback = fn (string $value, ?BarcodeFormat $format) => $value;

    $id = BarcodeScannerCallbackRegistry::register($callback);

    expect(BarcodeScannerCallbackRegistry::has($id))->toBeTrue();

    BarcodeScannerCallbackRegistry::execute($id, 'test', null);

    expect(BarcodeScannerCallbackRegistry::has($id))->toBeFalse();
});

it('can register multiple callbacks independently', function () {
    $callback1 = fn (string $value, ?BarcodeFormat $format) => $value . '_1';
    $callback2 = fn (string $value, ?BarcodeFormat $format) => $value . '_2';

    $id1 = BarcodeScannerCallbackRegistry::register($callback1);
    $id2 = BarcodeScannerCallbackRegistry::register($callback2);

    expect($id1)->not->toBe($id2)
        ->and(BarcodeScannerCallbackRegistry::has($id1))->toBeTrue()
        ->and(BarcodeScannerCallbackRegistry::has($id2))->toBeTrue();

    $result1 = BarcodeScannerCallbackRegistry::execute($id1, 'test', null);
    $result2 = BarcodeScannerCallbackRegistry::execute($id2, 'test', null);

    expect($result1)->toBe('test_1')
        ->and($result2)->toBe('test_2');
});

it('handles complex closure with external dependencies', function () {
    $prefix = 'MY_';
    $callback = fn (string $value, ?BarcodeFormat $format) => $prefix . $value;

    $id = BarcodeScannerCallbackRegistry::register($callback);
    $result = BarcodeScannerCallbackRegistry::execute($id, '12345', null);

    expect($result)->toBe('MY_12345');
});

it('preserves barcode format in callback execution', function () {
    // Test that the format is passed correctly by using it to modify the value
    $callback = fn (string $value, ?BarcodeFormat $format) => $value . ':' . ($format?->name ?? 'null');

    $id = BarcodeScannerCallbackRegistry::register($callback);
    $result = BarcodeScannerCallbackRegistry::execute($id, 'test', BarcodeFormat::QRCode);

    expect($result)->toBe('test:QRCode');
});

it('handles null format in callback', function () {
    // Test that null format is handled correctly
    $callback = fn (string $value, ?BarcodeFormat $format) => $value . ':' . ($format?->name ?? 'none');

    $id = BarcodeScannerCallbackRegistry::register($callback);
    $result = BarcodeScannerCallbackRegistry::execute($id, 'test', null);

    expect($result)->toBe('test:none');
});
