<?php

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
        static::assertFalse(JsonPointerVariable::isAllowed(new SchemaParser([], ['allowDataKeyword' => false])));
        static::assertTrue(JsonPointerVariable::isAllowed($this->schemaParser));
    }

    public function test(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $pointer = JsonPointer::parse('/x');
        Assertion::notNull($pointer);
        static::assertEquals(new JsonPointerVariable($pointer), $variable);

        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        static::assertSame('foo', $variable->getValue($context));
    }

    public function testUnresolved(): void
    {
        $variable = JsonPointerVariable::parse((object) ['$data' => '/x'], $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        static::assertNull($variable->getValue($context));
    }

    public function testFallback(): void
    {
        $variable = JsonPointerVariable::create((object) ['$data' => '/x', 'fallback' => 'test'], $this->schemaParser);
        $context = new ValidationContext('', $this->schemaLoader);
        static::assertSame('test', $variable->getValue($context));
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
        $context->setGlobals(['errorCollector' => $errorCollector]);
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
