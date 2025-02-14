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

declare(strict_types=1);

namespace Systopia\JsonSchema\Expression;

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
