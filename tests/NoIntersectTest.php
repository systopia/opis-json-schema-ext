<?php

/*
 * Copyright 2024 SYSTOPIA GmbH
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
 * @covers \Systopia\JsonSchema\Keywords\NoIntersectKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\NoIntersectKeywordParser
 */
final class NoIntersectTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testNumber(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
       "type": ["object"],
       "properties": {
          "from": { "type": "number" },
          "to": { "type": "number" }
       }
    },
    "noIntersect": { "begin":  "from", "end":  "to" }
}
JSON;

        $validator = new SystopiaValidator();

        self::assertTrue($validator->validate([], $schema)->isValid());
        self::assertTrue($validator->validate([(object) ['from' => 3, 'to' => 3]], $schema)->isValid());

        $data = [
            (object) ['from' => 3, 'to' => 5],
            (object) ['from' => 2, 'to' => 2],
            (object) ['from' => 6, 'to' => 9],
        ];
        self::assertTrue($validator->validate($data, $schema)->isValid());

        $data = [
            (object) ['from' => 3, 'to' => 5],
            (object) ['from' => 1, 'to' => 3],
            (object) ['from' => 6, 'to' => 9],
        ];
        $result = $validator->validate($data, $schema);
        $error = $result->error();
        self::assertNotNull($error);
        self::assertErrorKeyword('noIntersect', $error);
        self::assertFormattedErrorMessage('The intervals must not intersect.', $error);
    }

    public function testString(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
       "type": ["object"],
       "properties": {
          "from": { "type": "string" },
          "to": { "type": "string" }
       }
    },
    "noIntersect": { "begin":  "from", "end":  "to" }
}
JSON;

        $validator = new SystopiaValidator();

        self::assertTrue($validator->validate([], $schema)->isValid());
        self::assertTrue($validator->validate([(object) ['from' => 'a', 'to' => 'b']], $schema)->isValid());

        $data = [
            (object) ['from' => 'x', 'to' => 'y'],
            (object) ['from' => 'c', 'to' => 'd'],
            (object) ['from' => 'e', 'to' => 'k'],
        ];
        self::assertTrue($validator->validate($data, $schema)->isValid());

        $data = [
            (object) ['from' => 'k', 'to' => 'y'],
            (object) ['from' => 'c', 'to' => 'c'],
            (object) ['from' => 'd', 'to' => 'k'],
        ];
        $result = $validator->validate($data, $schema);
        $error = $result->error();
        self::assertNotNull($error);
        self::assertErrorKeyword('noIntersect', $error);
        self::assertFormattedErrorMessage('The intervals must not intersect.', $error);
    }

    public function testInvalidKeywordNoObject(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
       "type": ["object"],
       "properties": {
          "from": { "type": "number" },
          "to": { "type": "number" }
       }
     },
    "noIntersect": true
}
JSON;

        $validator = new SystopiaValidator();
        self::expectException(InvalidKeywordException::class);
        self::expectExceptionMessage('noIntersect must contain an object with "begin" and "end"');
        $validator->validate((object) ['array' => []], $schema);
    }

    public function testInvalidKeywordBeginMissing(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
       "type": ["object"],
       "properties": {
          "from": { "type": "number" },
          "to": { "type": "number" }
       }
     },
    "noIntersect": { "end":  "from" }
}
JSON;

        $validator = new SystopiaValidator();
        self::expectException(InvalidKeywordException::class);
        self::expectExceptionMessage('noIntersect entries must contain property "begin"');
        $validator->validate((object) ['array' => []], $schema);
    }

    public function testInvalidKeywordEndMissing(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "items": {
       "type": ["object"],
       "properties": {
          "from": { "type": "number" },
          "to": { "type": "number" }
       }
     },
    "noIntersect": { "begin":  "from" }
}
JSON;

        $validator = new SystopiaValidator();
        self::expectException(InvalidKeywordException::class);
        self::expectExceptionMessage('noIntersect entries must contain property "end"');
        $validator->validate((object) ['array' => []], $schema);
    }
}
