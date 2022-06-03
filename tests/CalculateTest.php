<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\DefaultVocabulary;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Parsers\KeywordValidators\CalculateKeywordValidationParser;
use Systopia\JsonSchema\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\KeywordValidators\CalculateInitKeywordValidator
 * @covers \Systopia\JsonSchema\KeywordValidators\CalculateKeywordValidator
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\CalculateKeywordValidationParser
 */
final class CalculateTest extends TestCase
{
    public function testSimpleCalculation(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "calculated": {
                        "type": "integer",
                        "$calculate": "2 * 3"
                    }
                },
                "required": ["calculated"]
            }
            JSON;

        $data = new \stdClass();

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(6, $data->calculated);
    }

    public function testCalculationWithVariable(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": 4 }
                        }
                    }
                },
                "required": ["calculated"]
            }
            JSON;

        $data = new \stdClass();

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(8, $data->calculated);
    }

    public function testCalculationWithReferencedVariable(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "multiplicand": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/multiplicand" } }
                        }
                    }
                },
                "required": ["multiplicand"]
            }
            JSON;

        $data = (object) ['multiplicand' => 5];

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(10, $data->calculated);
    }

    public function testCalculationFail(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "missing": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/missing" } }
                        }
                    }
                }
            }
            JSON;

        $data = (object) ['calculated' => 2];

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertObjectNotHasAttribute('calculated', $data);
    }

    public function testCalculationFailRequired(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "missing": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/missing" } }
                        }
                    }
                },
                "required": ["calculated"]
            }
            JSON;

        $data = (object) ['calculated' => 2];

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertNotNull($validationResult->error());
        static::assertSame('$calculate', $validationResult->error()->subErrors()[0]->keyword());
        static::assertObjectNotHasAttribute('calculated', $data);
    }

    public function testCalculationWVariableFallback(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "multiplicand": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/multiplicand", "fallback": 6 } }
                        }
                    }
                },
                "required": ["calculated"]
            }
            JSON;

        $data = new \stdClass();

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(12, $data->calculated);
    }

    public function testCalculationFallback(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "missing": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/missing" } },
                            "fallback": -1
                        }
                    }
                }
            }
            JSON;

        $data = (object) ['calculated' => 2];

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(-1, $data->calculated);
    }

    public function testNoCalculationIfReferencedDataHasViolation(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "multiplicand": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": {
                            "expression": "2 * a",
                            "variables": { "a": { "$data": "/multiplicand" } }
                        }
                    }
                }
            }
            JSON;

        $data = (object) ['multiplicand' => '2', 'calculated' => -3];

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validationResult = $validator->validate($data, $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        static::assertSame('type', $validationResult->error()->subErrors()[0]->keyword());
        static::assertSame(
            '/multiplicand',
            JsonPointer::pathToString($validationResult->error()->subErrors()[0]->data()->fullPath())
        );
        static::assertObjectNotHasAttribute('calculated', $data);
    }

    public function testKeywordIgnoredWithoutCalculator(): void
    {
        $vocabulary = new DefaultVocabulary(
            [],
            [new CollectErrorsKeywordValidatorParser(), new CalculateKeywordValidationParser()]
        );
        $parser = new SchemaParser([], [], $vocabulary);
        $loader = new SchemaLoader($parser);
        $validator = new Validator($loader);

        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "calculated": {
                        "type": "integer",
                        "$calculate": "2 * 3"
                    }
                },
                "required": ["calculated"]
            }
            JSON;

        $data = (object) ['calculated' => 2];

        $validationResult = $validator->validate($data, $schema);
        static::assertTrue($validationResult->isValid());
        static::assertSame(2, $data->calculated);
    }

    public function testInvalidExpression(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "multiplicand": { "type": "integer" },
                    "calculated": {
                        "type": "integer",
                        "$calculate": "2 * invalid"
                    }
                }
            }
            JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator();
        $validator->validate($data, $schema);
    }
}
