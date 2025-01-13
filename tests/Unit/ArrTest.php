<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities\Unit;

use PHPUnit\Framework\TestCase;
use Avmg\PhpSimpleUtilities\Arr;

class ArrTest extends TestCase
{
    private array $testArray;

    protected function setUp(): void
    {
        $this->testArray = [
            ['id' => 1, 'name' => 'John', 'age' => 25, 'info' => ['active' => true]],
            ['id' => 2, 'name' => 'Jane', 'age' => 30, 'info' => ['active' => false]],
            ['id' => 3, 'name' => 'Bob', 'age' => 35, 'info' => ['active' => true]],
            ['id' => 4, 'name' => 'Alice', 'age' => 28, 'info' => ['active' => true]]
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

    public function testContains()
    {
        $this->assertTrue(Arr::contains($this->testArray, 'name', 'John'));
        $this->assertFalse(Arr::contains($this->testArray, 'name', 'Non-existent'));
    }
}