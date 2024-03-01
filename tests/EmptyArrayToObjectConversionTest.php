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

declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\KeywordValidators\TypeKeywordValidator
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\TypeKeywordValidatorParser
 */
final class EmptyArrayToObjectConversionTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function test(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "object": { "type":  "object" }
                }
            }
            JSON;

        $validator = new SystopiaValidator(['convertEmptyArrays' => true]);
        $data = (object) ['object' => []];
        $validationResult = $validator->validate($data, $schema);

        self::assertTrue($validationResult->isValid());
        self::assertEquals(new \stdClass(), $data->object);
    }

    public function testIsDisabledByDefault(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "object": { "type":  "object" }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        self::assertFalse($validator->parser()->option('convertEmptyArrays'));

        $data = (object) ['object' => []];
        $validationResult = $validator->validate($data, $schema);

        self::assertFalse($validationResult->isValid());
        self::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('type', $error);
    }

    public function testDoesNotConvertNonEmptyArray(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "object": { "type":  "object" }
                }
            }
            JSON;

        $validator = new SystopiaValidator(['convertEmptyArrays' => true]);
        $data = (object) ['object' => [1, 2, 3]];
        $validationResult = $validator->validate($data, $schema);

        self::assertFalse($validationResult->isValid());
        self::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('type', $error);
    }
}
