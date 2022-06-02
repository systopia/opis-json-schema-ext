<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;

final class CalculatorUtil
{
    public static function getCalculator(SchemaParser $parser): CalculatorInterface
    {
        return $parser->option('calculator');
    }

    public static function getCalculatorFromContext(ValidationContext $context): CalculatorInterface
    {
        return self::getCalculator($context->loader()->parser());
    }

    public static function hasCalculator(SchemaParser $parser): bool
    {
        return null !== $parser->option('calculator');
    }

    public static function hasCalculatorInContext(ValidationContext $context): bool
    {
        return self::hasCalculator($context->loader()->parser());
    }
}
