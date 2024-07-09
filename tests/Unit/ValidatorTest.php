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
}
