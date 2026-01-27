@props([
    'id' => 'barcode-scanner-' . uniqid(),
    'config' => [],
    'supportedFormats' => [],
    'labels' => [],
    'showControls' => true,
])

@php
    $defaultConfig = [
        'fps' => 10,
    ];
    $mergedConfig = array_merge($defaultConfig, $config);

    $defaultLabels = [
        'switchCamera' => 'Switch Camera',
        'cameraUnavailable' => 'Camera is not available. Please check your device settings.',
        'permissionDenied' => 'Camera permission was denied. Please allow camera access to scan barcodes.',
        'browserNotSupported' => 'Your browser does not support camera access.',
        'cameraConstraints' => 'The selected camera does not meet the requirements.',
        'tryAgain' => 'Try Again',
        'loadingCamera' => 'Loading camera...',
        'cameraPreview' => 'Barcode scanner camera preview',
    ];
    $mergedLabels = array_merge($defaultLabels, $labels);
@endphp

<div x-data="{
    scannerId: @js($id),
    scannerConfig: @js($mergedConfig),
    supportedFormats: @js($supportedFormats),
    labels: @js($mergedLabels),
    showControls: @js($showControls),
    isScanning: false,
    isLoading: false,
    cameraError: false,
    cameraErrorMessage: '',
    scanner: null,
    cameras: [],
    currentCameraIndex: 0,
    currentCameraLabel: '',

    init() {
        this.startScanning();
    },

    destroy() {
        this.stopScanningSync();
    },

    stopScanningSync() {
        if (this.scanner) {
            try {
                this.scanner.stop().catch(() => {});
                this.scanner.clear();
            } catch (e) {
                // Ignore errors during cleanup
            }
            this.scanner = null;
        }
        this.isScanning = false;
    },

    sanitizeScannedText(text) {
        if (typeof text !== 'string') {
            return '';
        }
        return text.replace(/<[^>]*>/g, '').trim();
    },

    async loadHtml5Qrcode() {
        if (window.Html5Qrcode) {
            return true;
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            script.onload = () => resolve(true);
            script.onerror = () => reject(new Error('Failed to load scanner library'));
            document.head.appendChild(script);
        });
    },

    async startScanning() {
        this.isLoading = true;
        this.cameraError = false;
        this.cameraErrorMessage = '';

        try {
            await this.loadHtml5Qrcode();

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('camera_unavailable');
            }

            this.cameras = await Html5Qrcode.getCameras();

            if (!this.cameras || this.cameras.length === 0) {
                throw new Error('camera_unavailable');
            }

            this.isScanning = true;

            await this.$nextTick();

            const containerId = this.$refs.scannerContainer.id || this.scannerId + '-container';
            this.$refs.scannerContainer.id = containerId;

            this.scanner = new Html5Qrcode(containerId);

            // Determine initial camera based on facingMode config or prefer back camera
            if (this.scannerConfig.facingMode === 'environment') {
                const backCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('rear') ||
                    c.label.toLowerCase().includes('environment')
                );
                this.currentCameraIndex = backCamera ? this.cameras.indexOf(backCamera) : 0;
            } else if (this.scannerConfig.facingMode === 'user') {
                const frontCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('front') ||
                    c.label.toLowerCase().includes('user')
                );
                this.currentCameraIndex = frontCamera ? this.cameras.indexOf(frontCamera) : 0;
            } else {
                // Default: prefer back camera if not specified
                const preferredCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('rear') ||
                    c.label.toLowerCase().includes('environment')
                );
                this.currentCameraIndex = preferredCamera ? this.cameras.indexOf(preferredCamera) : 0;
            }

            await this.startCamera();

            // Emit ready event
            this.dispatchScannerEvent('barcode-scanner-ready', {
                cameraCount: this.cameras.length,
                currentCamera: this.cameras[this.currentCameraIndex],
            });

        } catch (error) {
            this.handleError(error);
        } finally {
            this.isLoading = false;
        }
    },

    async startCamera() {
        if (!this.scanner || !this.cameras.length) return;

        const camera = this.cameras[this.currentCameraIndex];
        this.currentCameraLabel = camera.label || `Camera ${this.currentCameraIndex + 1}`;

        const config = {
            fps: this.scannerConfig.fps,
            formatsToSupport: this.supportedFormats,
        };

        // Add optional qrbox configuration
        if (this.scannerConfig.qrbox) {
            config.qrbox = this.scannerConfig.qrbox;
        }

        // Add optional aspectRatio configuration
        if (this.scannerConfig.aspectRatio) {
            config.aspectRatio = this.scannerConfig.aspectRatio;
        }

        try {
            await this.scanner.start(
                camera.id,
                config,
                (decodedText, decodedResult) => this.onScanSuccess(decodedText, decodedResult),
                (errorMessage) => {}
            );
        } catch (error) {
            this.handleError(error);
        }
    },

    async stopScanning() {
        if (this.scanner) {
            try {
                const scannerState = this.scanner.getState();
                if (scannerState === Html5QrcodeScannerState.SCANNING ||
                    scannerState === Html5QrcodeScannerState.PAUSED) {
                    await this.scanner.stop();
                }
                await this.scanner.clear();
            } catch (error) {
                console.warn('Error stopping scanner:', error);
                try {
                    await this.scanner.clear();
                } catch (clearError) {
                    console.warn('Error clearing scanner:', clearError);
                }
            }
            this.scanner = null;
        }
        this.isScanning = false;
        this.isLoading = false;

        // Emit stopped event
        this.dispatchScannerEvent('barcode-scanner-stopped', {});
    },

    async switchCamera() {
        if (this.cameras.length <= 1 || this.isLoading) return;

        this.isLoading = true;

        try {
            if (this.scanner) {
                try {
                    await this.scanner.stop();
                } catch (stopError) {
                    // Ignore stop errors - scanner might already be stopped
                }
                try {
                    this.scanner.clear();
                } catch (clearError) {
                    // Ignore clear errors
                }
                this.scanner = null;
            }

            await new Promise(resolve => setTimeout(resolve, 150));

            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;

            const containerId = this.$refs.scannerContainer.id;
            this.scanner = new Html5Qrcode(containerId);

            await this.startCamera();
        } catch (error) {
            if (!error.message?.includes('removeChild') && !error.message?.includes('clear')) {
                this.handleError(error);
            }
        } finally {
            this.isLoading = false;
        }
    },

    async onScanSuccess(decodedText, decodedResult) {
        const sanitizedText = this.sanitizeScannedText(decodedText);
        const formatId = decodedResult?.result?.format?.format ?? 0;
        const formatName = this.getFormatName(formatId);

        // Stop camera first, then wait a moment for media cleanup
        await this.stopScanning();
        await new Promise(resolve => setTimeout(resolve, 50));

        // Emit barcode-scanned event
        this.dispatchScannerEvent('barcode-scanned', {
            value: sanitizedText,
            formatId: formatId,
            formatName: formatName,
        });
    },

    getFormatName(formatId) {
        const formatMap = {
            0: 'QR Code',
            1: 'Aztec',
            2: 'Codabar',
            4: 'Code 39',
            5: 'Code 93',
            6: 'Code 128',
            8: 'ITF',
            9: 'EAN-13',
            10: 'PDF417',
            11: 'EAN-8',
            12: 'Data Matrix',
            14: 'UPC-A',
            15: 'UPC-E',
        };
        return formatMap[formatId] || 'Unknown';
    },

    handleError(error) {
        this.isScanning = false;
        this.isLoading = false;
        this.cameraError = true;

        const errorString = error.message || error.toString();
        let errorType = 'unknown';

        if (errorString.includes('NotSupportedError')) {
            this.cameraErrorMessage = this.labels.browserNotSupported;
            errorType = 'not_supported';
        } else if (errorString.includes('OverconstrainedError')) {
            this.cameraErrorMessage = this.labels.cameraConstraints;
            errorType = 'overconstrained';
        } else if (errorString.includes('Permission') ||
            errorString.includes('NotAllowedError') ||
            errorString.includes('denied')) {
            this.cameraErrorMessage = this.labels.permissionDenied;
            errorType = 'permission_denied';
        } else if (errorString.includes('camera_unavailable') ||
            errorString.includes('NotFoundError') ||
            errorString.includes('NotReadableError')) {
            this.cameraErrorMessage = this.labels.cameraUnavailable;
            errorType = 'camera_unavailable';
        } else {
            this.cameraErrorMessage = this.labels.cameraUnavailable;
            errorType = 'unknown';
        }

        console.warn('Barcode scanner error:', error);

        // Emit error event
        this.dispatchScannerEvent('barcode-scanner-error', {
            error: errorString,
            errorType: errorType,
        });
    },

    dispatchScannerEvent(eventName, detail) {
        window.dispatchEvent(new CustomEvent(eventName, {
            detail: {
                ...detail,
                scannerId: this.scannerId,
                timestamp: Date.now(),
            }
        }));
    }
}"
    class="space-y-4"
    {{ $attributes }}>

    <div x-show="cameraError" role="alert" aria-live="assertive"
        class="rounded-lg bg-warning-50 p-4 dark:bg-warning-400/10">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-warning-700 dark:text-warning-400" x-text="cameraErrorMessage"></p>
                <button type="button" x-on:click="startScanning()"
                    class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
                    x-text="labels.tryAgain">
                </button>
            </div>
        </div>
    </div>

    <div x-show="isScanning || isLoading" class="space-y-3">
        <div x-ref="scannerContainer" role="region" aria-live="polite" x-bind:aria-label="labels.cameraPreview"
            class="relative overflow-hidden rounded-lg border border-gray-300 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
            style="min-height: 300px;">
            <div x-show="isLoading && !isScanning"
                class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                <svg class="h-8 w-8 animate-spin text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" x-bind:aria-label="labels.loadingCamera">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </div>

        <div x-show="showControls" class="flex items-center justify-between">
            <button type="button" x-show="cameras.length > 1" x-on:click="switchCamera()" x-bind:disabled="isLoading"
                x-bind:aria-label="labels.switchCamera"
                class="relative inline-grid grid-flow-col items-center justify-center gap-1 rounded-lg bg-white px-2 py-1.5 text-xs font-semibold text-gray-950 shadow-sm outline-none ring-1 ring-gray-950/10 transition duration-75 hover:bg-gray-50 focus-visible:ring-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:hover:bg-white/10">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                    x-bind:class="{ 'animate-spin': isLoading }">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span x-text="labels.switchCamera"></span>
            </button>

            <span x-show="cameras.length > 1" class="text-xs text-gray-500 dark:text-gray-400"
                x-text="currentCameraLabel" aria-live="polite"></span>
        </div>
    </div>
</div>
