<?php


use Avmg\PhpSimpleUtilities\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    protected string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cachePath = __DIR__ . '/../../storage';
        $this->removeDirectory($this->cachePath);
        @mkdir($this->cachePath, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->cachePath. '/cache');
        parent::tearDown();
    }

    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $directory The path to the directory to remove.
     */
    protected function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($directory);
    }

    public function testPutMethod()
    {
        $cache = new FileCache($this->cachePath);
        $cache->put('key', 'value', 3600); // TTL of 1 hour

        $this->assertEquals('value', $cache->get('key'));
    }

    public function testGetWithExpiredTTL()
    {
        $cache = new FileCache($this->cachePath);
        $cache->put('key', 'value', -1); // Expired TTL

        $this->assertNull($cache->get('key'));
    }

    public function testRemember()
    {
        $cache = new FileCache($this->cachePath);
        $result = $cache->remember('remember_key', 3600, function() {
            return 'computed_value';
        });

        $this->assertEquals('computed_value', $result);
        // Verify that value is actually cached
        $this->assertEquals('computed_value', $cache->get('remember_key'));
    }

    public function testDateTimeTTL()
    {
        $cache = new FileCache($this->cachePath);
        $dateTime = new DateTime('+1 hour');
        $cache->put('datetime_key', 'datetime_value', $dateTime);

        $this->assertEquals('datetime_value', $cache->get('datetime_key'));
    }

    public function testFlush()
    {
        $cache = new FileCache($this->cachePath);
        $cache->put('key1', 'value1', 3600);
        $cache->put('key2', 'value2', 3600);

        $cache->flush();

        $this->assertNull($cache->get('key1'));
        $this->assertNull($cache->get('key2'));
        // Assert the cache directory is recreated
        $this->assertDirectoryExists($cache->getCachePath());
    }

    public function testNonScalarValues()
    {
        $cache = new FileCache($this->cachePath);
        $arrayValue = ['a' => 'apple', 'b' => 'banana'];
        $objectValue = (object)$arrayValue;

        $cache->put('arrayKey', $arrayValue, 3600);
        $cache->put('objectKey', $objectValue, 3600);

        $this->assertEquals($arrayValue, $cache->get('arrayKey'));
        $this->assertEquals($objectValue, $cache->get('objectKey'));
    }

    public function testCustomDefaultValues()
    {
        $cache = new FileCache($this->cachePath);
        $defaultValue = 'default';

        $this->assertEquals($defaultValue, $cache->get('nonExistingKey', $defaultValue));
    }

    public function testCacheKeyCollision()
    {
        $cache = new FileCache($this->cachePath);
        $key1 = 'key1';
        $key2 = 'key2'; // Assume this produces the same hash as key1 for this test

        $cache->put($key1, 'value1', 3600);
        $cache->put($key2, 'value2', 3600);

        $this->assertEquals('value1', $cache->get($key1));
        $this->assertEquals('value2', $cache->get($key2)); // This should fail if there's a collision
    }

    public function testLargeDataHandling()
    {
        $cache = new FileCache($this->cachePath);
        $largeString = str_repeat("large_data", 10000); // Adjust size as needed

        $cache->put('largeKey', $largeString, 3600);

        $this->assertEquals($largeString, $cache->get('largeKey'));
    }

    public function testForgetRemovesItem()
    {
        $cache = new FileCache($this->cachePath);
        $cache->put('key_to_forget', 'value', 3600);

        // Ensure the item was initially set
        $this->assertEquals('value', $cache->get('key_to_forget'));

        // Attempt to forget the item and assert it was successful
        $this->assertTrue($cache->forget('key_to_forget'));

        // Ensure the item is no longer retrievable
        $this->assertNull($cache->get('key_to_forget'));
    }

    public function testForgetNonExistentItem()
    {
        $cache = new FileCache($this->cachePath);

        // Attempt to forget a non-existent item and assert it returns false
        $this->assertFalse($cache->forget('non_existent_key'));
    }

    public function testCacheItemIsActuallyRemoved()
    {
        $cache = new FileCache($this->cachePath);
        $cache->put('another_key', 'another_value', 3600);

        // Forget the item
        $cache->forget('another_key');

        // Verify attempting to get the item returns null (default)
        $this->assertNull($cache->get('another_key', null));
    }

    public function testGetWithCallbackDefault()
    {
        $cache = new FileCache($this->cachePath);
        $defaultCallback = function() {
            return 'callback_default_value';
        };

        $result = $cache->get('non_existent_key', $defaultCallback);

        // Assert that the default callback was called and its return value was used
        $this->assertEquals('callback_default_value', $result, 'The callback default value should be returned for non-existent keys.');
    }
}
