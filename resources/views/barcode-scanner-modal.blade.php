<div x-data="{
    statePath: @js($statePath),
    hasPhpModifier: @js($hasPhpModifier ?? false),
    stateModifierJs: @js($stateModifierJs),
    supportedFormats: @js($supportedFormats),
    messages: {
        cameraUnavailable: @js($cameraUnavailableMessage),
        permissionDenied: @js($permissionDeniedMessage),
        browserNotSupported: @js(__('Your browser does not support camera access.')),
        cameraConstraints: @js(__('The selected camera does not meet the requirements.')),
    },
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

    applyStateModifier(value, formatId) {
        if (!this.stateModifierJs) {
            return value;
        }

        try {
            const modifierFn = new Function('return ' + this.stateModifierJs)();
            return modifierFn(value, formatId);
        } catch (error) {
            console.warn('Error applying state modifier:', error);
            return value;
        }
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

            const containerId = this.$refs.scannerContainer.id || 'barcode-scanner-modal-container';
            this.$refs.scannerContainer.id = containerId;

            this.scanner = new Html5Qrcode(containerId);

            const preferredCamera = this.cameras.find(c =>
                c.label.toLowerCase().includes('back') ||
                c.label.toLowerCase().includes('rear') ||
                c.label.toLowerCase().includes('environment')
            );

            this.currentCameraIndex = preferredCamera ?
                this.cameras.indexOf(preferredCamera) :
                0;

            await this.startCamera();

        } catch (error) {
            this.handleError(error);
        } finally {
            this.isLoading = false;
        }
    },

    async startCamera() {
        if (!this.scanner || !this.cameras.length) return;

        const camera = this.cameras[this.currentCameraIndex];
        this.currentCameraLabel = camera.label ||
            `Camera ${this.currentCameraIndex + 1}`;

        const config = {
            fps: 10,
            formatsToSupport: this.supportedFormats,
        };

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

        // Stop camera first, then wait a moment for media cleanup
        await this.stopScanning();
        await new Promise(resolve => setTimeout(resolve, 50));

        if (this.hasPhpModifier) {
            // PHP closure via Livewire
            $wire.processBarcodeScan(this.statePath, sanitizedText, formatId)
                .then(modifiedValue => {
                    if (this.statePath) {
                        $wire.set(this.statePath, modifiedValue);
                    }
                    this.closeModal();
                });
        } else {
            // JS modifier or no modifier
            const modifiedValue = this.applyStateModifier(sanitizedText, formatId);
            if (this.statePath) {
                $wire.set(this.statePath, modifiedValue);
            }
            this.closeModal();
        }
    },

    closeModal() {
        if (typeof $wire.unmountFormComponentAction === 'function') {
            $wire.unmountFormComponentAction(false);
        } else if (typeof $wire.unmountAction === 'function') {
            $wire.unmountAction(false);
        }
    },

    handleError(error) {
        this.isScanning = false;
        this.isLoading = false;
        this.cameraError = true;

        const errorString = error.message || error.toString();

        if (errorString.includes('NotSupportedError')) {
            this.cameraErrorMessage = this.messages.browserNotSupported;
        } else if (errorString.includes('OverconstrainedError')) {
            this.cameraErrorMessage = this.messages.cameraConstraints;
        } else if (errorString.includes('Permission') ||
            errorString.includes('NotAllowedError') ||
            errorString.includes('denied')) {
            this.cameraErrorMessage = this.messages.permissionDenied;
        } else if (errorString.includes('camera_unavailable') ||
            errorString.includes('NotFoundError') ||
            errorString.includes('NotReadableError')) {
            this.cameraErrorMessage = this.messages.cameraUnavailable;
        } else {
            this.cameraErrorMessage = this.messages.cameraUnavailable;
        }

        console.warn('Barcode scanner error:', error);
    }
}"
    x-on:modal-closed.window="stopScanning()"
    x-on:close-modal.window="stopScanning()"
    x-on:open-modal.window="if ($event.detail.id !== 'barcode-scanner') stopScanning()"
    class="space-y-4">
    <div x-show="cameraError" role="alert" aria-live="assertive"
        class="rounded-lg bg-warning-50 p-4 dark:bg-warning-400/10">
        <div class="flex">
            <div class="flex-shrink-0">
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-5 w-5 text-warning-400"
                    aria-hidden="true" />
            </div>
            <div class="ml-3">
                <p class="text-sm text-warning-700 dark:text-warning-400" x-text="cameraErrorMessage"></p>
                <button type="button" x-on:click="startScanning()"
                    class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    {{ __('Try Again') }}
                </button>
            </div>
        </div>
    </div>

    <div x-show="isScanning || isLoading" class="space-y-3">
        <div x-ref="scannerContainer" role="region" aria-live="polite"
            aria-label="{{ __('Barcode scanner camera preview') }}"
            class="relative overflow-hidden rounded-lg border border-gray-300 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
            style="min-height: 300px;">
            <div x-show="isLoading && !isScanning"
                class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500"
                    aria-label="{{ __('Loading camera...') }}" />
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" x-show="cameras.length > 1" x-on:click="switchCamera()" x-bind:disabled="isLoading"
                aria-label="{{ $switchCameraLabel }}"
                class="fi-btn fi-btn-size-sm fi-color-custom fi-btn-color-gray fi-color-gray fi-size-sm relative inline-grid grid-flow-col items-center justify-center gap-1 rounded-lg bg-white px-2 py-1.5 text-xs font-semibold text-gray-950 shadow-sm outline-none ring-1 ring-gray-950/10 transition duration-75 hover:bg-gray-50 focus-visible:ring-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:hover:bg-white/10">
                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" aria-hidden="true"
                    x-bind:class="{ 'animate-spin': isLoading }" />
                <span>{{ $switchCameraLabel }}</span>
            </button>

            <span x-show="cameras.length > 1" class="text-xs text-gray-500 dark:text-gray-400"
                x-text="currentCameraLabel" aria-live="polite"></span>
        </div>
    </div>
</div>
