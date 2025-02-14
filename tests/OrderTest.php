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
 * @covers \Systopia\JsonSchema\Keywords\OrderObjectsKeyword
 * @covers \Systopia\JsonSchema\Keywords\OrderSimpleKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\OrderKeywordParser
 */
final class OrderTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testSimpleAsc(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": { "type":  ["string", "number", "boolean", "null"] },
        "$order": "ASC"
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        $data = (object) ['array' => ['b', false, 3, 'c', null, 2, 'a', 1, 'a', true]];
        self::assertTrue($validator->validate($data, $schema)->isValid());
        if (PHP_MAJOR_VERSION < 8) {
            // Prior to PHP 8.0.0, if a string is compared to a number or a
            // numeric string then the string was converted to a number before
            // performing the comparison.
            self::assertSame([false, null, 'a', 'a', 'b', 'c', 1, 2, 3, true], $data->array);
        } else {
            self::assertSame([false, null, 1, 2, 3, 'a', 'a', 'b', 'c', true], $data->array);
        }
    }

    public function testSimpleDesc(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": { "type":  ["string", "number", "boolean", "null"] },
        "$order": "DESC"
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        $data = (object) ['array' => ['b', false, 3, 'c', null, 2, 'a', 1, 'a', true]];
        self::assertTrue($validator->validate($data, $schema)->isValid());
        if (PHP_MAJOR_VERSION < 8) {
            // Prior to PHP 8.0.0, if a string is compared to a number or a
            // numeric string then the string was converted to a number before
            // performing the comparison.
            self::assertSame([3, 2, 1, 'c', 'b', 'a', 'a', true, false, null], $data->array);
        } else {
            self::assertSame(['c', 'b', 'a', 'a', 3, 2, 1, true, false, null], $data->array);
        }
    }

    public function testSimpleInvalid(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": { "type":  ["string"] },
        "$order": "ASC"
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        $data = (object) ['array' => ['b', 'a', 1]];
        self::assertFalse($validator->validate($data, $schema)->isValid());
        // Order is unchanged when validation fails.
        self::assertSame(['b', 'a', 1], $data->array);
    }

    public function testObject(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": { "type":  ["object", "null"] },
        "$order": { "foo":  "ASC", "bar":  "DESC" }
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        $object1 = (object) ['foo' => 3, 'bar' => 1];
        $object2 = (object) ['foo' => 2, 'bar' => 0];
        $object3 = (object) ['foo' => 3, 'bar' => 2];
        $object4 = (object) ['foo' => 3];
        $object5 = (object) ['foo' => 2, 'bar' => 0];
        $data = (object) ['array' => [$object1, $object2, $object3, null, $object4, $object5]];
        self::assertTrue($validator->validate($data, $schema)->isValid());
        self::assertSame([null, $object2, $object5, $object3, $object1, $object4], $data->array);
    }

    public function testObjectInvalid(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": {
          "type":  "object",
          "properties": {
            "foo": { "type":  "string" }
          }
        },
        "$order": { "foo":  "ASC" }
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        $object1 = (object) ['foo' => 'x'];
        $object2 = (object) ['foo' => 'a'];
        $object3 = (object) ['foo' => 3];
        $data = (object) ['array' => [$object1, $object2, $object3]];
        self::assertFalse($validator->validate($data, $schema)->isValid());
        self::assertSame([$object1, $object2, $object3], $data->array);
    }

    public function testInvalidOrder(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": ["array", "number", "boolean", "null"],
        "items": { "type":  "string" },
        "$order": "invalid"
    }
  }
}
JSON;

        $validator = new SystopiaValidator();
        self::expectException(InvalidKeywordException::class);
        self::expectExceptionMessage('$order must contain "ASC", "DESC", or a mapping of field names to "ASC" or "DESC"');
        $validator->validate((object) ['array' => []], $schema);
    }
}
