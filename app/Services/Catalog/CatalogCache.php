<?php

namespace App\Services\Catalog;

use Closure;
use Illuminate\Contracts\Cache\Repository;

/**
 * Versioned cache for catalog listings: flush() bumps the version instead of
 * deleting keys one by one, old entries just expire by TTL.
 */
class CatalogCache
{
    private const VERSION_KEY = 'catalog:version';

    private const TTL = 600;

    public function __construct(private readonly Repository $cache)
    {
    }

    public function remember(string $key, Closure $callback): mixed
    {
        return $this->cache->remember($this->versioned($key), self::TTL, $callback);
    }

    public function flush(): void
    {
        $this->cache->add(self::VERSION_KEY, 1);
        $this->cache->increment(self::VERSION_KEY);
    }

    private function versioned(string $key): string
    {
        $version = (int) $this->cache->get(self::VERSION_KEY, 1);

        return "catalog:v{$version}:{$key}";
    }
}
