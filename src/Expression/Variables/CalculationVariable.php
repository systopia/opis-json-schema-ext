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

namespace Systopia\JsonSchema\Expression\Variables;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Calculation;
use Systopia\JsonSchema\Expression\CalculatorUtil;

final class CalculationVariable extends Variable
{
    private Calculation $calculation;

    private ?Variable $fallback;

    public function __construct(Calculation $calculation, ?Variable $fallback = null)
    {
        $this->calculation = $calculation;
        $this->fallback = $fallback;
    }

    public static function isAllowed(SchemaParser $parser): bool
    {
        return CalculatorUtil::hasCalculator($parser);
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        if (!self::isAllowed($parser)) {
            throw new ParseException('Parser option "calculator" is not set');
        }

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }
        $fallback = null === ($data->fallback ?? null) ? null : Variable::create($data->fallback, $parser);

        if (!property_exists($data, '$calculate')) {
            throw new ParseException('keyword "$calculate" is required');
        }

        $calculation = Calculation::parse($data->{'$calculate'}, $parser);

        try {
            CalculatorUtil::getCalculator($parser)->validateCalcExpression(
                $calculation->getExpression(),
                $calculation->getVariableNames()
            );
        } catch (\Exception $e) {
            throw new ParseException(sprintf('Validating calculation expression failed: %s', $e->getMessage()));
        }

        return new self($calculation, $fallback);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ValidationContext $context, int $flags = 0)
    {
        $fallback = $this->calculation->getFallback() ?? $this->fallback;
        if (null === $fallback) {
            $variables = $this->calculation->getVariables(
                $context,
                $flags | Variable::FLAG_FAIL_ON_UNRESOLVED
            );
        } else {
            try {
                $variables = $this->calculation->getVariables(
                    $context,
                    $flags | Variable::FLAG_FAIL_ON_UNRESOLVED
                );
            } catch (ReferencedDataHasViolationException|VariableResolveException $e) {
                return $fallback->getValue($context, $flags);
            }
        }

        $calculator = CalculatorUtil::getCalculatorFromContext($context);

        return $calculator->calculate(
            $this->calculation->getExpression(),
            $variables,
        ) ?? (null === $fallback ? null : $fallback->getValue($context, $flags));
    }
}
