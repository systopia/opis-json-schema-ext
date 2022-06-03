<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Expression;

use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Expression\CalculatorInterface;
use Systopia\JsonSchema\Expression\CalculatorUtil;

/**
 * @covers \Systopia\JsonSchema\Expression\CalculatorUtil
 */
final class CalculatorUtilTest extends TestCase
{
    public function testWithCalculator(): void
    {
        $calculator = $this->createMock(CalculatorInterface::class);
        $parser = new SchemaParser([], ['calculator' => $calculator]);
        static::assertTrue(CalculatorUtil::hasCalculator($parser));
        static::assertSame($calculator, CalculatorUtil::getCalculator($parser));

        $context = new ValidationContext('', new SchemaLoader($parser));
        static::assertTrue(CalculatorUtil::hasCalculatorInContext($context));
        static::assertSame($calculator, CalculatorUtil::getCalculatorFromContext($context));
    }

    public function testWithoutCalculator(): void
    {
        $parser = new SchemaParser();
        static::assertFalse(CalculatorUtil::hasCalculator($parser));

        $context = new ValidationContext('', new SchemaLoader($parser));
        static::assertFalse(CalculatorUtil::hasCalculatorInContext($context));
    }
}
