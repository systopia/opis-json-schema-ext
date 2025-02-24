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

namespace Systopia\JsonSchema\Test\Expression\Variables;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Schemas\EmptySchema;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\Exceptions\CalculationFailedException;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Variables\CalculationVariable;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Parsers\SystopiaSchemaParser;

/**
 * @covers \Systopia\JsonSchema\Expression\Variables\CalculationVariable
 */
final class CalculationVariableTest extends TestCase
{
    private SchemaParser $schemaParser;

    private SchemaLoader $schemaLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaParser = new SystopiaSchemaParser();
        $this->schemaLoader = new SchemaLoader($this->schemaParser);
    }

    public function testIsAllowed(): void
    {
        self::assertFalse(CalculationVariable::isAllowed(new SchemaParser()));
        self::assertTrue(CalculationVariable::isAllowed($this->schemaParser));
    }

    public function testParse(): void
    {
        $variable = CalculationVariable::parse((object) ['$calculate' => '2 * 5'], $this->schemaParser);
        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        self::assertSame(10, $variable->getValue($context));
    }

    public function testFallback1(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'fallback' => 3,
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        self::assertSame(3, $variable->getValue($context));
    }

    public function testFallback2(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
            'fallback' => 5,
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        self::assertSame(5, $variable->getValue($context));
    }

    public function testFallbackCalculate(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
            'fallback' => (object) ['$calculate' => '1 + 2'],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);
        $context = new ValidationContext((object) [], $this->schemaLoader);
        self::assertSame(3, $variable->getValue($context));
    }

    public function testFailOnUnresolved(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);

        $this->expectException(VariableResolveException::class);
        $variable->getValue($context);
    }

    public function testFailOnViolation(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
                'fallback' => 123,
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);

        $context = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
        $context->pushDataPath('a');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $this->expectException(ReferencedDataHasViolationException::class);
        $this->expectExceptionMessage('The calculation at path "/" failed because of violations in the referenced data');
        $variable->getValue($context, Variable::FLAG_FAIL_ON_VIOLATION);
    }

    public function testFailOnViolationWithoutFallback(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);

        $context = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
        $context->pushDataPath('a');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $this->expectException(ReferencedDataHasViolationException::class);
        $this->expectExceptionMessage('The calculation at path "/" failed because of violations in the referenced data');
        $variable->getValue($context);
    }

    public function testFallbackOnViolation(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'fallback' => 3,
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);

        $context = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
        $context->pushDataPath('a');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $violated = false;
        self::assertSame(3, $variable->getValue($context, 0, $violated));
        self::assertTrue($violated);
    }

    public function testExceptionWithoutViolation(): void
    {
        $data = (object) [
            '$calculate' => (object) [
                'expression' => '2 * a',
                'fallback' => 3,
                'variables' => (object) ['a' => (object) ['$data' => '/a']],
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);

        $context = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);

        self::expectException(CalculationFailedException::class);
        self::expectExceptionMessage('');

        try {
            // @phpstan-ignore binaryOp.invalid
            2 * 'b';
            // @phpstan-ignore catch.neverThrown
        } catch (\Throwable $e) {
            self::expectExceptionMessage($e->getMessage());
        }

        $variable->getValue($context);
    }

    public function testParseWithoutCalculator(): void
    {
        $this->expectExceptionObject(new ParseException('Parser option "calculator" is not set'));
        CalculationVariable::parse((object) ['$calculate' => '2 * 5'], new SchemaParser());
    }

    public function testParseExpressionMissing(): void
    {
        $this->expectExceptionObject(new ParseException('keyword "$calculate" is required'));
        CalculationVariable::parse((object) ['$calculateX' => '2 * 5'], $this->schemaParser);
    }

    public function testParseExpressionInvalid(): void
    {
        $this->expectExceptionObject(
            new ParseException(
                'Validating calculation expression failed: Variable "a" is not valid around position 5 for expression `2 * a'
            )
        );
        CalculationVariable::parse((object) ['$calculate' => '2 * a'], $this->schemaParser);
    }

    public function testParseFallbackNull(): void
    {
        $this->expectExceptionObject(new ParseException('fallback must not be null'));
        CalculationVariable::parse((object) ['$calculate' => '2 * 5', 'fallback' => null], $this->schemaParser);
    }
}
