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

### Core Components

- **`BarcodeScannerAction`** (`src/BarcodeScannerAction.php`): Filament form action that extends `Filament\Forms\Components\Actions\Action`. Provides the suffix action button for text inputs with configuration methods for formats, labels, and value transformation.

- **`BarcodeScannerHeaderAction`** (`src/BarcodeScannerHeaderAction.php`): Filament header/page action that extends `Filament\Actions\Action`. Use this for standalone scanning (e.g., attendance check-in) with custom callbacks that can redirect or show notifications.

- **`BarcodeScannerServiceProvider`** (`src/BarcodeScannerServiceProvider.php`): Registers views and Livewire `on('call', ...)` hooks that intercept `processBarcodeScan` (form) and `processBarcodeScanHeader` (header) calls.

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

- **`resources/views/barcode-scanner-modal.blade.php`**: Alpine.js component that loads html5-qrcode from CDN, manages camera access, handles scanning, and communicates results back to Livewire.

## Testing

Tests use Pest with Orchestra Testbench. The `TestCase` class (`tests/TestCase.php`) configures Filament and Livewire service providers with an in-memory SQLite database and array cache.

## Compatibility

- PHP 8.1+
- Laravel 10, 11, 12
- Filament 3.x
- Livewire 3.x
