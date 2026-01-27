# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Filament 3 form action for scanning barcodes and QR codes using the device camera. Built with the [html5-qrcode](https://github.com/mebjas/html5-qrcode) JavaScript library.

**Package:** `chengkangzai/filament-qrcode-scanner-html5`
**Namespace:** `CCK\FilamentQrcodeScannerHtml5`

## Commands

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/pest

# Run a single test file
vendor/bin/pest tests/BarcodeScannerActionTest.php

# Run a specific test
vendor/bin/pest --filter="can set supported formats"
```

## Architecture

### Three-Tier Progressive Enhancement

The package uses a three-tier architecture for maximum flexibility:

**Tier 1: Base Alpine.js Component** (`resources/views/components/barcode-scanner.blade.php`)
- Pure Blade component usable anywhere (no Filament/Livewire required)
- Emits browser events: `barcode-scanned`, `barcode-scanner-error`, `barcode-scanner-ready`, `barcode-scanner-stopped`
- All scanner lifecycle management handled internally
- Configurable via props: `id`, `config`, `supportedFormats`, `labels`, `showControls`

**Tier 2: Livewire Component** (`src/Livewire/BarcodeScanner.php`)
- Wraps Tier 1 with Livewire integration
- Server-side callbacks via `onScan()` and `onError()`
- Works without Filament
- Inherits all Tier 1 configuration via `HasScannerConfiguration` trait

**Tier 3: Enhanced Filament Actions**
- Current Actions with added configuration options
- 100% backward compatible
- Uses Tier 1 internally via modal view

### Core Components

- **`BarcodeScannerAction`** (`src/BarcodeScannerAction.php`): Filament form action that extends `Filament\Forms\Components\Actions\Action`. Provides the suffix action button for text inputs with configuration methods for formats, labels, and value transformation.

- **`BarcodeScannerHeaderAction`** (`src/BarcodeScannerHeaderAction.php`): Filament header/page action that extends `Filament\Actions\Action`. Use this for standalone scanning (e.g., attendance check-in) with custom callbacks that can redirect or show notifications.

- **`HasScannerConfiguration`** (`src/Concerns/HasScannerConfiguration.php`): Trait providing scanner configuration methods (fps, qrbox, aspectRatio, facingMode, formats, labels) used by all tiers.

- **`BarcodeScannerServiceProvider`** (`src/BarcodeScannerServiceProvider.php`): Registers views, Livewire component, and Livewire `on('call', ...)` hooks that intercept `processBarcodeScan` (form) and `processBarcodeScanHeader` (header) calls.

- **`BarcodeFormat`** (`src/Enums/BarcodeFormat.php`): Backed enum mapping barcode format names to html5-qrcode format IDs (e.g., `QRCode = 0`, `Code128 = 6`).

### Form Action Value Transformation

Two approaches for transforming scanned values in form actions:

1. **JavaScript (client-side):** `->modifyStateUsingJs("(value, formatId) => ...")` - Executed immediately in the browser via the Blade view's Alpine.js component.

2. **PHP (server-side):** `->modifyStateUsing(fn ($value, $format) => ...)` - Closure is stored on the action instance. When scan completes, the view calls `$wire.processBarcodeScan(statePath, value, formatId)`. The Livewire hook intercepts this, finds the form component by statePath, retrieves the action's callback via `getStateModifierPhp()`, and executes it.

### Header Action Flow

For standalone header actions using `BarcodeScannerHeaderAction`:

1. **`->afterScan(fn ($value, $format) => ...)`** - Callback executes after scan. Can return:
   - `redirect('/url')` or `redirect()->route('name', $params)` - Laravel redirect
   - `'/url'` - String URL
   - `['redirect' => '/url']` - Array format
   - `null` - Just close the modal

2. When scan completes, the view calls `$wire.processBarcodeScanHeader(value, formatId)`. The Livewire hook intercepts this, finds the mounted action, executes the callback, and normalizes the result.

3. If the result contains a redirect URL, the browser redirects. Otherwise, the modal closes.

### Frontend

- **`resources/views/barcode-scanner-modal.blade.php`**: Filament modal wrapper that uses Tier 1 component and handles Filament-specific logic (form state, header actions).

## Configuration Options

All scanner tiers support these configuration methods:

### Scanner Options

- **`fps(int $fps)`**: Frames per second for scanning (1-30, default: 10)
- **`qrbox(int $width, ?int $height = null)`**: Focus box dimensions (square if height omitted)
- **`aspectRatio(float $ratio)`**: Camera aspect ratio (e.g., 1.777778 for 16:9)
- **`facingMode(string $mode)`**: Camera facing mode ('user' or 'environment')
- **`preferBackCamera()`**: Alias for `facingMode('environment')`
- **`preferFrontCamera()`**: Alias for `facingMode('user')`

### Format & Label Options

- **`supportedFormats(array $formats)`**: Array of `BarcodeFormat` enums to scan
- **`switchCameraLabel(string $label)`**: Label for camera switch button
- **`cameraUnavailableMessage(string $message)`**: Error message when camera not found
- **`permissionDeniedMessage(string $message)`**: Error message when permission denied

## Standalone Usage

### Tier 1: Alpine.js Component (No Framework)

```blade
<x-filament-qrcode-scanner-html5::barcode-scanner
    id="my-scanner"
    :config="[
        'fps' => 15,
        'qrbox' => ['width' => 250, 'height' => 250],
        'aspectRatio' => 1.777778,
        'facingMode' => 'environment'
    ]"
    :supported-formats="[0, 6, 8]"
    :labels="[
        'switchCamera' => 'Toggle Camera',
        'cameraUnavailable' => 'No camera found'
    ]"
    @barcode-scanned.window="handleScan($event.detail)"
/>

<script>
function handleScan(detail) {
    console.log('Scanned:', detail.value, detail.formatName);
    // Your custom logic
}
</script>
```

**Browser Events:**
- `barcode-scanned`: `{ value, formatId, formatName, scannerId, timestamp }`
- `barcode-scanner-error`: `{ error, errorType, scannerId, timestamp }`
- `barcode-scanner-ready`: `{ cameraCount, currentCamera, scannerId, timestamp }`
- `barcode-scanner-stopped`: `{ scannerId, timestamp }`

### Tier 2: Livewire Component (No Filament)

```php
// In Livewire component class
use CCK\FilamentQrcodeScannerHtml5\Livewire\BarcodeScanner;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

public function render()
{
    return view('livewire.my-page', [
        'scanner' => BarcodeScanner::make()
            ->fps(15)
            ->qrbox(250)
            ->preferBackCamera()
            ->onScan(function (string $value, ?BarcodeFormat $format) {
                $this->processScan($value, $format);
            })
    ]);
}
```

```blade
{{-- In blade view --}}
<livewire:barcode-scanner
    wire:key="scanner"
/>
```

### Tier 3: Enhanced Filament Actions

```php
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->fps(15)                          // NEW: Scan speed
            ->qrbox(250)                       // NEW: Square focus box
            ->aspectRatio(1.777778)            // NEW: 16:9 camera feed
            ->preferBackCamera()               // NEW: Force back camera
            ->supportedFormats([...])          // EXISTING
            ->modifyStateUsing(...)            // EXISTING
    )
```

## Testing

Tests use Pest with Orchestra Testbench. The `TestCase` class (`tests/TestCase.php`) configures Filament and Livewire service providers with an in-memory SQLite database and array cache.

## Compatibility

- PHP 8.1+
- Laravel 10, 11, 12
- Filament 3.x
- Livewire 3.x
