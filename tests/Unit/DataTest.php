<?php

namespace Avmg\PhpSimpleUtilities\Unit;

use Avmg\PhpSimpleUtilities\Collection;
use Avmg\PhpSimpleUtilities\Data;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

enum TestEnum: string {
    case TestValue = 'Test';
}

class EnumData extends Data
{
    public function __construct(
        public TestEnum $type,
    ) {
    }
}

enum TagEnum: string {
    case electronics = 'electronics';
    case clothing = 'clothing';
    case books = 'books';
}

class UserData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
        public ?bool $hasDog,
    ) {
    }
}

class ProductData extends Data
{
    public function __construct(
        public string $name,
        public float $price,
        public TagEnum $tag,
    ) {
    }
}


class AddressData extends Data
{
    public function __construct(
        public string $street,
        public string $city,
    ) {
    }
}

class CustomerData extends Data
{
    public function __construct(
        public string $name,
        public AddressData $address,
    ) {
    }
}

class CustomerDataWithCollection extends Data
{
    /**
     * @param string $name
     * @param Collection<int, AddressData> $address
     */
    public function __construct(
        public string $name,
        public Collection $address,
    ) {
    }
}

class TeamData extends Data
{
    /**
     * @param UserData[] $members
     */
    public function __construct(
        public array $members,
    ) {
    }
}

class DataTest extends TestCase
{
    public function testInvalidEnumValueThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        EnumData::from(['type' => 'Invalid']);
    }

    public function testNestedDataObjectCasting()
    {
        $nestedAttributes = [
            'name' => 'Nested User',
            'age' => 25,
            'address' => [
                'street' => 'Nested 123 Elm St',
                'city' => 'Nested Springfield',
            ],
        ];
        $instance = CustomerData::from($nestedAttributes);

        $this->assertInstanceOf(CustomerData::class, $instance);
        $this->assertEquals('Nested User', $instance->name);
        $this->assertInstanceOf(AddressData::class, $instance->address);
        $this->assertEquals('Nested 123 Elm St', $instance->address->street);
        $this->assertEquals('Nested Springfield', $instance->address->city);
    }

    public function testAutomaticTypeCastingOfPrimitiveTypes()
    {
        $attributes = ['name' => 'John Doe', 'age' => '30'];
        $instance = UserData::from($attributes);

        $this->assertIsInt($instance->age);
        $this->assertEquals(30, $instance->age);
    }

    public function testAutomaticTypeCastingOfEnum()
    {
        $attributes = ['name' => 'Gadget', 'price' => 19.99, 'tag' => 'electronics'];
        $productData = ProductData::from($attributes);

        $this->assertEquals(TagEnum::electronics, $productData->tag);
    }

    public function testNestedDataObjectInstantiation()
    {
        $attributes = [
            'name' => 'Jane Doe',
            'address' => [
                'street' => '123 Elm St',
                'city' => 'Springfield',
            ],
        ];
        $customerData = CustomerData::from($attributes);

        $this->assertInstanceOf(AddressData::class, $customerData->address);
        $this->assertEquals('Springfield', $customerData->address->city);
    }

    public function testNestedDataObjectInstantiationWithPreInitiatedDataObject() {
        $attributes = [
            'name' => 'Jane Doe',
            'address' => AddressData::from([
                'street' => '123 Elm St',
                'city' => 'Springfield',
            ]),
        ];
        $customerData = CustomerData::from($attributes);

        $this->assertInstanceOf(AddressData::class, $customerData->address);
        $this->assertEquals('Springfield', $customerData->address->city);
    }

    public function testToArrayWithEnum()
    {
        $attributes = [
            'name' => 'Gadget',
            'price' => 19.99,
            'tag' => TagEnum::electronics,
        ];
        $productData = ProductData::from($attributes);
        $array = $productData->toArray();

        $expectedArray = [
            'name' => 'Gadget',
            'price' => 19.99,
            'tag' => 'electronics',
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testValidationThrowsExceptionForMissingAttributes()
    {
        $this->expectException(InvalidArgumentException::class);
        UserData::from(['name' => 'John Doe']);
    }

    public function testCustomClassWithCollectionToArray()
    {
        $attributes = [
            'name' => 'Jane Doe',
            'address' => Collection::collect([
                AddressData::from(['street' => '123 Elm St', 'city' => 'Springfield'])
            ]),
        ];
        $customerData = CustomerDataWithCollection::from($attributes);
        $array = $customerData->toArray();

        $expectedArray = [
            'name' => 'Jane Doe',
            'address' => [
                ['street' => '123 Elm St', 'city' => 'Springfield']
            ],
        ];
        $this->assertEquals($expectedArray, $array);
    }

    // Test jsonSerialize Method
    public function testJsonSerialize()
    {
        $attributes = [
            'name' => 'Gadget',
            'price' => 19.99,
            'tag' => TagEnum::electronics,
        ];
        $productData = ProductData::from($attributes);
        $json = json_encode($productData);

        $expectedJson = '{"name":"Gadget","price":19.99,"tag":"electronics"}';
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }

    // Testing Arrays of Nested Data Objects (if applicable)
    public function testArraysWithNestedDataObjects()
    {
        // Assuming Collection can hold an array of AddressData objects
        $addresses = Collection::collect([
            AddressData::from(['street' => '123 Elm St', 'city' => 'Springfield']),
            AddressData::from(['street' => '456 Oak St', 'city' => 'Hill Valley'])
        ]);
        $customerData = CustomerDataWithCollection::from([
            'name' => 'John Doe',
            'address' => $addresses
        ]);
        $array = $customerData->toArray();

        $expectedArray = [
            'name' => 'John Doe',
            'address' => [
                ['street' => '123 Elm St', 'city' => 'Springfield'],
                ['street' => '456 Oak St', 'city' => 'Hill Valley']
            ]
        ];
        $this->assertEquals($expectedArray, $array);
    }

    public function testInvalidTypeForNestedObject()
    {
        $this->expectException(InvalidArgumentException::class);
        CustomerData::from([
            'name' => 'Invalid User',
            'address' => 'This is not an address object or array' // Invalid type
        ]);
    }

    public function testOptionalAndDefaultParameters()
    {
        // Assuming UserData has an optional parameter with a default value
        $userData = UserData::from(['name' => 'Optional User', 'age' => 18]);
        $this->assertEquals(null, $userData->hasDog);
    }

    public function testObjectWithArrayOfTypeAndToArray()
    {
        $teamMembers = [
            UserData::from(['name' => 'John Doe', 'age' => 30]),
            UserData::from(['name' => 'Jane Doe', 'age' => 25]),
        ];

        // Assuming TeamData class exists and can hold an array of UserData objects
        $teamData = new TeamData($teamMembers);
        $array = $teamData->toArray();

        $expectedArray = [
            'members' => [
                ['name' => 'John Doe', 'age' => 30, 'hasDog' => null],
                ['name' => 'Jane Doe', 'age' => 25, 'hasDog' => null],
            ]
        ];

        $this->assertEquals($expectedArray, $array);
    }
}