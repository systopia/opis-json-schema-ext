<?php

/*
 * Copyright 2023 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Test\Errors;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorUtil;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Errors\ErrorUtil
 */
final class ErrorUtilTest extends TestCase
{
    public function testGetKeywordValue(): void
    {
        $schema = <<<'JSON'
{
    "type": "array",
    "contains": {"const": 1},
    "items": {
        "type": "number"
    }
}
JSON;
        $validator = new SystopiaValidator();
        $validationResult = $validator->validate([2], $schema);
        $error = $validationResult->error();
        self::assertNotNull($error);

        $expectedKeywordValue = new \stdClass();
        $expectedKeywordValue->const = 1;
        self::assertEquals($expectedKeywordValue, ErrorUtil::getKeywordValue($error));
    }

    public function testGetKeywordValueSchemaFalse(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('test', $schema, $dataInfo, 'message');

        self::assertNull(ErrorUtil::getKeywordValue($error));
    }
}
