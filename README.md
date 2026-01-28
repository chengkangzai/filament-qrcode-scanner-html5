# Filament QR Code Scanner (html5-qrcode)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)
[![Tests](https://github.com/chengkangzai/filament-qrcode-scanner-html5/actions/workflows/tests.yml/badge.svg)](https://github.com/chengkangzai/filament-qrcode-scanner-html5/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)
[![License](https://img.shields.io/packagist/l/chengkangzai/filament-qrcode-scanner-html5.svg?style=flat-square)](https://packagist.org/packages/chengkangzai/filament-qrcode-scanner-html5)

A Filament form action for scanning barcodes and QR codes using the device camera. Built with the [html5-qrcode](https://github.com/mebjas/html5-qrcode) library.

## Features

- **Three-tier architecture**: Use as Filament action, standalone Livewire component, or pure Alpine.js
- Scan QR codes and various barcode formats (Code128, Code39, EAN-13, UPC-A, ITF, and more)
- Works on mobile and desktop devices with camera access
- **Configurable scanner options**: fps, qrbox, aspect ratio, camera facing mode
- Automatic camera switching (front/back) or force specific camera
- Transform scanned values with PHP closures or JavaScript functions
- **Header action support** for standalone scanning (e.g., attendance check-in)
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

### Header Action (Standalone Scanning)

Use `BarcodeScannerHeaderAction` for standalone scanning without a form field, such as attendance check-in or inventory lookup:

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerHeaderAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Filament\Notifications\Notification;

// In your Filament Resource page (ListRecords, ViewRecord, etc.)
protected function getHeaderActions(): array
{
    return [
        BarcodeScannerHeaderAction::make()
            ->label('Scan Attendance')
            ->afterScan(function (string $value, ?BarcodeFormat $format) {
                $user = User::where('qr_code', $value)->first();

                if (! $user) {
                    Notification::make()
                        ->title('User not found')
                        ->danger()
                        ->send();

                    return null; // Just close the modal
                }

                // Mark attendance
                $user->attendances()->create(['checked_in_at' => now()]);

                // Redirect to user page
                return redirect()->route('filament.admin.resources.users.view', $user);
            }),
    ];
}
```

The `afterScan` callback can return:
- `redirect('/url')` or `redirect()->route('name', $params)` - Redirect to a URL
- `'/url'` - String URL to redirect
- `null` - Just close the modal (useful after showing a notification)

### Scanner Configuration Options

Configure scanner behavior with these options (available on all actions):

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->fps(15)                          // Frames per second (1-30, default: 10)
            ->qrbox(250)                       // Square focus box (250x250)
            ->qrbox(300, 200)                  // Rectangle focus box (300x200)
            ->aspectRatio(1.777778)            // Camera aspect ratio (16:9)
            ->preferBackCamera()               // Force back/environment camera
            ->preferFrontCamera()              // Force front/user camera
            ->facingMode('environment')        // Alternative: 'user' or 'environment'
            ->supportedFormats([...])          // Limit barcode formats
            ->iconOnly()                       // Show only icon (no text label)
            ->controlPosition('center')        // Align controls: 'left', 'center', 'right'
            ->hideCameraName()                 // Hide camera name label
    )
```

#### Configuration Options Reference

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `fps(int)` | 1-30 | 10 | Frames per second for scanning |
| `qrbox(int, ?int)` | pixels | null | Focus box dimensions (width, height) |
| `aspectRatio(float)` | ratio | null | Camera feed aspect ratio |
| `facingMode(string)` | 'user'\|'environment' | null | Camera facing mode |
| `preferBackCamera()` | - | - | Alias for `facingMode('environment')` |
| `preferFrontCamera()` | - | - | Alias for `facingMode('user')` |
| `controlButtonStyle(string)` | 'icon'\|'icon-text' | 'icon-text' | Switch camera button display style |
| `iconOnly()` | - | - | Convenience for `controlButtonStyle('icon')` |
| `iconWithText()` | - | - | Convenience for `controlButtonStyle('icon-text')` |
| `controlPosition(string)` | 'left'\|'center'\|'right' | 'left' | Controls alignment |
| `showCameraName(bool)` | boolean | true | Show/hide camera name label |
| `hideCameraName()` | - | - | Convenience for `showCameraName(false)` |

### Standalone Usage (Without Filament)

This package can be used outside of Filament panels for custom integrations.

#### Livewire Component

Use the standalone Livewire component in any Laravel application:

```php
// In your Livewire component
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

class CheckInPage extends Component
{
    public $scannedValue = null;

    public function handleScan($value, $formatId)
    {
        $this->scannedValue = $value;
        $format = BarcodeFormat::fromHtml5QrcodeFormat($formatId);

        // Your custom logic here
        $this->processCheckIn($value);
    }

    public function render()
    {
        return view('livewire.check-in-page');
    }
}
```

```blade
{{-- In your blade view --}}
<div>
    <livewire:barcode-scanner
        wire:key="scanner"
        @barcode-scanned.window="$wire.handleScan($event.detail.value, $event.detail.formatId)"
    />

    @if($scannedValue)
        <p>Scanned: {{ $scannedValue }}</p>
    @endif
</div>
```

#### Pure Alpine.js Component

Use the base component without any framework dependencies:

```blade
<x-filament-qrcode-scanner-html5::barcode-scanner
    id="my-scanner"
    :config="[
        'fps' => 15,
        'qrbox' => ['width' => 250, 'height' => 250],
        'aspectRatio' => 1.777778,
        'facingMode' => 'environment'
    ]"
    :supported-formats="[0, 6, 8]"  {{-- QR Code, Code128, ITF --}}
    :labels="[
        'switchCamera' => 'Toggle Camera',
        'cameraUnavailable' => 'No camera found',
        'permissionDenied' => 'Camera access denied'
    ]"
    @barcode-scanned.window="handleScan($event.detail)"
    @barcode-scanner-error.window="handleError($event.detail)"
/>

<script>
function handleScan(detail) {
    console.log('Scanned value:', detail.value);
    console.log('Format:', detail.formatName);
    console.log('Format ID:', detail.formatId);
    console.log('Timestamp:', detail.timestamp);
    console.log('Scanner ID:', detail.scannerId);
}

function handleError(detail) {
    console.error('Scanner error:', detail.error);
    console.log('Error type:', detail.errorType);
}
</script>
```

#### Browser Events

The Alpine.js component emits these browser events:

| Event | Detail Properties | Description |
|-------|-------------------|-------------|
| `barcode-scanned` | `value`, `formatId`, `formatName`, `scannerId`, `timestamp` | Barcode successfully scanned |
| `barcode-scanner-error` | `error`, `errorType`, `scannerId`, `timestamp` | Scanner error occurred |
| `barcode-scanner-ready` | `cameraCount`, `currentCamera`, `scannerId`, `timestamp` | Scanner initialized |
| `barcode-scanner-stopped` | `scannerId`, `timestamp` | Scanner stopped |

Error types: `'not_supported'`, `'overconstrained'`, `'permission_denied'`, `'camera_unavailable'`, `'unknown'`

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

If you discover any security-related issues, please email pycck@hotmail.com instead of using the issue tracker.

## Credits

- [CCK](https://github.com/chengkangzai)
- [html5-qrcode](https://github.com/mebjas/html5-qrcode) by Minhaz

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
