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

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
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
use Systopia\JsonSchema\Expression\ExpressionVariablesContainer;
use Systopia\JsonSchema\Expression\Variables\IdentityVariable;
use Systopia\JsonSchema\Expression\Variables\JsonPointerVariable;
use Systopia\JsonSchema\Parsers\SystopiaSchemaParser;

/**
 * @covers \Systopia\JsonSchema\Expression\ExpressionVariablesContainer
 */
final class ExpressionVariablesContainerTest extends TestCase
{
    private SchemaParser $schemaParser;

    private SchemaLoader $schemaLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaParser = new SystopiaSchemaParser();
        $this->schemaLoader = new SchemaLoader($this->schemaParser);
    }

    public function testParseSimple(): void
    {
        $variableContainer = ExpressionVariablesContainer::parse((object) ['a' => 'b'], $this->schemaParser);
        self::assertSame(['a'], $variableContainer->getNames());

        self::assertEquals(['a' => new IdentityVariable('b')], $variableContainer->getVariables());

        $validationContext = new ValidationContext('', $this->schemaLoader);
        $violated = false;
        self::assertSame(['a' => 'b'], $variableContainer->getValues($validationContext, 0, $violated));
        self::assertFalse($violated);
    }

    public function testParsePointer(): void
    {
        $data = (object) ['a' => (object) ['$data' => '/a']];
        $variableContainer = ExpressionVariablesContainer::parse($data, $this->schemaParser);
        self::assertSame(['a'], $variableContainer->getNames());

        $pointer = JsonPointer::parse('/a');
        Assertion::notNull($pointer);
        self::assertEquals(['a' => new JsonPointerVariable($pointer)], $variableContainer->getVariables());

        $validationContext = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);
        $violated = false;
        self::assertSame(['a' => 'b'], $variableContainer->getValues($validationContext, 0, $violated));
        self::assertFalse($violated);

        $errorCollector = new ErrorCollector();
        ErrorCollectorUtil::setErrorCollector($validationContext, $errorCollector);
        $validationContext->pushDataPath('a');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($validationContext), '');
        $errorCollector->addError($error);
        $validationContext->popDataPath();

        self::assertSame(['a' => 'b'], $variableContainer->getValues($validationContext, 0, $violated));
        self::assertTrue($violated);
    }

    public function testCreateEmpty(): void
    {
        $variableContainer = ExpressionVariablesContainer::createEmpty();
        self::assertSame([], $variableContainer->getNames());

        $validationContext = new ValidationContext('', $this->schemaLoader);
        self::assertSame([], $variableContainer->getValues($validationContext));
    }
}
