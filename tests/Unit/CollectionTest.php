<?php

namespace Avmg\PhpSimpleUtilities\Unit;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Avmg\PhpSimpleUtilities\Collection;
use stdClass;
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

    public function testFilterMethodWithNoCallback()
    {
        $collection = new Collection([1, false, 3, null, 5]);
        $filtered = $collection->filter();
        $this->assertEquals([1, 3, 5], array_values($filtered->all()));
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

    public function testToArrayWithSimpleArray()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $collection->toArray());
    }

    public function testToArrayWithNestedCollections()
    {
        $nestedCollection = new Collection(['nested' => new Collection(['a', 'b'])]);
        $expected = ['nested' => ['a', 'b']];
        $this->assertEquals($expected, $nestedCollection->toArray());
    }

    public function testToArrayWithStdClass()
    {
        $object = new stdClass();
        $object->a = 'value';
        $collection = new Collection(['object' => $object]);
        $expected = ['object' => ['a' => 'value']];
        $this->assertEquals($expected, $collection->toArray());
    }

    public function testToArrayWithIterableObject()
    {
        $object = new ArrayObject(['a', 'b', 'c']);
        $collection = new Collection(['iterable' => $object]);
        $expected = ['iterable' => ['a', 'b', 'c']];
        $this->assertEquals($expected, $collection->toArray());
    }

    public function testToArrayWithObjectHavingToArrayMethod()
    {
        $object = new class {
            public function toArray()
            {
                return ['property' => 'value'];
            }
        };
        $collection = new Collection(['object' => $object]);
        $expected = ['object' => ['property' => 'value']];
        $this->assertEquals($expected, $collection->toArray());
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

    public function testReduceWithInitialValue()
    {
        $collection = new Collection([1, 2, 3, 4]);

        $product = $collection->reduce(function ($carry, $item) {
            return $carry * $item;
        }, 1);

        $this->assertEquals(24, $product);
    }

    public function testPipeThroughWithSingleCallable()
    {
        $collection = new Collection([100]);
        $result = $collection->pipeThrough([
            function ($collection) {
                return $collection->reduce(function ($carry, $item) {
                    return $carry + $item;
                }, 0);
            }
        ]);

        $this->assertEquals(100, $result);
    }

    public function testPipeThroughWithMultipleCallables()
    {
        $collection = new Collection([1, 2, 3]);
        $result = $collection->pipeThrough([
            function ($collection) {
                // Increment each item
                return $collection->map(function ($item) {
                    return $item + 1;
                });
            },
            function ($collection) {
                // Sum all items
                return $collection->reduce(function ($carry, $item) {
                    return $carry + $item;
                }, 0);
            }
        ]);

        $this->assertEquals(9, $result); // (1+1) + (2+1) + (3+1) = 9
    }

    public function testPutAddsNewItem()
    {
        $collection = new Collection();
        $collection->put('key1', 'value1');
        $this->assertEquals('value1', $collection->get('key1'), 'The value1 should be put at key1');
    }

    public function testPutUpdatesExistingItem()
    {
        $collection = new Collection(['key1' => 'value1']);
        $collection->put('key1', 'value2');
        $this->assertEquals('value2', $collection->get('key1'), 'The value at key1 should be updated to value2');
    }

    public function testPutWithNumericKey()
    {
        $collection = new Collection();
        $collection->put(0, 'numericKey');
        $this->assertEquals('numericKey', $collection->get(0), 'The value should be put at numeric key 0');
    }

    public function testPutAppendsWithNullKey()
    {
        $collection = new Collection(['key1' => 'value1', 'key2' => 'value2']);
        $collection->put(null, 'appendedValue');
        $endValue = $collection->last(); // Assuming last() method correctly retrieves the last item
        $this->assertEquals('appendedValue', $endValue, 'The value should be appended to the collection');
    }

    public function testPutEnsuresCorrectCollectionSize()
    {
        $collection = new Collection(['key1' => 'value1']);
        $collection->put('key2', 'value2');
        $collection->put(0, 'numericKey');
        $collection->put(null, 'appendedValue');
        $this->assertCount(4, $collection, 'The collection should contain exactly 4 items after all operations');
    }


    public function testFirstWithNoCallback()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(1, $collection->first());
    }

    public function testFirstWithCallback()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $firstEven = $collection->first(function ($item) {
            return $item % 2 === 0;
        });
        $this->assertEquals(2, $firstEven);
    }

    public function testFirstReturnsDefault()
    {
        $collection = new Collection();
        $default = 'default';
        $this->assertEquals($default, $collection->first(null, $default));
    }

    public function testLastWithNoCallback()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(3, $collection->last());
    }

    public function testLastReturnsDefault()
    {
        $collection = new Collection();
        $default = 'default';
        $this->assertEquals($default, $collection->last(null, $default));
    }

    public function testSumWithoutCallback()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertEquals(10, $collection->sum());
    }

    public function testSumWithCallback()
    {
        $collection = new Collection([1, 2, 3, 4]);
        $sum = $collection->sum(function ($item) {
            return $item * 2;
        });
        $this->assertEquals(20, $sum);
    }

    public function testSumWithEmptyCollection()
    {
        $collection = new Collection();
        $this->assertEquals(0, $collection->sum());
    }

    public function testSumWithNonNumericValues()
    {
        $collection = new Collection(['a', 'b', 'c']);
        $sum = $collection->sum(function ($item) {
            return is_numeric($item) ? $item : 0;
        });
        $this->assertEquals(0, $sum);
    }

    public function testIsEmptyReturnsTrueForEmptyCollection()
    {
        $collection = new Collection();
        $this->assertTrue($collection->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyCollection()
    {
        $collection = new Collection(['item']);
        $this->assertFalse($collection->isEmpty());
    }

    public function testIsNotEmptyReturnsFalseForEmptyCollection()
    {
        $collection = new Collection();
        $this->assertFalse($collection->isNotEmpty());
    }

    public function testIsNotEmptyReturnsTrueForNonEmptyCollection()
    {
        $collection = new Collection(['item']);
        $this->assertTrue($collection->isNotEmpty());
    }

    public function testValuesMethodReindexesCollection()
    {
        $collection = new Collection(['first' => 'apple', 'second' => 'banana']);
        $values = $collection->values();
        $expected = ['apple', 'banana'];

        $this->assertEquals($expected, $values->toArray());
    }

    public function testValuesMethodReturnsNewCollectionInstance()
    {
        $original = new Collection(['first' => 'apple', 'second' => 'banana']);
        $values = $original->values();

        $this->assertInstanceOf(Collection::class, $values);
        $this->assertNotSame($original, $values);
    }

    public function testUniqueWithNoParameters()
    {
        $collection = Collection::collect([1, 2, 2, 3, 4, 4, 5]);
        $unique = $collection->unique();

        $this->assertEquals([1, 2, 3, 4, 5], array_values($unique->toArray()));
    }

    public function testUniqueWithStringKey()
    {
        $collection = Collection::collect([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
            ['id' => 1, 'name' => 'John'],
        ]);
        $unique = $collection->unique('id');

        $this->assertCount(2, $unique);
        $this->assertEquals([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']], array_values($unique->toArray()));
    }

    public function testUniqueWithStrict()
    {
        $collection = Collection::collect([1, '1', 2]);
        $unique = $collection->unique(null, true);

        $this->assertEquals([1, '1', 2], array_values($unique->toArray()));
    }

    public function testUniqueWithCallback()
    {
        $collection = Collection::collect([1, 2, 3, 4, 5]);
        $unique = $collection->unique(fn($item) => $item % 2);

        $this->assertEquals([1, 2], array_values($unique->toArray()));
    }

    public function testUniqueWithNestedArrays()
    {
        $collection = Collection::collect([
            ['product' => ['id' => 1, 'name' => 'Apple']],
            ['product' => ['id' => 2, 'name' => 'Banana']],
            ['product' => ['id' => 1, 'name' => 'Apple']],
        ]);
        $unique = $collection->unique('product.id');

        $this->assertCount(2, $unique);
        $this->assertEquals([
            ['product' => ['id' => 1, 'name' => 'Apple']],
            ['product' => ['id' => 2, 'name' => 'Banana']],
        ], array_values($unique->toArray()));
    }

    public function testSimpleFlatten()
    {
        $collection = Collection::collect([1, [2, 3], [4, [5, 6]]]);
        $flattened = $collection->flatten();

        $this->assertEquals([1, 2, 3, 4, 5, 6], array_values($flattened->toArray()));
    }

    public function testFlattenWithDepth()
    {
        $collection = Collection::collect([1, [2, 3], [4, [5, 6]]]);
        $flattened = $collection->flatten(1);

        $this->assertEquals([1, 2, 3, 4, [5, 6]], array_values($flattened->toArray()));
    }
}
