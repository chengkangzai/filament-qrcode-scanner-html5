<div>
    <x-filament-qrcode-scanner-html5::barcode-scanner
        :id="$this->getId()"
        :config="$this->getScannerConfig()"
        :supported-formats="$this->getHtml5QrcodeFormatIds()"
        :labels="$this->getLabels()"
        @barcode-scanned.window="
            if ($event.detail.scannerId === '{{ $this->getId() }}') {
                $wire.handleScan($event.detail.value, $event.detail.formatId)
            }
        "
        @barcode-scanner-error.window="
            if ($event.detail.scannerId === '{{ $this->getId() }}') {
                $wire.handleError($event.detail.error, $event.detail.errorType)
            }
        "
    />
</div>
