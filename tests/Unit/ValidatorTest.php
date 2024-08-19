<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Avmg\PhpSimpleUtilities\Validator;

class ValidatorTest extends TestCase
{
    /**
     * Test that required fields are validated correctly.
     */
    public function testRequiredFieldValidation(): void
    {
        $data = ['name' => 'John Doe'];
        $rules = ['name' => 'required', 'age' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field is required.', $errors['age']);
    }

    /**
     * Test that required fields handle empty arrays correctly.
     */
    public function testRequiredFieldHandlesEmptyArray(): void
    {
        $data = ['items' => []];
        $rules = ['items' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('items', $errors);
        $this->assertContains('The items field is required.', $errors['items']);
    }

    /**
     * Test that required fields handle the value '0' correctly.
     */
    public function testRequiredFieldHandlesZeroValue(): void
    {
        $data = ['age' => '0'];
        $rules = ['age' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test that required fields fail on empty string value.
     */
    public function testRequiredFieldFailsOnEmptyString(): void
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field is required.', $errors['name']);
    }

    /**
     * Test that required fields fail on null value.
     */
    public function testRequiredFieldFailsOnNullValue(): void
    {
        $data = ['name' => null];
        $rules = ['name' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field is required.', $errors['name']);
    }

    /**
     * Test that string fields are validated correctly.
     */
    public function testStringFieldValidation(): void
    {
        $data = ['name' => 12345];
        $rules = ['name' => 'string'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must be a string.', $errors['name']);
    }

    /**
     * Test that numeric fields are validated correctly.
     */
    public function testNumericFieldValidation(): void
    {
        $data = ['age' => 'twenty'];
        $rules = ['age' => 'numeric'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field must be a numeric value.', $errors['age']);
    }

    /**
     * Test that array fields are validated correctly.
     */
    public function testArrayFieldValidation(): void
    {
        $data = ['items' => 'not an array'];
        $rules = ['items' => 'array'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('items', $errors);
        $this->assertContains('The items field must be an array.', $errors['items']);
    }

    /**
     * Test that min string length is validated correctly.
     */
    public function testMinStringLengthValidation(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'min:5'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must be at least 5 characters long.', $errors['name']);
    }

    /**
     * Test that min numeric value is validated correctly.
     */
    public function testMinNumericValueValidation(): void
    {
        $data = ['age' => 10];
        $rules = ['age' => 'min:18'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field must be at least 18.', $errors['age']);
    }

    /**
     * Test that max string length is validated correctly.
     */
    public function testMaxStringLengthValidation(): void
    {
        $data = ['name' => 'Johnathan'];
        $rules = ['name' => 'max:5'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must not exceed 5 characters.', $errors['name']);
    }

    /**
     * Test that max numeric value is validated correctly.
     */
    public function testMaxNumericValueValidation(): void
    {
        $data = ['age' => 25];
        $rules = ['age' => 'max:18'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field must not exceed 18.', $errors['age']);
    }

    /**
     * Test that multiple rules are validated correctly.
     */
    public function testMultipleRulesValidation(): void
    {
        $data = ['name' => 'John', 'age' => 'twenty'];
        $rules = ['name' => 'required|string|min:5', 'age' => 'required|numeric'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();

        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must be at least 5 characters long.', $errors['name']);

        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field must be a numeric value.', $errors['age']);
    }

    /**
     * Test that valid data passes validation.
     */
    public function testValidDataPassesValidation(): void
    {
        $data = ['name' => 'John Doe', 'age' => 30];
        $rules = ['name' => 'required|string|min:5|max:50', 'age' => 'required|numeric|min:18|max:65'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test overwriting error message
     */
    public function testOverwriteErrorMessage(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'min:5'];
        $messages = ['min.string' => '(:attribute)'];
        $validator = new Validator($data, $rules, $messages);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('(name)', $errors['name']);
    }

    /**
     * Test that between rule validates numeric values correctly.
     */
    public function testBetweenRuleValidatesNumericValues(): void
    {
        $data = ['age' => 25];
        $rules = ['age' => 'between:18,30'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        $data = ['age' => 35];
        $rules = ['age' => 'between:18,30'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('age', $errors);
        $this->assertContains('The age field must be between 18 and 30.', $errors['age']);
    }

    /**
     * Test that between rule validates string lengths correctly.
     */
    public function testBetweenRuleValidatesStringLengths(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'between:3,5'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        $data = ['name' => 'Jonathan'];
        $rules = ['name' => 'between:3,5'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must be between 3 and 5 characters.', $errors['name']);
    }

    /**
     * Test that in rule validates values correctly.
     */
    public function testInRuleValidatesValues(): void
    {
        $data = ['status' => 'active'];
        $rules = ['status' => 'in:active,inactive'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        $data = ['status' => 'pending'];
        $rules = ['status' => 'in:active,inactive'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('status', $errors);
        $this->assertContains('The status field must be one of the following values: active, inactive.', $errors['status']);
    }

    /**
     * test boolean rule, must be true,false,0,1,'0','1'
     */
    public function testBooleanRule(): void
    {
        $data = ['status' => 'active'];
        $rules = ['status' => 'boolean'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('status', $errors);
        $this->assertContains('The status field must be a boolean value.', $errors['status']);

        $data = ['status' => 1];
        $rules = ['status' => 'boolean'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        $data = ['status' => '1'];
        $rules = ['status' => 'boolean'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test that required fields in nested arrays are validated correctly.
     */
    public function testRequiredFieldInNestedArray(): void
    {
        $data = [
            'author' => [
                'name' => 'John Doe',
                'details' => [
                    'email' => 'john.doe@example.com'
                ]
            ]
        ];
        $rules = ['author.details.email' => 'required'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        // Test failure case
        $data = [
            'author' => [
                'name' => 'John Doe',
                'details' => []
            ]
        ];
        $validator = new Validator($data, $rules);
        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('author.details.email', $errors);
        $this->assertContains('The author.details.email field is required.', $errors['author.details.email']);
    }

    /**
     * Test that min rule for nested arrays validates correctly.
     */
    public function testMinRuleForNestedArray(): void
    {
        $data = [
            'author' => [
                'details' => [
                    'age' => 25
                ]
            ]
        ];
        $rules = ['author.details.age' => 'min:18'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        // Test failure case
        $data = [
            'author' => [
                'details' => [
                    'age' => 15
                ]
            ]
        ];
        $validator = new Validator($data, $rules);
        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('author.details.age', $errors);
        $this->assertContains('The author.details.age field must be at least 18.', $errors['author.details.age']);
    }

    /**
     * Test that max rule for wildcard fields validates correctly.
     */
    public function testMaxRuleForWildcardFields(): void
    {
        $data = [
            'person' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
                ['name' => 'Charls']
            ]
        ];
        $rules = ['person.*.name' => 'max:6'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        // Test failure case
        $data = [
            'person' => [
                ['name' => 'Alice'],
                ['name' => 'Bob'],
                ['name' => 'Charleston']
            ]
        ];
        $validator = new Validator($data, $rules);
        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('person.2.name', $errors);
        $this->assertContains('The person.2.name field must not exceed 6 characters.', $errors['person.2.name']);
    }

    /**
     * Test deep nesting array validation
     */
    public function testDeepNestingArrayValidation(): void
    {
        $data = [
            'person' => [
                [
                    'name' => 'Alice',
                    'details' => [
                        ['age' => 25],
                        ['age' => 30],
                        ['age' => 35]
                    ]
                ],
                [
                    'name' => 'Bob',
                    'details' => [
                        ['age' => 20],
                        ['age' => 25],
                        ['age' => 30]
                    ]
                ]
            ]
        ];

        $rules = [
            'person.*.details.*.age' => 'min:18|max:30'
        ];

        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();

        $this->assertArrayHasKey('person.0.details.2.age', $errors);
        $this->assertContains('The person.0.details.2.age field must not exceed 30.', $errors['person.0.details.2.age']);

        //unset the one that's wrong and assert the validation succeeds
        unset($data['person'][0]['details'][2]);
        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->validate());
    }


    /**
     * Test that validation rules can be passed as an array.
     */
    public function testValidationRulesAsArray(): void
    {
        $data = ['number' => 3, 'username' => 'john'];
        $rules = [
            'number' => ['required', 'numeric'],
            'username' => ['required', 'string', 'min:5']
        ];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();

        $this->assertArrayHasKey('username', $errors);
        $this->assertContains('The username field must be at least 5 characters long.', $errors['username']);
    }

    /**
     * Test that required_if rule validates values correctly.
     */
    public function testRequiredIfRuleValidatesValues(): void
    {
        $data = ['status' => 'active', 'reason' => ''];
        $rules = ['reason' => 'required_if:status,active'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('reason', $errors);
        $this->assertContains('The reason field is required when status is active.', $errors['reason']);

        $data = ['status' => 'inactive', 'reason' => ''];
        $rules = ['reason' => 'required_if:status,active'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test that required_unless rule validates values correctly.
     */
    public function testRequiredUnlessRuleValidatesValues(): void
    {
        $data = ['status' => 'active', 'reason' => ''];
        $rules = ['reason' => 'required_unless:status,inactive'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('reason', $errors);
        $this->assertContains('The reason field is required unless status is inactive.', $errors['reason']);

        $data = ['status' => 'inactive', 'reason' => ''];
        $rules = ['reason' => 'required_unless:status,inactive'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test min and max and between rules with null values
     */
    public function testMinMaxBetweenWithNullValues(): void
    {
        $data = ['age' => null];
        $rules = ['age' => 'min:18|max:65|between:18,65'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test that nullable fields are not required and validation passes when the field is not provided.
     */
    public function testNullableFieldNotProvided(): void
    {
        $data = ['title' => 'A valid title'];
        $rules = [
            'version' => 'nullable|numeric',
            'title' => 'required|string'
        ];

        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());
    }

    /**
     * Test that nullable fields are validated correctly when provided with a valid value.
     */
    public function testNullableFieldProvidedWithValidValue(): void
    {
        $data = ['version' => 1, 'title' => 'A valid title'];
        $rules = ['version' => 'nullable|numeric', 'title' => 'required|string'];
        $validator = new Validator($data, $rules);

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->errors());

        //make version a text instead and see if it fails
        $data = ['version' => 'one', 'title' => 'A valid title'];
        $rules = ['version' => 'nullable|numeric', 'title' => 'required|string'];
        $validator = new Validator($data, $rules);

        $this->assertFalse($validator->validate());
        $errors = $validator->errors();
        $this->assertArrayHasKey('version', $errors);
    }
}
