<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test\Expression;

use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\OpisJsonSchemaExt\Expression\Evaluation;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Expression\Evaluation
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

        static::assertSame('2 * 5 == data', $evaluation->getExpression());
        static::assertSame([], $evaluation->getVariableNames());
        static::assertSame([], $evaluation->getVariables($this->validationContext));
    }

    public function testParseSimple(): void
    {
        $data = (object) [
            'expression' => '2 * 5',
        ];
        $evaluation = Evaluation::parse($data, $this->schemaParser);

        static::assertSame('2 * 5', $evaluation->getExpression());
        static::assertSame([], $evaluation->getVariableNames());
        static::assertSame([], $evaluation->getVariables($this->validationContext));
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

        static::assertSame('a * b == data', $evaluation->getExpression());
        static::assertSame(['a', 'b'], $evaluation->getVariableNames());
        static::assertSame(['a' => 3, 'b' => 2], $evaluation->getVariables($this->validationContext));
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
