<?php

namespace Avmg\PhpSimpleUtilities\Unit;

use PHPUnit\Framework\TestCase;
use Avmg\PhpSimpleUtilities\Collection;
use TypeError;
use UnexpectedValueException;

class CollectionTest extends TestCase
{
    public function testCollectMethodReturnsNewInstance()
    {
        $collection = Collection::collect(['first', 'second']);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(2, $collection->count());
    }

    public function testEachMethodAppliesCallbackToItems()
    {
        $collection = Collection::collect([1, 2, 3]);
        $sum = 0;
        $collection->each(function ($item) use (&$sum) {
            $sum += $item;
        });

        $this->assertEquals(6, $sum);
    }

    public function testMapMethodTransformsItems()
    {
        $collection = Collection::collect([1, 2, 3]);
        $newCollection = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6], $newCollection->all());
    }

    public function testFilterMethodFiltersItems()
    {
        $collection = Collection::collect([1, 2, 3, 4, 5]);
        $filtered = $collection->filter(function ($item) {
            return $item > 3;
        });

        $this->assertEquals([4, 5], array_values($filtered->all())); // array_values to reset keys
    }

    public function testFirstMethodReturnsFirstItemThatPassesGivenTruthTest()
    {
        $collection = Collection::collect([1, 2, 3]);
        $first = $collection->first(function ($item) {
            return $item > 1;
        });

        $this->assertEquals(2, $first);
    }

    public function testFirstWithoutCallback()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $this->assertEquals('a', $collection->first());
    }

    public function testLastWithCallback()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $lastEven = $collection->last(function ($item) {
            return $item % 2 === 0;
        });
        $this->assertEquals(4, $lastEven);
    }

    public function testLastWithoutCallback()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $this->assertEquals('c', $collection->last());
    }

    public function testPluckMethod()
    {
        $collection = Collection::collect([
            ['product_id' => 'prod-100', 'name' => 'Desk'],
            ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]);

        $plucked = $collection->pluck('name');

        $this->assertEquals(['Desk', 'Chair'], array_values($plucked->all())); // array_values to reset keys
    }

    public function testPushAddsItemToEndOfCollection()
    {
        $collection = Collection::collect([1, 2, 3]);
        $collection->push(4);
        $this->assertEquals(4, $collection->count());
        $this->assertEquals(4, $collection->get(3));
    }

    public function testCountReturnsCorrectNumberOfItems()
    {
        $collection = Collection::collect(['a', 'b', 'c']);
        $this->assertEquals(3, $collection->count());
    }

    public function testGetReturnsDefaultValueForNonexistentKey()
    {
        $collection = Collection::collect(['name' => 'John', 'age' => 30]);
        $this->assertEquals('default', $collection->get('nonexistent', 'default'));
    }

    public function testDotFlattensMultiDimensionalArrays()
    {
        $collection = Collection::collect([
            'person' => ['name' => 'John', 'age' => 30, 'hobbies' => ['Reading', 'Cycling']]
        ]);
        $flattened = $collection->dot();
        $this->assertTrue(isset($flattened['person.name']));
        $this->assertTrue(isset($flattened['person.hobbies.0']));
        $this->assertEquals('John', $flattened['person.name']);
        $this->assertEquals('Reading', $flattened['person.hobbies.0']);
    }

    public function testToArrayConvertsCollectionToArray()
    {
        $collection = Collection::collect(['name' => 'John', 'age' => 30]);
        $array = $collection->toArray();
        $this->assertIsArray($array);
        $this->assertEquals(['name' => 'John', 'age' => 30], $array);
    }

    public function testToJsonEncodesCollectionToJson()
    {
        $collection = Collection::collect(['name' => 'John', 'age' => 30]);
        $json = $collection->toJson();
        $this->assertJson($json);
        $this->assertEquals('{"name":"John","age":30}', $json);
    }

    public function testEnsureThrowsExceptionForInvalidType()
    {
        $this->expectException(UnexpectedValueException::class);
        $collection = Collection::collect([1, 2, 'three']);
        $collection->ensure('int');
    }

    public function testArrayAccessInterface()
    {
        $collection = Collection::collect(['first' => 'John', 'second' => 'Doe']);
        // Offset exists
        $this->assertTrue(isset($collection['first']));
        // Offset get
        $this->assertEquals('John', $collection['first']);
        // Offset set
        $collection['third'] = 'Smith';
        $this->assertEquals('Smith', $collection['third']);
        // Offset unset
        unset($collection['second']);
        $this->assertFalse(isset($collection['second']));
    }

    public function testMergeCollections()
    {
        $collection1 = Collection::collect(['name' => 'John']);
        $collection2 = Collection::collect(['age' => 30]);
        $merged = $collection1->merge($collection2);
        $this->assertEquals(['name' => 'John', 'age' => 30], $merged->all());
    }

    public function testChunkCreatesSmallerCollections()
    {
        $collection = Collection::collect(range(1, 10));
        $chunks = $collection->chunk(4);
        // Ensure $chunks is a Collection of Collections and then count its items
        $this->assertCount(3, $chunks->all()); // Use ->all() to get the array of chunks
        // Ensure the first chunk contains the correct items
        $this->assertEquals([1, 2, 3, 4], $chunks->all()[0]->all());
    }

    public function testRejectMethodFiltersOutItemsCorrectly()
    {
        $collection = Collection::collect([1, 2, 3, 4, 5]);
        $rejected = $collection->reject(function ($item) {
            return $item > 3;
        });

        $this->assertEquals([1, 2, 3], array_values($rejected->all())); // Use array_values to reset keys
    }

    public function testTakeMethodWithNegativeIntegers()
    {
        $collection = Collection::collect([1, 2, 3, 4, 5]);
        $lastTwo = $collection->take(-2);

        $this->assertEquals([4, 5], array_values($lastTwo->all()));
    }

    public function testEnsureMethodWithMultipleTypes()
    {
        $collection = Collection::collect(['string', 100, new Collection()]);
        $collection->ensure(['string', 'integer', Collection::class]);
        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testMergeWithOverlappingKeys()
    {
        $collection1 = Collection::collect(['name' => 'John', 'age' => 25]);
        $collection2 = Collection::collect(['age' => 30, 'city' => 'New York']);
        $merged = $collection1->merge($collection2);

        $this->assertEquals(['name' => 'John', 'age' => 30, 'city' => 'New York'], $merged->all());
    }

    public function testPluckMethodWithNestedKeys()
    {
        $collection = Collection::collect([
            ['product' => ['id' => 'prod-100', 'name' => 'Desk']],
            ['product' => ['id' => 'prod-200', 'name' => 'Chair']],
        ]);

        $pluckedNames = $collection->pluck('product.name');

        $this->assertEquals(['Desk', 'Chair'], array_values($pluckedNames->all()));
    }

    public function testToArrayWithNestedCollections()
    {
        $nestedCollection = Collection::collect(['nested' => Collection::collect(['key' => 'value'])]);
        $array = $nestedCollection->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(['nested' => ['key' => 'value']], $array);
    }

    public function testToJsonEncodesWithCustomOptions()
    {
        $collection = Collection::collect(['data' => ['name' => 'John', 'age' => 30]]);
        $json = $collection->toJson(JSON_PRETTY_PRINT);

        $expectedJson = "{\n    \"data\": {\n        \"name\": \"John\",\n        \"age\": 30\n    }\n}";
        $this->assertEquals($expectedJson, $json);
    }

    public function testPipeMethodTransformsCollection()
    {
        $collection = Collection::collect([1, 2, 3]);
        $result = $collection->pipe(function ($collection) {
            return array_sum($collection->all());
        });

        $this->assertEquals(6, $result);
    }

    public function testTapMethodAllowsSideEffects()
    {
        $originalCollection = Collection::collect([1, 2, 3]);
        $sideEffect = 0;

        // Corrected to manually calculate the sum to demonstrate side effects
        $tappedCollection = $originalCollection->tap(function ($collection) use (&$sideEffect) {
            foreach ($collection->all() as $item) {
                $sideEffect += $item;
            }
        });

        // Asserts that the side effect (sum calculation) occurred as expected
        $this->assertEquals(6, $sideEffect);
        // Asserts that the original collection remains unchanged after tap
        $this->assertEquals($originalCollection->all(), $tappedCollection->all());
    }

    public function testFilterMethodWithNonCallableValue()
    {
        $collection = Collection::collect([1, 2, 3, 4, 5]);

        $this->expectException(TypeError::class);
        $collection->filter("nonCallableValue");
    }

    public function testGetReturnsClosureDefaultValue()
    {
        $collection = Collection::collect(['name' => 'John', 'age' => 30]);
        $defaultValue = function() { return 'not found'; };
        $value = $collection->get('nonexistent', $defaultValue);

        $this->assertEquals('not found', $value);
    }

    public function testTransformMethodAltersCollectionInPlace()
    {
        $collection = Collection::collect([1, 2, 3]);

        // Apply a transformation that multiplies each item by 2
        $collection->transform(function ($item) {
            return $item * 2;
        });

        // Assert that the original collection has been modified as expected
        $this->assertEquals([2, 4, 6], $collection->all());
    }

    public function testTransformMethodOnEmptyCollection()
    {
        $collection = Collection::collect([]);

        // Attempt to transform an empty collection
        $collection->transform(function ($item) {
            return $item * 2;
        });

        // Assert that the collection remains empty
        $this->assertEquals([], $collection->all());
    }
}
