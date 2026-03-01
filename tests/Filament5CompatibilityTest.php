<?php

use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction;
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerHeaderAction;
use CCK\FilamentQrcodeScannerHtml5\BarcodeScannerServiceProvider;
use CCK\FilamentQrcodeScannerHtml5\Livewire\BarcodeScanner;

describe('Filament 5 Compatibility', function () {
    it('service provider boots without errors', function () {
        $provider = app()->getProvider(BarcodeScannerServiceProvider::class);

        expect($provider)->toBeInstanceOf(BarcodeScannerServiceProvider::class);
    });

    it('Filament Actions\\Action class exists and is extendable', function () {
        expect(class_exists(\Filament\Actions\Action::class))->toBeTrue();

        $action = BarcodeScannerAction::make();
        expect($action)->toBeInstanceOf(\Filament\Actions\Action::class);
    });

    it('Filament Schemas\\Components\\Component class exists', function () {
        expect(class_exists(\Filament\Schemas\Components\Component::class))->toBeTrue();
    });

    it('Filament Contracts\\Plugin interface exists', function () {
        expect(interface_exists(\Filament\Contracts\Plugin::class))->toBeTrue();
    });

    it('Livewire component registration works', function () {
        $component = app('livewire')->new('barcode-scanner');

        expect($component)->toBeInstanceOf(BarcodeScanner::class);
    });

    it('views load correctly', function () {
        $view = view('filament-qrcode-scanner-html5::barcode-scanner-modal');

        expect($view->getPath())->toContain('barcode-scanner-modal');
    });

    it('BarcodeScannerAction can be instantiated with configuration', function () {
        $action = BarcodeScannerAction::make()
            ->fps(15)
            ->qrbox(250)
            ->preferBackCamera();

        expect($action)->toBeInstanceOf(BarcodeScannerAction::class);
    });

    it('BarcodeScannerHeaderAction can be instantiated with configuration', function () {
        $action = BarcodeScannerHeaderAction::make()
            ->fps(10)
            ->qrbox(200, 200);

        expect($action)->toBeInstanceOf(BarcodeScannerHeaderAction::class);
    });

    it('Livewire on hook function exists', function () {
        expect(function_exists('Livewire\\on'))->toBeTrue();
    });
});
