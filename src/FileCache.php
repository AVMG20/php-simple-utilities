<?php

declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Closure;
use DateTime;
use DateTimeInterface;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Simple file-based cache system
 */
class FileCache
{
    /**
     * The base path for cache storage.
     *
     * @var string
     */
    protected string $cachePath;

    /**
     * Constructor.
     *
     * @param string $path Path to store cache files.
     */
    public function __construct(string $path)
    {
        //check if the path is a directory and exist
        if (!is_dir($path)) {
            throw new InvalidArgumentException('The path is not a directory or does not exist.');
        }

        //check if the path is writable
        if (!is_writable($path)) {
            throw new InvalidArgumentException('The path is not writable.');
        }

        //set the base path
        $cachePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cache';

        //create the base path if it does not exist
        if (!is_dir($cachePath)) {
            if (!mkdir($cachePath, 0755, true)) {
                throw new InvalidArgumentException('The cache directory could not be created.');
            }
        }

        $this->cachePath = realpath($cachePath);
    }

    /**
     * Store a value in the cache.
     *
     * @param string $key Cache key.
     * @param mixed $value Cache value.
     * @param int|DateTime $ttl Time to live in seconds.
     * @return void
     */
    public function put(string $key, $value, int|DateTime $ttl): void
    {
        //if the value is null, do nothing
        if (is_null($value)) {
            return;
        }

        $filePath = $this->getFilePath($key);

        $expireTime = $this->getExpiryTime($ttl);

        $data = serialize($value);

        file_put_contents($filePath, "$expireTime\n$data");
    }

    /**
     * Get a cache value.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param TCacheValue|(Closure(): TCacheValue) $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public function get(string $key, $default = null): mixed
    {
        $filePath = $this->getFilePath($key);
        if (!file_exists($filePath)) {
            return $this->value($default);
        }

        $contents = file($filePath);
        $expireTime = (int)$contents[0];

        if (time() > $expireTime) {
            unlink($filePath);
            return $this->value($default);
        }

        return unserialize($contents[1]);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key Cache key.
     * @return bool True if the item was removed, false otherwise.
     */
    public function forget(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }

        return false;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template TCacheValue
     *
     * @param string $key Cache key.
     * @param int|DateTime $ttl Time to live in seconds.
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public function remember(string $key, int|DateTime $ttl, Closure $callback)
    {
        $value = $this->get($key);
        if (!is_null($value)) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);
        return $value;
    }

    /**
     * Clear all cache files.
     *
     * @return void
     */
    public function flush(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileInfo->getRealPath());
        }
    }

    /**
     * Get the hashed key.
     *
     * @param string $key Cache key.
     * @return string
     */
    protected function getHashedKey(string $key): string
    {
        return hash('xxh64', $key);
    }

    /**
     * Get the file path for a hashed key.
     *
     * @param string $key The cache key.
     * @return string
     */
    protected function getFilePath(string $key): string
    {
        $hash = $this->getHashedKey($key);

        $prefix1 = substr($hash, 0, 2);
        $prefix2 = substr($hash, 2, 2);

        $directory = implode(DIRECTORY_SEPARATOR, [$this->cachePath, $prefix1, $prefix2]);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory . DIRECTORY_SEPARATOR . $hash;
    }

    /**
     * Get the expiry time.
     *
     * @param DateTime|int $ttl Time to live in seconds or DateTime.
     * @return DateTime|int Expiry time.
     */
    private function getExpiryTime(DateTime|int $ttl): DateTime|int
    {
        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        return time() + $ttl;
    }

    /**
     *  Get the base path for cache storage.
     *
     * @return string The base path for cache storage.
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Return the default value of the given value or callback.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}