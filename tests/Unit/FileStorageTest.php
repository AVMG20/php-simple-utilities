<?php
declare(strict_types=1);

use Avmg\PhpSimpleUtilities\FileStorage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    private FileStorage $storage;
    private string $testDir = __DIR__ . '/../../storage';

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure test directory is clean before each test
        $this->removeDirectory($this->testDir);
        @mkdir($this->testDir, 0755, true);
        $this->storage = new FileStorage($this->testDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testDir);
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

    public function testDirectoryCreationAndRetrieval(): void
    {
        $subDir = 'newDir';
        $this->storage->dir($subDir); // Attempt to create a new directory

        $this->assertDirectoryExists($this->testDir . '/' . $subDir, 'Directory should be created');

        // Test retrieving the directory through allFiles
        $files = $this->storage->allFiles($subDir);
        $this->assertIsArray($files, 'Should return an array');
    }

    public function testFileCreationAndDeletion(): void
    {
        $filename = 'testFile.txt';
        $content = 'Hello, world!';
        $this->storage->put($filename, $content);

        $this->assertFileExists($this->testDir . '/' . $filename, 'File should be created');
        $this->assertEquals($content, file_get_contents($this->testDir . '/' . $filename), 'File content should match');

        // Test deletion
        $this->storage->delete($filename);
        $this->assertFileDoesNotExist($this->testDir . '/' . $filename, 'File should be deleted');
    }

    public function testFileCreationInsideNestedDirectory()
    {
        $filename = 'testFile.txt';
        $content = 'Hello, world!';
        $this->storage->dir('a/b/c/d/e')->put($filename, $content);

        $this->assertFileExists($this->testDir . '/a/b/c/d/e/' . $filename, 'File should be created');
        $this->assertEquals($content, file_get_contents($this->testDir . '/a/b/c/d/e/' . $filename), 'File content should match');

        // test nested without using dir
        $this->storage->put('a/b/c/d/e/f/g/' . $filename, $content);
        $this->assertFileExists($this->testDir . '/a/b/c/d/e/f/g/' . $filename, 'File should be created');
        $this->assertEquals($content, file_get_contents($this->testDir . '/a/b/c/d/e/f/g/' . $filename), 'File content should match');
    }

    public function testLastModified(): void
    {
        $filename = 'testFile.txt';
        $content = 'New content';
        $this->storage->put($filename, $content);

        sleep(1); // Ensure the file timestamp is definitely different

        $lastModified = $this->storage->lastModified($filename);
        $this->assertIsInt($lastModified, 'Last modified should be an integer');
        $this->assertTrue(time() >= $lastModified, 'Last modified should be in the past');
    }

    public function testAllFiles(): void
    {
        // Create multiple files
        $this->storage->put('file1.txt', 'Content 1');
        $this->storage->put('file2.txt', 'Content 2');

        $files = $this->storage->allFiles();
        $this->assertCount(2, $files, 'There should be two files listed');
    }
}
