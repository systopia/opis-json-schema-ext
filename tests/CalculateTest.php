<?php

/*
 * Copyright 2022 SYSTOPIA GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(6, $data->calculated);
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(8, $data->calculated);
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(10, $data->calculated);
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
        self::assertTrue($validationResult->isValid());
        self::assertFalse(property_exists($data, 'calculated'));
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
        self::assertNotNull($validationResult->error());
        self::assertSame('$calculate', $validationResult->error()->subErrors()[0]->keyword());
        self::assertFalse(property_exists($data, 'calculated'));
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(12, $data->calculated);
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(-1, $data->calculated);
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
        self::assertTrue($validationResult->isValid());
        self::assertSame(2, $data->calculated);
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
