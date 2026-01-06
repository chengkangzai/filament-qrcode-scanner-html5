<?php

namespace Pixalink\FilamentQrcodeScannerHtml5\Enums;

/**
 * Barcode formats supported by the html5-qrcode library.
 *
 * @see https://github.com/mebjas/html5-qrcode
 */
enum BarcodeFormat: int
{
    case QRCode = 0;
    case Aztec = 1;
    case Codabar = 2;
    case Code39 = 4;
    case Code93 = 5;
    case Code128 = 6;
    case DataMatrix = 12;
    case ITF = 8;
    case Ean13 = 9;
    case Ean8 = 11;
    case Pdf417 = 10;
    case UpcA = 14;
    case UpcE = 15;

    public function getLabel(): string
    {
        return match ($this) {
            self::QRCode => 'QR Code',
            self::Aztec => 'Aztec',
            self::Codabar => 'Codabar',
            self::Code39 => 'Code 39',
            self::Code93 => 'Code 93',
            self::Code128 => 'Code 128',
            self::DataMatrix => 'Data Matrix',
            self::ITF => 'ITF (Interleaved 2 of 5)',
            self::Ean13 => 'EAN-13',
            self::Ean8 => 'EAN-8',
            self::Pdf417 => 'PDF417',
            self::UpcA => 'UPC-A',
            self::UpcE => 'UPC-E',
        };
    }

    public static function fromHtml5QrcodeFormat(int $format): ?self
    {
        return self::tryFrom($format);
    }
}
