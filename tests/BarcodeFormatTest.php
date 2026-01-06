<?php

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

it('has correct format ids for all barcode types', function () {
    expect(BarcodeFormat::QRCode->value)->toBe(0)
        ->and(BarcodeFormat::Aztec->value)->toBe(1)
        ->and(BarcodeFormat::Codabar->value)->toBe(2)
        ->and(BarcodeFormat::Code39->value)->toBe(4)
        ->and(BarcodeFormat::Code93->value)->toBe(5)
        ->and(BarcodeFormat::Code128->value)->toBe(6)
        ->and(BarcodeFormat::ITF->value)->toBe(8)
        ->and(BarcodeFormat::Ean13->value)->toBe(9)
        ->and(BarcodeFormat::Pdf417->value)->toBe(10)
        ->and(BarcodeFormat::Ean8->value)->toBe(11)
        ->and(BarcodeFormat::DataMatrix->value)->toBe(12)
        ->and(BarcodeFormat::UpcA->value)->toBe(14)
        ->and(BarcodeFormat::UpcE->value)->toBe(15);
});

it('returns correct labels for all barcode formats', function () {
    expect(BarcodeFormat::QRCode->getLabel())->toBe('QR Code')
        ->and(BarcodeFormat::Aztec->getLabel())->toBe('Aztec')
        ->and(BarcodeFormat::Codabar->getLabel())->toBe('Codabar')
        ->and(BarcodeFormat::Code39->getLabel())->toBe('Code 39')
        ->and(BarcodeFormat::Code93->getLabel())->toBe('Code 93')
        ->and(BarcodeFormat::Code128->getLabel())->toBe('Code 128')
        ->and(BarcodeFormat::ITF->getLabel())->toBe('ITF (Interleaved 2 of 5)')
        ->and(BarcodeFormat::Ean13->getLabel())->toBe('EAN-13')
        ->and(BarcodeFormat::Ean8->getLabel())->toBe('EAN-8')
        ->and(BarcodeFormat::Pdf417->getLabel())->toBe('PDF417')
        ->and(BarcodeFormat::DataMatrix->getLabel())->toBe('Data Matrix')
        ->and(BarcodeFormat::UpcA->getLabel())->toBe('UPC-A')
        ->and(BarcodeFormat::UpcE->getLabel())->toBe('UPC-E');
});

it('can create format from html5-qrcode format id', function () {
    expect(BarcodeFormat::fromHtml5QrcodeFormat(0))->toBe(BarcodeFormat::QRCode)
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(6))->toBe(BarcodeFormat::Code128)
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(8))->toBe(BarcodeFormat::ITF)
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(10))->toBe(BarcodeFormat::Pdf417)
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(12))->toBe(BarcodeFormat::DataMatrix);
});

it('returns null for invalid format id', function () {
    expect(BarcodeFormat::fromHtml5QrcodeFormat(999))->toBeNull()
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(-1))->toBeNull()
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(3))->toBeNull() // 3 is not a valid format
        ->and(BarcodeFormat::fromHtml5QrcodeFormat(7))->toBeNull(); // 7 is not a valid format
});

it('has all expected cases', function () {
    $cases = BarcodeFormat::cases();

    expect($cases)->toHaveCount(13)
        ->and($cases)->toContain(BarcodeFormat::QRCode)
        ->and($cases)->toContain(BarcodeFormat::Aztec)
        ->and($cases)->toContain(BarcodeFormat::Codabar)
        ->and($cases)->toContain(BarcodeFormat::Code39)
        ->and($cases)->toContain(BarcodeFormat::Code93)
        ->and($cases)->toContain(BarcodeFormat::Code128)
        ->and($cases)->toContain(BarcodeFormat::DataMatrix)
        ->and($cases)->toContain(BarcodeFormat::ITF)
        ->and($cases)->toContain(BarcodeFormat::Ean13)
        ->and($cases)->toContain(BarcodeFormat::Ean8)
        ->and($cases)->toContain(BarcodeFormat::Pdf417)
        ->and($cases)->toContain(BarcodeFormat::UpcA)
        ->and($cases)->toContain(BarcodeFormat::UpcE);
});
