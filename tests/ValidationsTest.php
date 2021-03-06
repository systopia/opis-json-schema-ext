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
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Keywords\ValidationsKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\ValidationsKeywordParser
 */
final class ValidationsTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testSimpleValidation(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": 10
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be greater than or equal to 10', $minimumError);
    }

    public function testSimpleValidationWithCustomMessage(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": 10,
                                "message": "Number must be at least {minimum}"
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be at least 10', $minimumError);
    }

    public function testCalculatedValidation(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": { "$calculate": "2 * 5" }
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be greater than or equal to 10', $minimumError);
    }

    public function testEvaluateValidation(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "evaluate",
                                "value": {
                                    "expression": "2 * a == data",
                                    "variables": { "a": "5" }
                                }
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('evaluate', $minimumError);
        static::assertFormattedErrorMessage('Evaluation of "2 * a == data" failed', $minimumError);
    }

    public function testEvaluateValidationWithCustomMessage(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "evaluate",
                                "value": {
                                    "expression": "2 * a == data",
                                    "variables": { "a": "5" }
                                },
                                "message": "Number is not equal to 2 * {a}"
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('evaluate', $minimumError);
        static::assertFormattedErrorMessage('Number is not equal to 2 * 5', $minimumError);
    }

    public function testValidationWithReferencedVariable(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": { "$data": "/a" }
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['a' => 10, 'foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['a' => 10, 'foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be greater than or equal to 10', $minimumError);
    }

    public function testValidationWithReferencedVariableWithFallback(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": { "$data": "/a", "fallback": 10 }
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['a' => 10, 'foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['a' => 10, 'foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be greater than or equal to 10', $minimumError);
    }

    public function testNoValidationWithReferencedVariableNotSet(): void
    {
        $validator = new SystopiaValidator();
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": { "$data": "/a" }
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertTrue($validationResult->isValid());
    }

    public function testMultipleValidations(): void
    {
        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);

        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum",
                                "value": 10
                            },
                            {
                                "keyword": "exclusiveMinimum",
                                "value": 10
                            }
                        ]
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['foo' => 10], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(1, $error->subErrors());
        $exclusiveMinimumError = $error->subErrors()[0];
        static::assertErrorKeyword('exclusiveMinimum', $exclusiveMinimumError);
        static::assertFormattedErrorMessage('Number must be greater than 10', $exclusiveMinimumError);

        $validationResult = $validator->validate((object) ['foo' => 9], $schema);
        static::assertNotNull($validationResult->error());
        static::assertCount(1, $validationResult->error()->subErrors());
        $error = $validationResult->error()->subErrors()[0];
        static::assertErrorKeyword('$validations', $error);
        static::assertFormattedErrorMessage('The property must match validations', $error);
        static::assertCount(2, $error->subErrors());
        $minimumError = $error->subErrors()[0];
        static::assertErrorKeyword('minimum', $minimumError);
        static::assertFormattedErrorMessage('Number must be greater than or equal to 10', $minimumError);
        $exclusiveMinimumError = $error->subErrors()[1];
        static::assertErrorKeyword('exclusiveMinimum', $exclusiveMinimumError);
        static::assertFormattedErrorMessage('Number must be greater than 10', $exclusiveMinimumError);
    }

    public function testValueMissing(): void
    {
        static::expectException(InvalidKeywordException::class);
        static::expectExceptionMessage('$validations entries must contain property "value"');
        $validator = new SystopiaValidator();

        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "integer",
                        "$validations": [
                            {
                                "keyword": "minimum"
                            }
                        ]
                    }
                }
            }
            JSON;

        $validator->validate((object) ['foo' => 10], $schema);
    }
}
