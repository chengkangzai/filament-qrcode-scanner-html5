# Changelog

All notable changes to `filament-qrcode-scanner-html5` will be documented in this file.

## v1.1.0 - 2025-01-08

### Added
- New `BarcodeScannerHeaderAction` for standalone scanning without form fields
- Support for attendance check-in, inventory lookup, and similar use cases
- `afterScan()` callback that supports returning `redirect()` directly
- Automatic handling of `RedirectResponse`, string URLs, and array responses

### Example
```php
BarcodeScannerHeaderAction::make()
    ->afterScan(function (string $value, ?BarcodeFormat $format) {
        $user = User::where('qr_code', $value)->first();
        return redirect()->route('users.show', $user);
    })
```

## v1.0.0 - 2025-01-07

- Initial release
- Support for scanning QR codes and various barcode formats
- PHP closure support for transforming scanned values (`modifyStateUsing`)
- JavaScript function support for client-side transformation (`modifyStateUsingJs`)
- Camera switching support for devices with multiple cameras
- Customizable labels and error messages
- Dark mode support
