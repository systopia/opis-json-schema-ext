<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Expression;

use Assert\Assertion;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
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
        static::assertSame(['a'], $variableContainer->getNames());

        static::assertEquals(['a' => new IdentityVariable('b')], $variableContainer->getVariables());

        $validationContext = new ValidationContext('', $this->schemaLoader);
        static::assertSame(['a' => 'b'], $variableContainer->getValues($validationContext));
    }

    public function testParsePointer(): void
    {
        $data = (object) ['a' => (object) ['$data' => '/a']];
        $variableContainer = ExpressionVariablesContainer::parse($data, $this->schemaParser);
        static::assertSame(['a'], $variableContainer->getNames());

        $pointer = JsonPointer::parse('/a');
        Assertion::notNull($pointer);
        static::assertEquals(['a' => new JsonPointerVariable($pointer)], $variableContainer->getVariables());

        $validationContext = new ValidationContext((object) ['a' => 'b'], $this->schemaLoader);
        static::assertSame(['a' => 'b'], $variableContainer->getValues($validationContext));
    }

    public function testCreateEmpty(): void
    {
        $variableContainer = ExpressionVariablesContainer::createEmpty();
        static::assertSame([], $variableContainer->getNames());

        $validationContext = new ValidationContext('', $this->schemaLoader);
        static::assertSame([], $variableContainer->getValues($validationContext));
    }
}
