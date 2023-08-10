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
use Systopia\JsonSchema\Expression\Calculation;

/**
 * @covers \Systopia\JsonSchema\Expression\Calculation
 */
final class CalculationTest extends TestCase
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
        $calculation = Calculation::parse('2 * 5', $this->schemaParser);

        self::assertSame('2 * 5', $calculation->getExpression());
        self::assertNull($calculation->getFallback());
        self::assertSame([], $calculation->getVariableNames());
        self::assertSame([], $calculation->getVariables($this->validationContext));
    }

    public function testParseSimple(): void
    {
        $data = (object) [
            'expression' => '2 * 5',
        ];
        $calculation = Calculation::parse($data, $this->schemaParser);

        self::assertSame('2 * 5', $calculation->getExpression());
        self::assertNull($calculation->getFallback());
        self::assertSame([], $calculation->getVariableNames());
        self::assertSame([], $calculation->getVariables($this->validationContext));
    }

    public function testParse(): void
    {
        $data = (object) [
            'expression' => 'a * b',
            'fallback' => 4,
            'variables' => (object) [
                'a' => 3,
                'b' => (object) ['$data' => '/b', 'fallback' => 2],
            ],
        ];
        $calculation = Calculation::parse($data, $this->schemaParser);

        self::assertSame('a * b', $calculation->getExpression());
        self::assertNotNull($calculation->getFallback());
        self::assertSame(4, $calculation->getFallback()->getValue($this->validationContext));
        self::assertSame(['a', 'b'], $calculation->getVariableNames());
        self::assertSame(['a' => 3, 'b' => 2], $calculation->getVariables($this->validationContext));
    }

    public function testParseWithoutVariables(): void
    {
        $data = (object) [
            'expression' => '2 + 3',
            'fallback' => 4,
            'variables' => [],
        ];
        $calculation = Calculation::parse($data, $this->schemaParser);

        self::assertSame('2 + 3', $calculation->getExpression());
        self::assertNotNull($calculation->getFallback());
        self::assertSame(4, $calculation->getFallback()->getValue($this->validationContext));
        self::assertSame([], $calculation->getVariableNames());
        self::assertSame([], $calculation->getVariables($this->validationContext));
    }

    public function testParseFallbackDataPointer(): void
    {
        $data = (object) [
            'expression' => '2 + 3',
            'fallback' => (object) ['$data' => '/fallback', 'fallback' => 4],
            'variables' => [],
        ];
        $calculation = Calculation::parse($data, $this->schemaParser);

        self::assertSame('2 + 3', $calculation->getExpression());
        self::assertNotNull($calculation->getFallback());
        self::assertSame(4, $calculation->getFallback()->getValue($this->validationContext));
        self::assertSame([], $calculation->getVariableNames());
        self::assertSame([], $calculation->getVariables($this->validationContext));
    }

    public function testParseNoExpression(): void
    {
        $data = (object) [
            'expressionX' => '2 * 5',
        ];

        $this->expectException(SchemaException::class);
        Calculation::parse($data, $this->schemaParser);
    }

    public function testParseFallbackNull(): void
    {
        $data = (object) [
            'expression' => '2 * 5',
            'fallback' => null,
        ];

        $this->expectException(SchemaException::class);
        Calculation::parse($data, $this->schemaParser);
    }
}
