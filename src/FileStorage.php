<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileStorage
{
    protected string $basePath;

    /**
     * Constructor.
     *
     * @param string $path Path to store files.
     */
    public function __construct(string $path) {
        // Check if the path is a directory and exists
        if (!is_dir($path)) {
            throw new InvalidArgumentException('The path is not a directory or does not exist.');
        }

        // Check if the path is writable
        if (!is_writable($path)) {
            throw new InvalidArgumentException('The path is not writable.');
        }

        // Set the base path
        $this->basePath = realpath(rtrim($path, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
    }

    /**
     * Create directories recursively based on the given path.
     *
     * @param string $path Path or Name for the directory. 'avatars' or 'user/profiles/avatars' for example.
     * @return static New instance of FileStorage with the selected path.
     */
    public function dir(string $path): static
    {
        $newPath = $this->basePath . trim($path, DIRECTORY_SEPARATOR);

        if (!is_dir($newPath)) {
            if (!mkdir($newPath, 0755, true)) {
                throw new InvalidArgumentException('The directory could not be created.');
            }
        }

        return new self($newPath);
    }

    /**
     * Put a file in the selected path.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @param mixed $content Content to be written.
     */
    public function put(string $filename, mixed $content): void {
        $fullPath = $this->getFullPath($filename);

        if (false === file_put_contents($fullPath, $content)) {
            throw new InvalidArgumentException('The file could not be written.');
        }
    }

    /**
     * Append a file in the selected path.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @param mixed $content Content to be written.
     */
    public function append(string $filename, mixed $content): void {
        $fullPath = $this->getFullPath($filename);

        if (false === file_put_contents($fullPath, $content, FILE_APPEND)) {
            throw new InvalidArgumentException('The file could not be written.');
        }
    }

    /**
     * Prepend a file in the selected path.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @param mixed $content Content to be written.
     */
    public function prepend(string $filename, mixed $content): void {
        $fullPath = $this->getFullPath($filename);

        if (false === file_put_contents($fullPath, $content . file_get_contents($fullPath))) {
            throw new InvalidArgumentException('The file could not be written.');
        }
    }

    /**
     * Delete a file from the selected path.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     */
    public function delete(string $filename): void {
        $fullPath = $this->getFullPath($filename);

        if (file_exists($fullPath)) {
            if (!unlink($fullPath)) {
                throw new InvalidArgumentException('The file could not be deleted.');
            }
        }
    }

    /**
     * Retrieve all files in the current basePath. $path is optional, but if $path is used, dir will be called to retrieve an instance of that path.
     *
     * @param string|null $path Optional path to list files from 'avatars' or 'user/profiles/avatars'.
     * @return string[] An array of all file paths in the given directory
     */
    public function allFiles(?string $path = null): array {
        $directory = $this->basePath;

        if ($path !== null) {
            $fileStorage = $this->dir($path);
            $directory = $fileStorage->getBasePath();
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getFilename();
            }
        }

        return $files;
    }

    /**
     * The lastModified method returns the UNIX timestamp of the last time the file was modified.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @return int The UNIX timestamp of the last modification time.
     */
    public function lastModified(string $filename): int {
        $fullPath = $this->getFullPath($filename);

        if (!file_exists($fullPath)) {
            throw new InvalidArgumentException('The file does not exist.');
        }

        return filemtime($fullPath);
    }
    /**
     * The size method returns the file size in bytes.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @return false|string The MIME type of the file
     */
    public function mimeType(string $filename): false|string
    {
        $fullPath = $this->getFullPath($filename);

        if (!file_exists($fullPath)) {
            throw new InvalidArgumentException('The file does not exist.');
        }

        return mime_content_type($fullPath);
    }

    /**
     * @return string The base path of this instance
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get the full path of the file creating directories if needed.
     *
     * @param string $filename Name or Path of the file starting from the base path.
     * @return string The full path of the file
     */
    protected function getFullPath(string $filename): string
    {
        $directory = dirname($filename);

        // check if a path is provided
        if ($directory !== '.') {
            $instance = $this->dir($directory); // Ensure directory exists
            // Get the full path using newly created instance which has the correct path
            $fullPath = $instance->getBasePath() . basename($filename);
        } else {
            // Get the full path using the current instance
            $fullPath = $this->basePath . $filename;
        }

        return $fullPath;
    }
}