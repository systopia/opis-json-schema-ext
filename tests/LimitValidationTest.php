<?php

/*
 * Copyright 2025 SYSTOPIA GmbH
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
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\KeywordValidators\ApplyLimitValidationKeywordValidator
 * @covers \Systopia\JsonSchema\KeywordValidators\LimitValidationKeywordValidator
 * @covers \Systopia\JsonSchema\LimitValidation\LimitValidationRule
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\LimitValidationKeywordParser
 */
final class LimitValidationTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function test(): void
    {
        $schema = <<<'JSON'
            {
                "$limitValidation": true,
                "type": "object",
                "properties": {
                    "val1": { "type": "integer" },
                    "val2": { "type": "string", "minLength": 10 }
                },
                "required": ["foo"]
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $ignoredErrorCollector = new ErrorCollector();
        $globals = [
            'errorCollector' => $errorCollector,
            'ignoredErrorCollector' => $ignoredErrorCollector,
        ];
        $validator = new SystopiaValidator([], 10);

        self::assertTrue($validator->validate((object) ['val1' => null, 'val2' => ''], $schema, $globals)->isValid());
        $ignoredErrorCollector->hasErrorAt('/val1');
        $ignoredErrorCollector->hasErrorAt('/val2');
        self::assertTrue($validator->validate((object) ['val1' => null, 'val2' => null], $schema, $globals)->isValid());
        self::assertTrue($validator->validate(null, $schema, $globals)->isValid());
        self::assertFalse($errorCollector->hasErrors());

        // Empty string is not ignored for "type" keyword.
        $result = $validator->validate((object) ['val1' => ''], $schema, $globals);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
        self::assertTrue($errorCollector->hasErrorAt('/val1'));

        // false is not ignored for "type" keyword.
        $result = $validator->validate((object) ['val1' => false, 'val2' => true], $schema, $globals);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(2, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
        self::assertErrorKeyword('type', $result->error()->subErrors()[1]);
        self::assertTrue($errorCollector->hasErrorAt('/val1'));
        self::assertTrue($errorCollector->hasErrorAt('/val2'));

        // False is not ignored for "type" keyword.
        $result = $validator->validate((object) ['val2' => false], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
    }

    /**
     * When there are multiple errors on the same schema depth it results in an
     * error with keyword "" having the individual errors as sub errors. This
     * test verifies that those errors are handled correctly.
     */
    public function testMultiError(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": true,
    "type": "integer",
    "enum": ["test"]
}
JSON;

        $validator = new SystopiaValidator([], 10);

        self::assertTrue($validator->validate(null, $schema)->isValid());

        $result = $validator->validate(123, $schema);
        self::assertNotNull($result->error());
        self::assertErrorKeyword('enum', $result->error());

        $result = $validator->validate('bar', $schema);
        self::assertNotNull($result->error());
        self::assertErrorKeyword('', $result->error());
        self::assertSubErrorsCount(2, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
        self::assertErrorKeyword('enum', $result->error()->subErrors()[1]);
    }

    public function testOverride(): void
    {
        $schema = <<<'JSON'
            {
                "$limitValidation": true,
                "type": "object",
                "properties": {
                    "val": { "$limitValidation": false, "type": "integer" }
                },
                "required": ["foo"]
            }
            JSON;

        $validator = new SystopiaValidator([], 10);

        $result = $validator->validate((object) ['val' => null], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
    }

    public function testRuleKeyword(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": true,
        "rules": [
            {
              "keyword": { "const":  "type" },
              "validate": true
            }
        ]
    },
    "type": "object",
    "properties": {
        "val": { "type": "string", "minLength": 1 }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);

        // "type" should be validated.
        $result = $validator->validate((object) ['val' => null], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);

        // "minLength" should not be validated.
        self::assertTrue($validator->validate((object) ['val' => ''], $schema)->isValid());
    }

    public function testRuleKeywordValue(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": true,
        "rules": [
            {
                "keywordValue": { "const":  "string" },
                "validate": true
            }
        ]
    },
    "type": "object",
    "properties": {
        "val": { "type": "string", "minLength": 1 }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);

        // "type" should be validated because it's value matches "keywordValue".
        $result = $validator->validate((object) ['val' => null], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);

        // "minLength" should not be validated.
        self::assertTrue($validator->validate((object) ['val' => ''], $schema)->isValid());
    }

    public function testRuleValue(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": true,
        "rules": [
            {
                "value": { "const":  "test" },
                "validate": false
            }
        ]
    },
    "type": "object",
    "properties": {
        "val": { "type": "boolean" }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);

        // "type" should be validated because the value to validate doesn't match "value".
        $result = $validator->validate((object) ['val' => 123], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);

        // "type" should not be validated because the value to validate matches "value".
        self::assertTrue($validator->validate((object) ['val' => 'test'], $schema)->isValid());
    }

    public function testCondition(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": {
            "properties": {
                "val1": { "const":  true }
             }
        }
    },
    "type": "object",
    "properties": {
          "val1": { "type": "boolean" },
          "val2": { "type": "integer" }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);
        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        // "type" should be validated because "condition" isn't matched.
        $result = $validator->validate((object) ['val1' => false, 'val2' => null], $schema, $globals);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
        // Test that errors on evaluation of condition schema are not added to error collector.
        self::assertFalse($errorCollector->hasErrorAt('/val1'));

        // "type" should not be validated because "condition" is matched.
        self::assertTrue($validator->validate((object) ['val1' => true, 'val2' => null], $schema)->isValid());
    }

    public function testParentCondition(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": {
            "properties": {
                "val1": { "const":  true }
             }
        }
    },
    "type": "object",
    "properties": {
          "val1": { "type": "boolean" },
          "val2": {
              "$limitValidation": {
                  "schema": { "const": "valid" }
              },
              "type": "string"
          }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);

        // "type" should be validated because "condition" isn't matched.
        $result = $validator->validate((object) ['val1' => false, 'val2' => null], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);

        // "schema" should be validated because "condition" is matched.
        self::assertTrue($validator->validate((object) ['val1' => true, 'val2' => 'valid'], $schema)->isValid());

        $result = $validator->validate((object) ['val1' => true, 'val2' => 'invalid'], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('const', $result->error()->subErrors()[0]);

        $result = $validator->validate((object) ['val1' => true, 'val2' => 123], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        $subError = $result->error()->subErrors()[0];
        self::assertErrorKeyword('', $subError);
        self::assertSubErrorsCount(2, $subError);
        self::assertErrorKeyword('type', $subError->subErrors()[0]);
        self::assertErrorKeyword('const', $subError->subErrors()[1]);
    }

    public function testSchema(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "condition": true,
        "schema": {
            "required": ["test"]
        }
    },
    "type": "object",
    "properties": {
          "val": { "type": "boolean" }
    }
}
JSON;

        $validator = new SystopiaValidator([], 10);

        // "schema" is applied additionally.
        $result = $validator->validate((object) ['val' => false], $schema);
        self::assertNotNull($result->error());
        self::assertErrorKeyword('required', $result->error());

        self::assertTrue($validator->validate((object) ['val' => true, 'test' => 'foo'], $schema)->isValid());

        $result = $validator->validate((object) ['val' => 123, 'test' => 'foo'], $schema);
        self::assertNotNull($result->error());
        self::assertSubErrorsCount(1, $result->error());
        self::assertErrorKeyword('type', $result->error()->subErrors()[0]);
    }

    public function testCalculatedValueWithViolatedData(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": true,
    "type": "object",
    "properties": {
        "foo": {
            "type": "integer",
            "minimum": 10
        },
        "calculated": {
            "$calculate": {
                "expression": "foo + 1",
                "variables": {
                    "foo": { "$data": "/foo" }
                }
            },
            "minimum": 10
        }
    }
}
JSON;

        $schema = json_decode($schema);
        $validator = new SystopiaValidator([], 10);

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];
        self::assertFalse($validator->validate((object) ['foo' => 1], $schema, $globals)->isValid());
        self::assertFalse($errorCollector->hasErrorAt('/calculated'));

        $schema = clone $schema;
        $schema->{'$limitValidation'} = (object) [
            'condition' => true,
            'rules' => [
                (object) [
                    'calculatedValueUsedViolatedData' => true,
                    'validate' => true,
                ],
            ],
        ];

        $validator->validate((object) ['foo' => 1], $schema, $globals)->isValid();
        self::assertTrue($errorCollector->hasErrorAt('/calculated'));
    }

    public function testInvalidKeyword(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": 123
}
JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator([], 10);
        $validator->validate($data, $schema);
    }

    public function testInvalidRules(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "rules": "test"
    }
}
JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator([], 10);
        $validator->validate($data, $schema);
    }

    public function testInvalidRule1(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "rules": [
            "test"
        ]
    }
}
JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator([], 10);
        $validator->validate($data, $schema);
    }

    public function testInvalidRule2(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "rules": [
            {
              "validate": 123
            }
        ]
    }
}
JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator([], 10);
        $validator->validate($data, $schema);
    }

    public function testInvalidRule3(): void
    {
        $schema = <<<'JSON'
{
    "$limitValidation": {
        "rules": [
            {
              "value": 123
            }
        ]
    }
}
JSON;

        $data = new \stdClass();

        self::expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator([], 10);
        $validator->validate($data, $schema);
    }
}
