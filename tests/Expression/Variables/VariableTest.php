<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Expression\Variables;

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Expression\Calculation;
use Systopia\JsonSchema\Expression\Variables\CalculationVariable;
use Systopia\JsonSchema\Expression\Variables\IdentityVariable;
use Systopia\JsonSchema\Expression\Variables\JsonPointerVariable;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Parsers\SystopiaSchemaParser;

/**
 * @covers \Systopia\JsonSchema\Expression\Variables\Variable
 */
final class VariableTest extends TestCase
{
    private SchemaParser $schemaParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaParser = new SystopiaSchemaParser();
    }

    public function testCreateIdentity(): void
    {
        $variable = Variable::create('foo', $this->schemaParser);
        static::assertEquals(new IdentityVariable('foo'), $variable);
    }

    public function testCreateIdentityWithObject(): void
    {
        $data = (object) ['a' => 'b'];
        $variable = Variable::create($data, $this->schemaParser);
        static::assertEquals(new IdentityVariable($data), $variable);
    }

    public function testCreatePointer(): void
    {
        $variable = Variable::create((object) ['$data' => '/x', 'fallback' => 'test'], $this->schemaParser);
        $pointer = JsonPointer::parse('/x');
        Assertion::notNull($pointer);
        static::assertEquals(new JsonPointerVariable($pointer, 'test'), $variable);
    }

    public function testCreatePointerNotAllowed(): void
    {
        $this->expectExceptionObject(new ParseException('keyword "$data" is not allowed'));
        Variable::create((object) ['$data' => '/x'], new SchemaParser([], ['allowDataKeyword' => false]));
    }

    public function testCreateCalculation(): void
    {
        $variable = Variable::create((object) ['$calculate' => '2 * 5'], $this->schemaParser);
        $expectedVariable = new CalculationVariable(Calculation::parse('2 * 5', $this->schemaParser));
        static::assertEquals($expectedVariable, $variable);
    }

    public function testCreateCalculationNotAllowed(): void
    {
        $this->expectExceptionObject(new ParseException('Parser option "calculator" is not set'));
        Variable::create((object) ['$calculate' => 'a * b'], new SchemaParser());
    }

    public function testCreateNull(): void
    {
        $this->expectExceptionObject(new ParseException('null is not allowed as variable'));
        Variable::create(null, $this->schemaParser);
    }
}
