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

declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Keywords\MaxDateKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\MaxDateKeywordParser
 */
final class MaxDateTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testSimple(): void
    {
        $schema = <<<'JSON'
{
    "type": "string",
    "format": "date",
    "maxDate": "1970-01-02"
}
JSON;

        $validator = new SystopiaValidator();
        static::assertTrue($validator->validate('1970-01-01', $schema)->isValid());
        static::assertTrue($validator->validate('1970-01-02', $schema)->isValid());

        $validationResult = $validator->validate('1970-01-03', $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertErrorKeyword('maxDate', $error);
        static::assertFormattedErrorMessage('Date must not be after 1970-01-02', $error);
    }

    public function testInvalidMaxDate(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
        "type": "string",
        "format": "date",
        "maxDate": "invalid"
    }
}
JSON;

        $validator = new SystopiaValidator();
        static::expectException(InvalidKeywordException::class);
        static::expectExceptionMessage('maxDate must contain a date in the form YYYY-MM-DD');

        $validator->validate([1], $schema);
    }

    public function testOnlyValidatesWithFormateDate(): void
    {
        $schema = <<<'JSON'
{
    "type": "string",
    "maxDate": "1970-01-02"
}
JSON;

        $validator = new SystopiaValidator();
        static::assertTrue($validator->validate('1970-01-01', $schema)->isValid());
    }

    public function testMaxDateReferencesMissingValue(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "maxDate": { "type": "string" },
        "value": {
            "type": "string",
            "format": "date",
            "maxDate": { "$data": "/maxDate" }
        }
    }
}
JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['value' => '1970-01-01'], $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertCount(1, $error->subErrors());
        $maxDateError = $error->subErrors()[0];
        static::assertNotNull($maxDateError);
        static::assertErrorKeyword('maxDate', $maxDateError);
        static::assertFormattedErrorMessage('Failed to resolve maxDate', $maxDateError);
    }

    public function testMaxDateReferencesInvalidValue(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "maxDate": {},
        "value": {
            "type": "string",
            "format": "date",
            "maxDate": { "$data": "/maxDate" }
        }
    }
}
JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['value' => '1970-01-01', 'maxDate' => 'invalid'], $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertCount(1, $error->subErrors());
        $maxDateError = $error->subErrors()[0];
        static::assertNotNull($maxDateError);
        static::assertErrorKeyword('maxDate', $maxDateError);
        static::assertFormattedErrorMessage('Invalid maxDate invalid', $maxDateError);
    }

    public function testMaxDateFallback(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "maxDate": { "type": "string" },
        "value": {
            "type": "string",
            "format": "date",
            "maxDate": { "$data": "/maxDate", "fallback": "1970-01-02" }
        }
    }
}
JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['value' => '1970-01-03'], $schema);
        $error = $validationResult->error();
        static::assertNotNull($error);
        static::assertCount(1, $error->subErrors());
        $maxDateError = $error->subErrors()[0];
        static::assertNotNull($maxDateError);
        static::assertErrorKeyword('maxDate', $maxDateError);
        static::assertFormattedErrorMessage('Date must not be after 1970-01-02', $maxDateError);
    }
}
