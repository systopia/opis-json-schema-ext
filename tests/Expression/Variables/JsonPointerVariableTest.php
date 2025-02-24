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

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Schemas\EmptySchema;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Variables\JsonPointerVariable;
use Systopia\JsonSchema\Expression\Variables\Variable;

/**
 * @covers \Systopia\JsonSchema\Expression\Variables\JsonPointerVariable
 */
final class JsonPointerVariableTest extends TestCase
{
    private SchemaParser $schemaParser;

    private SchemaLoader $schemaLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaParser = new SchemaParser();
        $this->schemaLoader = new SchemaLoader($this->schemaParser);
    }

    public function testIsAllowed(): void
    {
        self::assertFalse(JsonPointerVariable::isAllowed(new SchemaParser([], ['allowDataKeyword' => false])));
        self::assertTrue(JsonPointerVariable::isAllowed($this->schemaParser));
    }

    public function test(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $pointer = JsonPointer::parse('/x');
        Assertion::notNull($pointer);
        self::assertEquals(new JsonPointerVariable($pointer), $variable);

        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        $violated = false;
        self::assertSame('foo', $variable->getValue($context, 0, $violated));
        self::assertFalse($violated);
    }

    public function testViolatedIsNotReset(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $pointer = JsonPointer::parse('/x');
        Assertion::notNull($pointer);
        self::assertEquals(new JsonPointerVariable($pointer), $variable);

        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        $violated = true;
        self::assertSame('foo', $variable->getValue($context, 0, $violated));
        // $violated shall not be reset to false.
        self::assertTrue($violated);
    }

    public function testOnViolation(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);

        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
        $context->pushDataPath('x');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $violated = false;
        self::assertSame('foo', $variable->getValue($context, 0, $violated));
        self::assertTrue($violated);
    }

    public function testUnresolved(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        self::assertNull($variable->getValue($context));
    }

    public function testFallback(): void
    {
        $variable = JsonPointerVariable::create((object) ['$data' => '/x', 'fallback' => 'test'], $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        self::assertSame('test', $variable->getValue($context));
    }

    public function testFallbackDataPointer(): void
    {
        $variable = JsonPointerVariable::create(
            (object) [
                '$data' => '/x',
                'fallback' => (object) ['$data' => '/fallback'],
            ],
            $this->schemaParser
        );
        $context = new ValidationContext((object) ['fallback' => 'foo'], $this->schemaLoader);
        self::assertSame('foo', $variable->getValue($context));
    }

    public function testFailOnUnresolved(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);

        $this->expectException(VariableResolveException::class);
        $variable->getValue($context, Variable::FLAG_FAIL_ON_UNRESOLVED);
    }

    public function testFailOnViolation(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);

        $context = new ValidationContext('', $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
        $context->pushDataPath('x');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $this->expectException(ReferencedDataHasViolationException::class);
        $variable->getValue($context, Variable::FLAG_FAIL_ON_VIOLATION);
    }

    public function testParseWithDataPointerNotAllowed(): void
    {
        $schemaParser = new SchemaParser([], ['allowDataKeyword' => false]);
        $this->expectExceptionObject(new ParseException('keyword "$data" is not allowed'));
        JsonPointerVariable::parse((object) ['$data' => '/x'], $schemaParser);
    }

    public function testParseExpressionMissing(): void
    {
        $this->expectExceptionObject(new ParseException('keyword "$data" is required'));
        JsonPointerVariable::parse((object) ['$dataX' => '/x'], $this->schemaParser);
    }

    public function testParseExpressionInvalid(): void
    {
        $this->expectExceptionObject(new ParseException('Invalid JSON pointer "invalid!"'));
        JsonPointerVariable::parse((object) ['$data' => 'invalid!'], $this->schemaParser);
    }

    public function testParseFallbackNull(): void
    {
        $this->expectExceptionObject(new ParseException('fallback must not be null'));
        JsonPointerVariable::parse((object) ['$data' => '/x', 'fallback' => null], $this->schemaParser);
    }
}
