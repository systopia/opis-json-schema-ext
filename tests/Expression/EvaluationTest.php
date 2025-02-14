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

namespace Systopia\JsonSchema\Test\Expression;

use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Expression\Evaluation;

/**
 * @covers \Systopia\JsonSchema\Expression\Evaluation
 */
final class EvaluationTest extends TestCase
{
    private SchemaParser $schemaParser;

    private ValidationContext $validationContext;

    protected function setUp(): void
    {
        parent::setUp();
        $schemaLoader = new SchemaLoader();
        $this->schemaParser = new SchemaParser();
        $this->validationContext = new ValidationContext(new \stdClass(), $schemaLoader);
    }

    public function testParseString(): void
    {
        $evaluation = Evaluation::parse('2 * 5 == data', $this->schemaParser);

        self::assertSame('2 * 5 == data', $evaluation->getExpression());
        self::assertSame([], $evaluation->getVariableNames());
        self::assertSame([], $evaluation->getVariables($this->validationContext));
    }

    public function testParseSimple(): void
    {
        $data = (object) [
            'expression' => '2 * 5',
        ];
        $evaluation = Evaluation::parse($data, $this->schemaParser);

        self::assertSame('2 * 5', $evaluation->getExpression());
        self::assertSame([], $evaluation->getVariableNames());
        self::assertSame([], $evaluation->getVariables($this->validationContext));
    }

    public function testParse(): void
    {
        $data = (object) [
            'expression' => 'a * b == data',
            'variables' => (object) [
                'a' => 3,
                'b' => (object) ['$data' => '/b', 'fallback' => 2],
            ],
        ];
        $evaluation = Evaluation::parse($data, $this->schemaParser);

        self::assertSame('a * b == data', $evaluation->getExpression());
        self::assertSame(['a', 'b'], $evaluation->getVariableNames());
        self::assertSame(['a' => 3, 'b' => 2], $evaluation->getVariables($this->validationContext));
    }

    public function testParseNoExpression(): void
    {
        $data = (object) [
            'expressionX' => '2 * 5 == data',
        ];

        $this->expectException(SchemaException::class);
        Evaluation::parse($data, $this->schemaParser);
    }
}
