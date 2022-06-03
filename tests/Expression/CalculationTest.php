<?php

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

        static::assertSame('2 * 5', $calculation->getExpression());
        static::assertNull($calculation->getFallback());
        static::assertSame([], $calculation->getVariableNames());
        static::assertSame([], $calculation->getVariables($this->validationContext));
    }

    public function testParseSimple(): void
    {
        $data = (object) [
            'expression' => '2 * 5',
        ];
        $calculation = Calculation::parse($data, $this->schemaParser);

        static::assertSame('2 * 5', $calculation->getExpression());
        static::assertNull($calculation->getFallback());
        static::assertSame([], $calculation->getVariableNames());
        static::assertSame([], $calculation->getVariables($this->validationContext));
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

        static::assertSame('a * b', $calculation->getExpression());
        static::assertSame(4, $calculation->getFallback());
        static::assertSame(['a', 'b'], $calculation->getVariableNames());
        static::assertSame(['a' => 3, 'b' => 2], $calculation->getVariables($this->validationContext));
    }

    public function testParseNoExpression(): void
    {
        $data = (object) [
            'expressionX' => '2 * 5',
        ];

        $this->expectException(SchemaException::class);
        $calculation = Calculation::parse($data, $this->schemaParser);
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
