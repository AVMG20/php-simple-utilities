<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities\Unit;

use PHPUnit\Framework\TestCase;
use Avmg\PhpSimpleUtilities\Arr;

class ArrTest extends TestCase
{
    private array $testArray;
    private array $testArray2;
    private object $testObject;

    protected function setUp(): void
    {
        $this->testArray = [
            ['id' => 1, 'name' => 'John', 'age' => 25, 'info' => ['active' => true]],
            ['id' => 2, 'name' => 'Jane', 'age' => 30, 'info' => ['active' => false]],
            ['id' => 3, 'name' => 'Bob', 'age' => 35, 'info' => ['active' => true]],
            ['id' => 4, 'name' => 'Alice', 'age' => 28, 'info' => ['active' => true]]
        ];

        $this->testArray2 = [
            'name' => 'John Doe',
            'age' => 30,
            'address' => [
                'street' => '123 Main St',
                'city' => 'Anytown',
                'country' => 'USA'
            ],
            'roles' => ['admin', 'user', 'editor'],
            'settings' => [
                'notifications' => [
                    'email' => true,
                    'sms' => false
                ]
            ],
            'profile' => null
        ];

        $this->testObject = (object)[
            'name' => 'Jane Smith',
            'age' => 28,
            'address' => (object)[
                'street' => '456 Oak Ave',
                'city' => 'Somewhere',
                'country' => 'Canada'
            ]
        ];
    }

    public function testWhereWithCallback(): void
    {
        $result = Arr::where($this->testArray, fn($item) => $item['age'] > 30);
        $this->assertCount(1, $result);
        $this->assertEquals('Bob', reset($result)['name']);
    }

    public function testWhereWithOperator(): void
    {
        $result = Arr::where($this->testArray, 'age', '>', 30);
        $this->assertCount(1, $result);
        $this->assertEquals('Bob', reset($result)['name']);

        // Test with dot notation
        $result = Arr::where($this->testArray, 'info.active', true);
        $this->assertCount(3, $result);
    }

    public function testWhereWithInvalidOperator(): void
    {
        $result = Arr::where($this->testArray, 'age', 'invalid_operator', 30);
        $this->assertEmpty($result);
    }

    public function testWhereIn(): void
    {
        $result = Arr::whereIn($this->testArray, 'id', [1, 3]);
        $this->assertCount(2, $result);
        $this->assertEquals(['John', 'Bob'], array_column($result, 'name'));

        // Test with empty values array
        $result = Arr::whereIn($this->testArray, 'id', []);
        $this->assertEmpty($result);

        // Test with non-existent values
        $result = Arr::whereIn($this->testArray, 'id', [99, 100]);
        $this->assertEmpty($result);
    }

    public function testWhereNot(): void
    {
        $result = Arr::whereNot($this->testArray, 'id', 1);
        $this->assertCount(3, $result);
        $this->assertFalse(in_array('John', array_column($result, 'name')));

        // Test with non-existent key
        $result = Arr::whereNot($this->testArray, 'non_existent', 1);
        $this->assertCount(4, $result);
    }

    public function testFirst(): void
    {
        // Test with callback
        $result = Arr::first($this->testArray, fn($item) => $item['age'] > 30);
        $this->assertEquals('Bob', $result['name']);

        // Test with empty array
        $result = Arr::first([], null, 'default');
        $this->assertEquals('default', $result);

        // Test with callback that returns false for all items
        $result = Arr::first($this->testArray, fn($item) => $item['age'] > 100, 'default');
        $this->assertEquals('default', $result);

        // Test without callback
        $result = Arr::first($this->testArray);
        $this->assertEquals('John', $result['name']);
    }

    public function testFirstWhereWithKeyValue(): void
    {
        // Test basic key-value match
        $result = Arr::firstWhere($this->testArray, 'name', 'Jane');
        $this->assertEquals(2, $result['id']);
        $this->assertEquals('Jane', $result['name']);

        // Test with non-existent value
        $result = Arr::firstWhere($this->testArray, 'name', 'NonExistent');
        $this->assertNull($result);
    }

    public function testFirstWhereWithOperator(): void
    {
        // Test with operator
        $result = Arr::firstWhere($this->testArray, 'age', '>=', 30);
        $this->assertEquals('Jane', $result['name']);

        // Test with dot notation
        $result = Arr::firstWhere($this->testArray, 'info.active');
        $this->assertEquals('John', $result['name']);
    }

    public function testFirstWhereWithCallback(): void
    {
        // Test with callback
        $result = Arr::firstWhere($this->testArray, fn($item) => $item['age'] > 30);
        $this->assertEquals('Bob', $result['name']);

        // Test with callback that returns false for all items
        $result = Arr::firstWhere($this->testArray, fn($item) => $item['age'] > 100);
        $this->assertEquals(null, $result);
    }

    public function testLast(): void
    {
        // Test with callback
        $result = Arr::last($this->testArray, fn($item) => $item['age'] < 30);
        $this->assertEquals('Alice', $result['name']);

        // Test with empty array
        $result = Arr::last([], null, 'default');
        $this->assertEquals('default', $result);

        // Test with callback that returns false for all items
        $result = Arr::last($this->testArray, fn($item) => $item['age'] > 100, 'default');
        $this->assertEquals('default', $result);

        // Test without callback
        $result = Arr::last($this->testArray);
        $this->assertEquals('Alice', $result['name']);
    }

