@php
    $scannerId = 'filament-modal-scanner-' . uniqid();
@endphp

<div x-data="{
    scannerId: @js($scannerId),
    statePath: @js($statePath),
    isHeaderAction: @js($isHeaderAction ?? false),
    hasPhpModifier: @js($hasPhpModifier ?? false),
    stateModifierJs: @js($stateModifierJs),

    init() {
        // Listen for scan events from the Tier 1 component
        window.addEventListener('barcode-scanned', (event) => {
            if (event.detail.scannerId === this.scannerId) {
                this.handleFilamentScan(event.detail);
            }
        });
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

    async handleFilamentScan(detail) {
        const sanitizedText = this.sanitizeScannedText(detail.value);
        const formatId = detail.formatId;

        await new Promise(resolve => setTimeout(resolve, 50));

        if (this.isHeaderAction) {
            // Header action - custom callback with redirect support
            $wire.processBarcodeScanHeader(sanitizedText, formatId)
                .then(response => {
                    if (response && response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }
                    this.closeModal();
                });
        } else if (this.hasPhpModifier) {
            // Form action - PHP closure via Livewire
            $wire.processBarcodeScan(this.statePath, sanitizedText, formatId)
                .then(modifiedValue => {
                    if (this.statePath) {
                        $wire.set(this.statePath, modifiedValue);
                    }
                    this.closeModal();
                });
        } else {
            // Form action - JS modifier or no modifier
            const modifiedValue = this.applyStateModifier(sanitizedText, formatId);
            if (this.statePath) {
                $wire.set(this.statePath, modifiedValue);
            }
            this.closeModal();
        }
    },

    closeModal() {
        if (this.isHeaderAction) {
            if (typeof $wire.unmountAction === 'function') {
                $wire.unmountAction(false);
            }
        } else {
            if (typeof $wire.unmountFormComponentAction === 'function') {
                $wire.unmountFormComponentAction(false);
            } else if (typeof $wire.unmountAction === 'function') {
                $wire.unmountAction(false);
            }
        }
    }
}"
    x-on:modal-closed.window="$refs.scanner?.destroy?.()"
    x-on:close-modal.window="$refs.scanner?.destroy?.()"
    class="space-y-4">

    <div x-ref="scanner">
        <x-filament-qrcode-scanner-html5::barcode-scanner
            :id="$scannerId"
            :config="$scannerConfig"
            :supported-formats="$supportedFormats"
            :labels="$labels"
        />
    </div>
</div>
