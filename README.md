# Filament QR Code Scanner (html5-qrcode)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)
[![Total Downloads](https://img.shields.io/packagist/dt/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)
[![License](https://img.shields.io/packagist/l/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)

A Filament form action for scanning barcodes and QR codes using the device camera. Built with the [html5-qrcode](https://github.com/mebjas/html5-qrcode) library.

## Features

- Scan QR codes and various barcode formats (Code128, Code39, EAN-13, UPC-A, ITF, and more)
- Works on mobile and desktop devices with camera access
- Automatic camera switching (front/back)
- Transform scanned values with PHP closures or JavaScript functions
- Fully customizable labels and error messages
- Dark mode support
- Accessible with ARIA labels

## Requirements

- PHP 8.1+
- Laravel 10+
- Filament 3.x
- Livewire 3.x

## Installation

Install the package via Composer:

```bash
composer require chengkangzai/filament-qrcode-scanner-html5
```

The package will auto-register its service provider.

## Usage

### Basic Usage

Add the `BarcodeScannerAction` as a suffix action to any text input:

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;

TextInput::make('barcode')
    ->suffixAction(BarcodeScannerAction::make())
```

### Limit Supported Formats

Restrict scanning to specific barcode formats:

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

TextInput::make('product_code')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->supportedFormats([
                BarcodeFormat::QRCode,
                BarcodeFormat::Code128,
                BarcodeFormat::Ean13,
            ])
    )
```

### Transform Scanned Values (PHP)

Use a PHP closure to transform the scanned value server-side:

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->modifyStateUsing(fn (string $value, ?BarcodeFormat $format) =>
                // Strip leading zeros from ITF barcodes
                $format === BarcodeFormat::ITF
                    ? ltrim($value, '0')
                    : $value
            )
    )
```

### Transform Scanned Values (JavaScript)

Use a JavaScript function to transform the scanned value client-side:

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;

TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->modifyStateUsingJs("(value, formatId) => value.replace(/^0+/, '')")
    )
```

Format IDs for JavaScript: QR=0, PDF417=10, Code39=4, Code128=6, DataMatrix=12, ITF=8

### Customize Labels and Messages

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;

TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->switchCameraLabel('Toggle Camera')
            ->cameraUnavailableMessage('No camera detected on this device.')
            ->permissionDeniedMessage('Please allow camera access in your browser settings.')
    )
```

## Supported Barcode Formats

| Format | Enum Value | Format ID |
|--------|------------|-----------|
| QR Code | `BarcodeFormat::QRCode` | 0 |
| Aztec | `BarcodeFormat::Aztec` | 1 |
| Codabar | `BarcodeFormat::Codabar` | 2 |
| Code 39 | `BarcodeFormat::Code39` | 4 |
| Code 93 | `BarcodeFormat::Code93` | 5 |
| Code 128 | `BarcodeFormat::Code128` | 6 |
| ITF | `BarcodeFormat::ITF` | 8 |
| EAN-13 | `BarcodeFormat::Ean13` | 9 |
| PDF417 | `BarcodeFormat::Pdf417` | 10 |
| EAN-8 | `BarcodeFormat::Ean8` | 11 |
| Data Matrix | `BarcodeFormat::DataMatrix` | 12 |
| UPC-A | `BarcodeFormat::UpcA` | 14 |
| UPC-E | `BarcodeFormat::UpcE` | 15 |

## Publishing Views

If you need to customize the scanner modal view:

```bash
php artisan vendor:publish --tag=filament-qrcode-scanner-html5-views
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security-related issues, please email nicholas@pixalink.io instead of using the issue tracker.

## Credits

- [Nicholas Chun](https://github.com/chengkangzai)
- [html5-qrcode](https://github.com/mebjas/html5-qrcode) by Minhaz

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
