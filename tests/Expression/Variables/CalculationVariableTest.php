<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test\Expression\Variables;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Schemas\EmptySchema;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\OpisJsonSchemaExt\Errors\ErrorCollector;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;
use Systopia\OpisJsonSchemaExt\Expression\Variables\CalculationVariable;
use Systopia\OpisJsonSchemaExt\Expression\Variables\Variable;
use Systopia\OpisJsonSchemaExt\Parsers\SystopiaSchemaParser;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Expression\Variables\CalculationVariable
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
        static::assertFalse(CalculationVariable::isAllowed(new SchemaParser()));
        static::assertTrue(CalculationVariable::isAllowed($this->schemaParser));
    }

    public function testParse(): void
    {
        $variable = CalculationVariable::parse((object) ['$calculate' => '2 * 5'], $this->schemaParser);
        $context = new ValidationContext((object) ['x' => 'foo'], $this->schemaLoader);
        static::assertSame(10, $variable->getValue($context));
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
        static::assertSame(3, $variable->getValue($context));
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
        static::assertSame(5, $variable->getValue($context));
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
            ],
        ];
        $variable = CalculationVariable::parse($data, $this->schemaParser);

        $context = new ValidationContext('', $this->schemaLoader);
        $errorCollector = new ErrorCollector();
        $context->setGlobals(['errorCollector' => $errorCollector]);
        $context->pushDataPath('a');
        $schemaInfo = new SchemaInfo(true, null);
        $error = new ValidationError('test', new EmptySchema($schemaInfo), DataInfo::fromContext($context), '');
        $errorCollector->addError($error);
        $context->popDataPath();

        $this->expectException(ReferencedDataHasViolationException::class);
        $variable->getValue($context, Variable::FLAG_FAIL_ON_VIOLATION);
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
