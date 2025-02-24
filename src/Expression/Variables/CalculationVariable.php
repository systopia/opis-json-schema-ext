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
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\CalculationFailedException;
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
            throw new ParseException(\sprintf('Validating calculation expression failed: %s', $e->getMessage()));
        }

        return new self($calculation, $fallback);
    }

    /**
     * {@inheritDoc}
     *
     * The calculation is tried, even if one of the variables has a violation,
     * because in case of objects the violated value might not be involved.
     *
     * @throws ReferencedDataHasViolationException if calculation fails, one of the variables has a violation, and the
     *                                             flag Variable::FLAG_FAIL_ON_VIOLATION is set or no fallback is
     *                                             defined
     * @throws VariableResolveException if one of the variables cannot be resolved and the flag
     *                                  Variable::FLAG_FAIL_ON_UNRESOLVED is set or no fallback is defined
     */
    public function getValue(ValidationContext $context, int $flags = 0, ?bool &$violated = null)
    {
        if (false === $violated) {
            $variableViolated = &$violated;
        } else {
            $variableViolated = false;
        }

        $fallback = $this->calculation->getFallback() ?? $this->fallback;
        if (null === $fallback || 0 !== ($flags & Variable::FLAG_FAIL_ON_UNRESOLVED)) {
            $variables = $this->calculation->getVariables(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED,
                $variableViolated
            );
        } else {
            try {
                $variables = $this->calculation->getVariables(
                    $context,
                    Variable::FLAG_FAIL_ON_UNRESOLVED,
                    $variableViolated
                );
            } catch (VariableResolveException $e) {
                return $fallback->getValue($context, $flags, $violated);
            }
        }

        $calculator = CalculatorUtil::getCalculatorFromContext($context);

        try {
            // The calculation is tried, even if one of the variable has a
            // violation, because in case of objects the violated value might
            // not be involved.
            $result = $calculator->calculate(
                $this->calculation->getExpression(),
                $variables,
            );
        } catch (CalculationFailedException $e) {
            // Evaluating the expression might fail in case of violations so we
            // only re-throw the exception if there was no violation.
            if (!$variableViolated) {
                throw $e;
            }

            if (null === $fallback || 0 !== ($flags & Variable::FLAG_FAIL_ON_VIOLATION)) {
                throw new ReferencedDataHasViolationException(
                    \sprintf(
                        'The calculation at path "%s" failed because of violations in the referenced data',
                        JsonPointer::pathToString($context->currentDataPath())
                    ),
                    0,
                    $e
                );
            }
        }

        return $result ?? (null === $fallback ? null : $fallback->getValue($context, $flags, $violated));
    }
}