    public function testFilter(): void
    {
        // Test with callback
        $result = Arr::filter($this->testArray, fn($item) => $item['age'] >= 30);
        $this->assertCount(2, $result);
        $this->assertEquals(['Jane', 'Bob'], array_column($result, 'name'));

        // Test without callback (removes null values)
        $arrayWithNull = array_merge($this->testArray, [null]);
        $result = Arr::filter($arrayWithNull);
        $this->assertCount(4, $result);
    }

    public function testMap(): void
    {
        // Test basic mapping
        $result = Arr::map($this->testArray, fn($item) => [
            'id' => $item['id'],
            'full_name' => strtoupper($item['name'])
        ]);
        $this->assertEquals('JOHN', $result[0]['full_name']);

        // Test mapping with keys
        $result = Arr::map($this->testArray, fn($item, $key) => [
            'original_key' => $key,
            'name' => $item['name']
        ]);
        $this->assertEquals(0, $result[0]['original_key']);

        // Test mapping empty array
        $result = Arr::map([], fn($item) => $item);
        $this->assertEmpty($result);
    }


    public function testEach()
    {
        $result = [];
        Arr::each($this->testArray, function($item) use (&$result) {
            $result[] = $item['name'];
        });
        $this->assertEquals(['John', 'Jane', 'Bob', 'Alice'], $result);
    }

    public function testContainsWithCallback()
    {
        $result = Arr::contains($this->testArray, function ($value) {
            return $value['age'] > 30;
        });
        $this->assertTrue($result);

        $result = Arr::contains($this->testArray, function ($value) {
            return $value['age'] > 40;
        });
        $this->assertFalse($result);
    }

    public function testContainsSimpleValue()
    {
        $simpleArray = ['name' => 'Desk', 'price' => 100];
        $this->assertTrue(Arr::contains($simpleArray, 'Desk'));
        $this->assertFalse(Arr::contains($simpleArray, 'Chair'));
    }

    public function testContainsKeyValuePair()
    {
        $this->assertTrue(Arr::contains($this->testArray, 'name', 'John'));
        $this->assertFalse(Arr::contains($this->testArray, 'name', 'Mark'));
    }

    public function testContainsNestedValue()
    {
        $this->assertTrue(Arr::contains($this->testArray, 'info.active', true));
        $this->assertTrue(Arr::contains($this->testArray, 'info.active', false));
    }

    public function testBasicArrayAccess(): void
    {
        $this->assertEquals('John Doe', Arr::dataGet($this->testArray2, 'name'));
        $this->assertEquals(30, Arr::dataGet($this->testArray2, 'age'));
        $this->assertEquals(['admin', 'user', 'editor'], Arr::dataGet($this->testArray2, 'roles'));
    }

    public function testNestedArrayAccess(): void
    {
        $this->assertEquals('123 Main St', Arr::dataGet($this->testArray2, 'address.street'));
        $this->assertEquals('Anytown', Arr::dataGet($this->testArray2, 'address.city'));
        $this->assertEquals('USA', Arr::dataGet($this->testArray2, 'address.country'));
        $this->assertEquals(true, Arr::dataGet($this->testArray2, 'settings.notifications.email'));
        $this->assertEquals(false, Arr::dataGet($this->testArray2, 'settings.notifications.sms'));
    }

    public function testArray2IndexAccess(): void
    {
        $this->assertEquals('admin', Arr::dataGet($this->testArray2, 'roles.0'));
        $this->assertEquals('user', Arr::dataGet($this->testArray2, 'roles.1'));
        $this->assertEquals('editor', Arr::dataGet($this->testArray2, 'roles.2'));
    }

    public function testObjectAccess(): void
    {
        $this->assertEquals('Jane Smith', Arr::dataGet($this->testObject, 'name'));
        $this->assertEquals(28, Arr::dataGet($this->testObject, 'age'));
    }

    public function testNestedObjectAccess(): void
    {
        $this->assertEquals('456 Oak Ave', Arr::dataGet($this->testObject, 'address.street'));
        $this->assertEquals('Somewhere', Arr::dataGet($this->testObject, 'address.city'));
        $this->assertEquals('Canada', Arr::dataGet($this->testObject, 'address.country'));
    }

    public function testNonExistentKey(): void
    {
        $this->assertNull(Arr::dataGet($this->testArray2, 'nonexistent'));
        $this->assertNull(Arr::dataGet($this->testArray2, 'address.nonexistent'));
    }

    public function testDefaultValue(): void
    {
        $this->assertEquals('default', Arr::dataGet($this->testArray2, 'nonexistent', 'default'));
        $this->assertEquals('default', Arr::dataGet($this->testArray2, 'address.nonexistent', 'default'));
        $this->assertEquals('default', Arr::dataGet($this->testArray2, 'nonexistent.key', 'default'));
    }

    public function testCallableDefault(): void
    {
        $default = fn() => 'calculated default';
        $this->assertEquals('calculated default', Arr::dataGet($this->testArray2, 'nonexistent', $default));
    }

    public function testNullValues(): void
    {
        $this->assertNull(Arr::dataGet($this->testArray2, 'profile'));
        $this->assertEquals('default', Arr::dataGet($this->testArray2, 'profile.image', 'default'));
        $this->assertNull(Arr::dataGet(null, 'any.key'));

        $value = Arr::dataGet($this->testArray2, 'profile.image', fn() => 'default');
    }
}