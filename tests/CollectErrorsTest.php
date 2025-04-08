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

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Errors\ErrorCollector
 * @covers \Systopia\JsonSchema\KeywordValidators\CollectErrorsKeywordValidator
 * @covers \Systopia\JsonSchema\KeywordValidators\RootCollectErrorsKeywordValidator
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser
 */
final class CollectErrorsTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function test(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "parent": {
                        "type": "object",
                        "properties": {
                            "child1": { "type": "integer" },
                            "child2": { "type": "string", "minLength": 10 }
                        }
                    },
                    "string": { "type": "string" }
                }
            }
            JSON;

        $data = (object) [
            'parent' => (object) [
                'child1' => 1,
                'child2' => 'string',
            ],
            'string' => 2,
        ];

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];
        self::assertFalse($errorCollector->hasErrors());

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validator->validate($data, $schema, $globals);

        self::assertTrue($errorCollector->hasErrors());
        self::assertCount(4, $errorCollector->getErrors());
        self::assertTrue($errorCollector->hasErrorAt([]));
        self::assertTrue($errorCollector->hasErrorAt(['parent']));
        self::assertTrue($errorCollector->hasErrorAt(['parent', 'child2']));
        self::assertTrue($errorCollector->hasErrorAt('/parent/child2'));
        self::assertTrue($errorCollector->hasErrorAt(['string']));
        self::assertFalse($errorCollector->hasErrorAt(['parent', 'child1']));

        $expectedErrorKeys = [
            '/parent/child2',
            '/parent',
            '/string',
            '/',
        ];
        self::assertSame($expectedErrorKeys, array_keys($errorCollector->getErrors()));

        $stringErrors = $errorCollector->getErrorsAt(['string']);
        self::assertCount(1, $stringErrors);
        self::assertErrorKeyword('type', $stringErrors[0]);

        self::assertCount(2, $errorCollector->getLeafErrors());
        self::assertTrue($errorCollector->hasLeafErrorAt('/parent/child2'));
        self::assertFalse($errorCollector->hasLeafErrorAt('/parent'));
        self::assertTrue($errorCollector->hasLeafErrorAt(['string']));
        self::assertSame(['/parent/child2', '/string'], array_keys($errorCollector->getLeafErrors()));

        $child2Errors = $errorCollector->getLeafErrorsAt(['parent', 'child2']);
        self::assertCount(1, $child2Errors);
        self::assertErrorKeyword('minLength', $child2Errors[0]);
    }

    public function testMultipleViolations(): void
    {
        $schema = <<<'JSON'
            {
                "type": "string",
                "$validations": [
                    { "keyword": "minLength", "value": 10 },
                    { "keyword": "pattern", "value": "test" }
                ]
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validator->validate('foo', $schema, $globals);

        self::assertCount(1, $errorCollector->getErrors());
        $stringErrors = $errorCollector->getErrorsAt('/');
        self::assertCount(3, $stringErrors);
        self::assertErrorKeyword('minLength', $stringErrors[0]);
        self::assertErrorKeyword('pattern', $stringErrors[1]);
        self::assertErrorKeyword('$validations', $stringErrors[2]);

        self::assertCount(1, $errorCollector->getLeafErrors());
        $leafErrors = $errorCollector->getLeafErrorsAt('/');
        self::assertCount(2, $leafErrors);
        self::assertErrorKeyword('minLength', $leafErrors[0]);
        self::assertErrorKeyword('pattern', $leafErrors[1]);
    }

    public function testMultipleErrorSameDepth(): void
    {
        $data = (object) [
            'a' => 1,
        ];

        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'a' => (object) ['type' => 'string'],
                'b' => (object) ['type' => 'string'],
            ],
            'required' => ['a', 'b'],
        ];

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();
        $validator->validate($data, $schema, $globals);

        $leafErrors = $errorCollector->getLeafErrors();
        self::assertCount(2, $leafErrors);
    }

    public function testOneOfIgnoreSubValidation(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "test": {
                        "oneOf": [{"const": 1}, {"type": "string"}]
                    }
                }
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();

        $validator->validate((object) ['test' => 1], $schema, $globals);
        self::assertFalse($errorCollector->hasErrors());

        $validator->validate((object) ['test' => 'foo'], $schema, $globals);
        self::assertFalse($errorCollector->hasErrors());
    }

    public function testOneOfErrors(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "test": {
                        "oneOf": [{"const": 1}, {"type": "string"}]
                    }
                }
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();

        $validator->validate((object) ['test' => 3], $schema, $globals);
        self::assertTrue($errorCollector->hasErrors());
        $leafErrors = $errorCollector->getLeafErrorsAt(['test']);
        self::assertCount(1, $leafErrors);
        self::assertErrorKeyword('oneOf', $leafErrors[0]);
        self::assertSubErrorsCount(2, $leafErrors[0]);

        $errors = $errorCollector->getErrorsAt(['test']);
        self::assertCount(1, $errors);
        self::assertSame($leafErrors, $errors);
    }

    public function testAnyOfErrors(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "test": {
                        "anyOf": [{"const": 1}, {"type": "string"}]
                    }
                }
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();

        $validator->validate((object) ['test' => 3], $schema, $globals);
        self::assertTrue($errorCollector->hasErrors());
        $leafErrors = $errorCollector->getLeafErrorsAt(['test']);
        self::assertCount(1, $leafErrors);
        self::assertErrorKeyword('anyOf', $leafErrors[0]);
        self::assertSubErrorsCount(2, $leafErrors[0]);

        $errors = $errorCollector->getErrorsAt(['test']);
        self::assertCount(1, $errors);
        self::assertSame($leafErrors, $errors);
    }

    public function testIf(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "boolean": { "type": "boolean" }
                },
                "if": {
                    "properties":  {
                        "boolean": { "const": true }
                    }
                },
                "then": {
                    "properties": {
                        "string": { "type": "string" }
                    }
                }
            }
            JSON;

        $data = (object) [
            'boolean' => false,
            'string' => 2,
        ];

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validator->validate($data, $schema, $globals);

        // There should be no errors for the keywords below "if".
        self::assertFalse($errorCollector->hasErrors());

        $data = (object) [
            'boolean' => true,
            'string' => 2,
        ];
        $validator->validate($data, $schema, $globals);

        self::assertCount(2, $errorCollector->getErrors());
        self::assertTrue($errorCollector->hasErrorAt([]));
        self::assertTrue($errorCollector->hasErrorAt(['string']));
        self::assertTrue($errorCollector->hasErrorAt('/string'));

        $expectedErrorKeys = [
            '/string',
            '/',
        ];
        self::assertSame($expectedErrorKeys, array_keys($errorCollector->getErrors()));

        $stringErrors = $errorCollector->getErrorsAt(['string']);
        self::assertCount(1, $stringErrors);
        self::assertErrorKeyword('type', $stringErrors[0]);

        self::assertCount(1, $errorCollector->getLeafErrors());
        self::assertTrue($errorCollector->hasLeafErrorAt(['string']));
        self::assertSame(['/string'], array_keys($errorCollector->getLeafErrors()));
    }

    /**
     * An error for "additionalProperties" should not be added if there are
     * other violations because of a possible false positive.
     * See https://github.com/opis/json-schema/issues/148.
     */
    public function testAdditionalProperties(): void
    {
        $schema = <<<'JSON'

            {
                "type": "object",
                "properties": {
                    "boolean": { "type": "boolean" }
                },
                "additionalProperties": false
            }
            JSON;

        $validator = new SystopiaValidator([], 10);

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];
        $validator->validate((object) ['boolean' => 123, 'foo' => 'bar'], $schema, $globals);
        self::assertCount(1, $errorCollector->getLeafErrors());
        self::assertTrue($errorCollector->hasLeafErrorAt('/boolean'));
        self::assertErrorKeyword('type', $errorCollector->getLeafErrorsAt('/boolean')[0]);

        // Test that "additionalProperties" error is added if there's no other violation.
        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];
        $validator->validate((object) ['boolean' => true, 'foo' => 'bar'], $schema, $globals);
        self::assertCount(1, $errorCollector->getLeafErrors());
        self::assertTrue($errorCollector->hasLeafErrorAt('/'));
        self::assertErrorKeyword('additionalProperties', $errorCollector->getLeafErrorsAt('/')[0]);
    }
}
