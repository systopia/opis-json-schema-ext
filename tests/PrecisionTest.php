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
 * @covers \Systopia\JsonSchema\Keywords\PrecisionKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\PrecisionKeywordParser
 */
final class PrecisionTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testSimple(): void
    {
        $schema = <<<'JSON'
{
    "type": "number",
    "precision": 2
}
JSON;

        $validator = new SystopiaValidator();
        self::assertTrue($validator->validate(2, $schema)->isValid());
        self::assertTrue($validator->validate(2.3, $schema)->isValid());
        self::assertTrue($validator->validate(2.34, $schema)->isValid());
        self::assertTrue($validator->validate(-2.34, $schema)->isValid());
        self::assertTrue($validator->validate(2.0, $schema)->isValid());
        self::assertTrue($validator->validate(-2.0, $schema)->isValid());

        $validationResult = $validator->validate(2.345, $schema);
        $error = $validationResult->error();
        self::assertNotNull($error);
        self::assertErrorKeyword('precision', $error);
        self::assertFormattedErrorMessage('The number must not have more than 2 decimal places', $error);

        $validationResult = $validator->validate('2.345', $schema);
        $error = $validationResult->error();
        self::assertNotNull($error);
        self::assertErrorKeyword('type', $error);
    }

    public function testInvalidPrecision(): void
    {
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
        self::expectException(InvalidKeywordException::class);
        self::expectExceptionMessage('precision must contain an integer');

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
        self::assertNotNull($error);
        self::assertCount(1, $error->subErrors());
        $precisionError = $error->subErrors()[0];
        self::assertNotNull($precisionError);
        self::assertErrorKeyword('precision', $precisionError);
        self::assertFormattedErrorMessage('Failed to resolve precision', $precisionError);
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
        self::assertNotNull($error);
        self::assertCount(1, $error->subErrors());
        $precisionError = $error->subErrors()[0];
        self::assertNotNull($precisionError);
        self::assertErrorKeyword('precision', $precisionError);
        self::assertFormattedErrorMessage('Invalid precision (got value of type string)', $precisionError);
    }
}
