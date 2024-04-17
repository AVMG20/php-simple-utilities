# File Storage Documentation

The `FileStorage` class offers a simple and efficient way to handle file storage operations within a filesystem. This class is designed to be intuitive, providing functionalities such as creating directories, storing, retrieving, and deleting files.

## All methods available in the FileStorage class

- [constructor()](#constructor)  Constructs a new FileStorage instance.
- [dir()](#dir) Create or set the directory context for file operations.
- [get()](#get) Retrieves the content of a file at the specified path.
- [put()](#put) Stores content in a file at the specified path.
- [append()](#append) Appends content to a file at the specified path.
- [prepend()](#prepend) Prepends content to a file at the specified path.
- [delete()](#delete) Deletes a file from the specified path.
- [allFiles()](#allFiles) Retrieves all files in the current or a specified directory.
- [lastModified()](#lastModified) Gets the last modification time of a specified file.

### Constructing a FileStorage Instance

To start using the `FileStorage`, instantiate it with the path of the directory where files should be stored.

```php
$storage = new FileStorage('/path/to/storage');
```
### dir()

Create or set the directory context for file operations. If the directory does not exist, it will be created.

```php
// Return a new instance of FileStorage with the new set path.
$storage->dir('user/images');
```

### get()

Retrieves the content of a file at the specified path.

```php
// Get the content of 'report.txt'

$content = $storage->get('report.txt');
```

Get the content of a file located in a sub directory
```php
// Get the content of a file inside a nested directory
$contentInSubDir = $storage->dir('someDir/anotherDir')->get('report.txt');
// Or shorthand without setting the dir context
$contentInSubDirDirect = $storage->get('someDir/anotherDir/report.txt');
```

### put()

Stores content in a file at the specified path. If the file does not exist, it will be created.

```php
// Store 'Hello, world!' in 'greetings.txt' 
$storage->put('greetings.txt', 'Hello, world!');
```

Store files in sub directory of the FileStorage
```php
// Store greetings in a sub directory
$storage->dir('someDir/anotherDir')->put('greetings.txt', 'Hello, world!');
// Or shorthand without setting the dir context
$storage->put('someDir/anotherDir/greetings.txt', 'Hello, world!');
```

### append()

Appends content to a file at the specified path. If the file does not exist, it will be created.

```php
// Append 'Hello, world!' to 'greetings.txt'
$storage->append('greetings.txt', 'Hello, world!');
```

Append to a file located in a sub directory
```php
// Append to a file inside a nested directory
$storage->dir('someDir/anotherDir')->append('greetings.txt', 'Hello, world!');
// Or shorthand without setting the dir context
$storage->append('someDir/anotherDir/greetings.txt', 'Hello, world!');
```

### prepend()

Prepends content to a file at the specified path. If the file does not exist, it will be created.

```php
// Prepend 'Hello, world!' to 'greetings.txt'
$storage->prepend('greetings.txt', 'Hello, world!');
```


### delete()

Deletes a file from the specified path.

```php
// Delete 'oldfile.txt' from storage 
$storage->delete('oldfile.txt');
```

Delete a file located in a sub directory
```php
// Delete a file inside a nested directory
$storage->dir('someDir/anotherDir')->delete('oldfile.txt');
// Or shorthand without setting the dir context
$storage->delete('someDir/anotherDir/oldfile.txt');
```
### allFiles()

Retrieves the path of all files in the current or a specified directory.

```php
// Get all files in the storage root
$files = $storage->allFiles();

// Get all files in the 'documents' directory
$filesInDocuments = $storage->allFiles('documents');

// Get all files in a subdirectory using dir()
$filesInSubDir = $storage->dir('someDir/anotherDir')->allFiles();

// Alternatively, without setting the dir context
$filesInSubDirDirect = $storage->allFiles('someDir/anotherDir');
```

### lastModified()

Gets the last modification time of a specified file.

```php
// Get the last modification time of 'report.txt'
$lastModified = $storage->lastModified('report.txt');

// Get the last modification time of a file in a subdirectory
$lastModifiedInSubDir = $storage->lastModified('someDir/anotherDir/report.txt');

// Using dir() to set the context before getting the last modification time
$lastModifiedUsingDir = $storage->dir('someDir/anotherDir')->lastModified('report.txt');
```

### mimeType()

Gets the mime type of a specified file.

```php
// Get the mime type of 'report.txt'
$mimeType = $storage->mimeType('report.txt');

// output: text/plain
```
