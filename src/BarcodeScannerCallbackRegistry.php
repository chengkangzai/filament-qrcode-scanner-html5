<?php

namespace CCK\FilamentQrcodeScannerHtml5;

use CCK\FilamentQrcodeScannerHtml5\Enums\BarcodeFormat;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

class BarcodeScannerCallbackRegistry
{
    private const CACHE_PREFIX = 'barcode_scanner_callback:';

    private const CACHE_TTL = 300; // 5 minutes

    public static function register(Closure $callback): string
    {
        $id = Str::uuid()->toString();
        $serializable = new SerializableClosure($callback);

        Cache::put(self::CACHE_PREFIX . $id, serialize($serializable), self::CACHE_TTL);

        return $id;
    }

    public static function execute(string $id, string $value, ?BarcodeFormat $format): string
    {
        $key = self::CACHE_PREFIX . $id;
        $serialized = Cache::pull($key);

        if (! $serialized) {
            return $value;
        }

        /** @var SerializableClosure $serializable */
        $serializable = unserialize($serialized);
        $callback = $serializable->getClosure();

        return $callback($value, $format);
    }

    public static function has(string $id): bool
    {
        return Cache::has(self::CACHE_PREFIX . $id);
    }
}
