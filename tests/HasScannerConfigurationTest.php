<?php

use CCK\FilamentQrcodeScannerHtml5\Concerns\HasScannerConfiguration;
use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;

beforeEach(function () {
    $this->instance = new class
    {
        use HasScannerConfiguration;

        public function exposeScannerConfig(): array
        {
            return $this->getScannerConfig();
        }

        public function exposeLabels(): array
        {
            return $this->getLabels();
        }

        public function exposeHtml5QrcodeFormatIds(): array
        {
            return $this->getHtml5QrcodeFormatIds();
        }
    };
});

it('has correct default fps', function () {
    $config = $this->instance->exposeScannerConfig();

    expect($config['fps'])->toBe(10);
});

it('can set custom fps', function () {
    $this->instance->fps(15);
    $config = $this->instance->exposeScannerConfig();

    expect($config['fps'])->toBe(15);
});

it('validates fps minimum value', function () {
    $this->instance->fps(0);
})->throws(InvalidArgumentException::class, 'FPS must be between 1 and 30');

it('validates fps maximum value', function () {
    $this->instance->fps(31);
})->throws(InvalidArgumentException::class, 'FPS must be between 1 and 30');

it('accepts fps boundary values', function () {
    $this->instance->fps(1);
    expect($this->instance->exposeScannerConfig()['fps'])->toBe(1);

    $this->instance->fps(30);
    expect($this->instance->exposeScannerConfig()['fps'])->toBe(30);
});

it('can set square qrbox', function () {
    $this->instance->qrbox(250);
    $config = $this->instance->exposeScannerConfig();

    expect($config['qrbox'])->toBe(['width' => 250, 'height' => 250]);
});

it('can set rectangular qrbox', function () {
    $this->instance->qrbox(300, 200);
    $config = $this->instance->exposeScannerConfig();

    expect($config['qrbox'])->toBe(['width' => 300, 'height' => 200]);
});

it('validates qrbox width', function () {
    $this->instance->qrbox(0);
})->throws(InvalidArgumentException::class, 'Qrbox width must be positive');

it('validates qrbox height', function () {
    $this->instance->qrbox(250, 0);
})->throws(InvalidArgumentException::class, 'Qrbox height must be positive');

it('does not include qrbox in config when not set', function () {
    $config = $this->instance->exposeScannerConfig();

    expect($config)->not->toHaveKey('qrbox');
});

it('can set aspect ratio', function () {
    $this->instance->aspectRatio(1.777778);
    $config = $this->instance->exposeScannerConfig();

    expect($config['aspectRatio'])->toBe(1.777778);
});

it('validates aspect ratio must be positive', function () {
    $this->instance->aspectRatio(0);
})->throws(InvalidArgumentException::class, 'Aspect ratio must be positive');

it('validates aspect ratio cannot be negative', function () {
    $this->instance->aspectRatio(-1.5);
})->throws(InvalidArgumentException::class, 'Aspect ratio must be positive');

it('does not include aspect ratio in config when not set', function () {
    $config = $this->instance->exposeScannerConfig();

    expect($config)->not->toHaveKey('aspectRatio');
});

it('can set facing mode to environment', function () {
    $this->instance->facingMode('environment');
    $config = $this->instance->exposeScannerConfig();

    expect($config['facingMode'])->toBe('environment');
});

it('can set facing mode to user', function () {
    $this->instance->facingMode('user');
    $config = $this->instance->exposeScannerConfig();

    expect($config['facingMode'])->toBe('user');
});

it('validates facing mode values', function () {
    $this->instance->facingMode('invalid');
})->throws(InvalidArgumentException::class, "Facing mode must be 'user' or 'environment'");

it('does not include facing mode in config when not set', function () {
    $config = $this->instance->exposeScannerConfig();

    expect($config)->not->toHaveKey('facingMode');
});

it('can prefer back camera', function () {
    $this->instance->preferBackCamera();
    $config = $this->instance->exposeScannerConfig();

    expect($config['facingMode'])->toBe('environment');
});

it('can prefer front camera', function () {
    $this->instance->preferFrontCamera();
    $config = $this->instance->exposeScannerConfig();

    expect($config['facingMode'])->toBe('user');
});

it('returns all formats when none specified', function () {
    expect($this->instance->getSupportedFormats())->toBe(BarcodeFormat::cases());
});

it('can set supported formats', function () {
    $formats = [BarcodeFormat::QRCode, BarcodeFormat::Code128];
    $this->instance->supportedFormats($formats);

    expect($this->instance->getSupportedFormats())->toBe($formats);
});

it('returns html5-qrcode format ids', function () {
    $this->instance->supportedFormats([BarcodeFormat::QRCode, BarcodeFormat::Code128]);
    $formatIds = $this->instance->exposeHtml5QrcodeFormatIds();

    expect($formatIds)->toBe([0, 6]);
});

it('can set custom labels', function () {
    $this->instance
        ->switchCameraLabel('Toggle Camera')
        ->cameraUnavailableMessage('Camera not found')
        ->permissionDeniedMessage('Access denied');

    $labels = $this->instance->exposeLabels();

    expect($labels)->toBe([
        'switchCamera' => 'Toggle Camera',
        'cameraUnavailable' => 'Camera not found',
        'permissionDenied' => 'Access denied',
    ]);
});

it('has default labels', function () {
    $labels = $this->instance->exposeLabels();

    expect($labels)->toHaveKeys(['switchCamera', 'cameraUnavailable', 'permissionDenied']);
});

it('can chain all configuration methods', function () {
    $result = $this->instance
        ->fps(20)
        ->qrbox(250)
        ->aspectRatio(1.5)
        ->preferBackCamera()
        ->supportedFormats([BarcodeFormat::QRCode])
        ->switchCameraLabel('Toggle')
        ->cameraUnavailableMessage('No camera')
        ->permissionDeniedMessage('Denied');

    expect($result)->toBe($this->instance);
});

it('generates complete scanner config with all options', function () {
    $this->instance
        ->fps(20)
        ->qrbox(300, 200)
        ->aspectRatio(1.777778)
        ->preferBackCamera();

    $config = $this->instance->exposeScannerConfig();

    expect($config)->toBe([
        'fps' => 20,
        'qrbox' => ['width' => 300, 'height' => 200],
        'aspectRatio' => 1.777778,
        'facingMode' => 'environment',
    ]);
});

it('generates minimal scanner config with defaults', function () {
    $config = $this->instance->exposeScannerConfig();

    expect($config)->toBe([
        'fps' => 10,
    ]);
});
