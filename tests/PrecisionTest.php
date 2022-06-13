<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Keywords\PrecisionKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\PrecisionKeywordParser
 */
final class PrecisionTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function test01(): void
    {
        $schema = <<<'JSON'
{
    "type": "number",
    "precision": 2
}
JSON;

        $validator = new SystopiaValidator();
        static::assertTrue($validator->validate(2, $schema)->isValid());
        static::assertTrue($validator->validate(2.3, $schema)->isValid());
        static::assertTrue($validator->validate(2.34, $schema)->isValid());
        static::assertTrue($validator->validate(-2.34, $schema)->isValid());

        $validationResult = $validator->validate(2.345, $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertErrorKeyword('precision', $error);
        static::assertFormattedErrorMessage('The number must not have more than 2 decimal places', $error);

        $validationResult = $validator->validate('2.345', $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertErrorKeyword('type', $error);
    }

    public function testInvalidPrecision(): void
    {
        $validator = new SystopiaValidator();

        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
        "type": "number",
        "precision": "invalid"
    }
}
JSON;

        $validator = new SystopiaValidator();
        static::expectException(InvalidKeywordException::class);
        static::expectExceptionMessage('precision must contain an integer');

        $validator->validate([1], $schema);
    }

    public function testPrecisionReferencesMissingValue(): void
    {
        $validator = new SystopiaValidator();

        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "precision": { "type": "number" },
        "value": {
            "type": "number",
            "precision": { "$data": "/precision" }
        }
    }
}
JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['value' => 1.2], $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertCount(1, $error->subErrors());
        $precisionError = $error->subErrors()[0];
        static::assertNotNull($precisionError);
        static::assertErrorKeyword('precision', $precisionError);
        static::assertFormattedErrorMessage('Failed to resolve precision', $precisionError);
    }

    public function testPrecisionReferencesInvalidValue(): void
    {
        $validator = new SystopiaValidator();

        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "precision": {},
        "value": {
            "type": "number",
            "precision": { "$data": "/precision" }
        }
    }
}
JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['value' => 1.2, 'precision' => 'invalid'], $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertCount(1, $error->subErrors());
        $precisionError = $error->subErrors()[0];
        static::assertNotNull($precisionError);
        static::assertErrorKeyword('precision', $precisionError);
        static::assertFormattedErrorMessage('Invalid precision (got value of type string)', $precisionError);
    }
}
