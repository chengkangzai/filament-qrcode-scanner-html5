# Upgrade Guide

## Upgrading from v1.x to v2.0

### Version Requirements

| Version | PHP | Laravel | Filament | Livewire |
|---------|-----|---------|----------|----------|
| v2.x    | 8.2+ | 11.28+ | 4.x     | 3.x      |
| v1.x    | 8.1+ | 10+    | 3.x     | 3.x      |

### Breaking Changes

**v2.0 requires:**
- **PHP 8.2+** (was 8.1+)
- **Laravel 11.28+** (was 10+)
- **Filament 4.x** (was 3.x)

**Important:** The public API is 100% compatible. No code changes are required in your application.

### Step-by-Step Upgrade

#### 1. Upgrade Filament to v4

First, upgrade Filament itself following the [official Filament 4 upgrade guide](https://filamentphp.com/docs/4.x/support/upgrade-guide).

```bash
# Update Filament to v4
composer require filament/filament:"^4.0" -W
```

#### 2. Verify Requirements

Ensure your environment meets the minimum requirements:

- **PHP 8.2+**: Check with `php -v`
- **Laravel 11.28+**: Check `composer show laravel/framework`

If you need to upgrade PHP or Laravel, do so before proceeding.

#### 3. Update Package

Update this package to v2.0:

```bash
composer require chengkangzai/filament-qrcode-scanner-html5:"^2.0" -W
```

#### 4. Republish Views (If Customized)

If you've published and customized the package views:

```bash
# Backup your customizations first
cp -r resources/views/vendor/filament-qrcode-scanner-html5 /tmp/backup

# Republish views
php artisan vendor:publish --tag=filament-qrcode-scanner-html5-views --force

# Merge your customizations back
```

#### 5. Clear Caches

Clear Laravel's caches:

```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### No Code Changes Required

The public API remains unchanged. All these continue to work:

**Form Actions:**
```php
TextInput::make('barcode')
    ->suffixAction(
        BarcodeScannerAction::make()
            ->fps(15)
            ->qrbox(250)
            ->preferBackCamera()
            ->supportedFormats([...])
            ->modifyStateUsing(...)
    )
```

**Header Actions:**
```php
BarcodeScannerHeaderAction::make()
    ->fps(15)
    ->preferBackCamera()
    ->afterScan(fn ($value, $format) => ...)
```

**Standalone Livewire:**
```php
BarcodeScanner::make()
    ->onScan(fn ($value, $format) => ...)
```

**Alpine.js Component:**
```blade
<x-filament-qrcode-scanner-html5::barcode-scanner
    :config="[...]"
    @barcode-scanned.window="..."
/>
```

### Testing Your Upgrade

After upgrading, test these scenarios:

- [ ] Form suffix action scans and populates field
- [ ] Header action scans and executes callback
- [ ] Custom `modifyStateUsing()` closures work
- [ ] Custom `afterScan()` callbacks work
- [ ] Supported formats filter correctly
- [ ] Scanner configuration options work (fps, qrbox, camera)
- [ ] Dark mode renders correctly
- [ ] Published views display correctly (if customized)

### Rollback Instructions

If you encounter issues and need to rollback:

```bash
# Downgrade to v1.x
composer require chengkangzai/filament-qrcode-scanner-html5:"^1.3"

# If you downgraded Filament, restore it
composer require filament/filament:"^3.0" -W

# Clear caches
php artisan config:clear
php artisan view:clear
```

### Support for v1.x

**Maintenance Timeline:**
- **0-12 months**: Critical bugfixes backported to v1.x branch
- **12+ months**: End-of-life, upgrade to v2.x recommended

To stay on v1.x:

```bash
composer require chengkangzai/filament-qrcode-scanner-html5:"^1.3"
```

### Getting Help

If you encounter issues:

1. Check the [GitHub Issues](https://github.com/chengkangzai/filament-qrcode-scanner-html5/issues)
2. Review the [Filament 4 Upgrade Guide](https://filamentphp.com/docs/4.x/support/upgrade-guide)
3. Create a new issue with:
   - PHP version (`php -v`)
   - Laravel version (`composer show laravel/framework`)
   - Filament version (`composer show filament/filament`)
   - Error message and stack trace

### Internal Changes

While the public API is unchanged, these internal changes were made:

**Updated Namespaces:**
- `BarcodeScannerAction` now extends `Filament\Actions\Action` (was `Filament\Forms\Components\Actions\Action`)
- Unified with Filament 4's action architecture

**Dependency Updates:**
- PHP: `^8.1` → `^8.2`
- Filament: `^3.0` → `^4.0`
- Orchestra Testbench: Removed `^8.0` support

These changes are transparent to your application code.
