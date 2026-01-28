# Changelog

All notable changes to `filament-qrcode-scanner-html5` will be documented in this file.

## v1.3.0 - 2026-01-28

### Added
- **UI configuration options** for scanner controls:
  - `controlButtonStyle(string)` - Switch camera button display style ('icon' or 'icon-text')
  - `controlPosition(string)` - Controls alignment ('left', 'center', or 'right')
  - `showCameraName(bool)` - Toggle camera name visibility
  - `iconOnly()` - Convenience method for icon-only button
  - `iconWithText()` - Convenience method for icon with text button
  - `hideCameraName()` - Convenience method to hide camera name

### Enhanced
- Scanner controls now support flexible visual configurations
- Alpine.js component supports dynamic button styles and control positioning

### Tests
- Added 41 new tests for UI configuration options
- All 109 tests passing (179 assertions)

### Backward Compatibility
- ✅ 100% backward compatible
- ✅ Default values match previous UI behavior (icon-text, left, show camera name)
- ✅ All new features are opt-in

## v1.2.0 - 2025-01-XX

### Added
- **Three-tier architecture** for progressive enhancement:
  - Tier 1: Pure Alpine.js component (no framework dependencies)
  - Tier 2: Standalone Livewire component (works without Filament)
  - Tier 3: Enhanced Filament actions (existing API + new options)
- **Configurable scanner options** for all tiers:
  - `fps(int)` - Frames per second (1-30)
  - `qrbox(int, ?int)` - Focus box dimensions
  - `aspectRatio(float)` - Camera aspect ratio
  - `facingMode(string)` - Camera facing mode ('user' or 'environment')
  - `preferBackCamera()` - Convenience method for back camera
  - `preferFrontCamera()` - Convenience method for front camera
- **Browser event system** for Tier 1 component:
  - `barcode-scanned` - Emitted when barcode is successfully scanned
  - `barcode-scanner-error` - Emitted when scanner error occurs
  - `barcode-scanner-ready` - Emitted when scanner is initialized
  - `barcode-scanner-stopped` - Emitted when scanner is stopped
- Standalone Livewire component (`<livewire:barcode-scanner />`) for non-Filament usage
- Pure Alpine.js component (`<x-filament-qrcode-scanner-html5::barcode-scanner />`) for framework-agnostic usage
- `HasScannerConfiguration` trait for DRY configuration across all tiers

### Enhanced
- `BarcodeScannerAction` now supports all new scanner configuration options
- `BarcodeScannerHeaderAction` now supports all new scanner configuration options
- Modal view refactored to use Tier 1 component internally

### Internal
- Extracted scanner logic to reusable Alpine.js component
- Improved code organization with shared configuration trait

### Backward Compatibility
- ✅ 100% backward compatible - all existing API methods unchanged
- ✅ Default values match previous behavior
- ✅ All new features are opt-in
- ✅ Published views continue to work

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
