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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SymfonyExpressionHandler implements CalculatorInterface, EvaluatorInterface
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct(?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage();
    }

    public static function isAvailable(): bool
    {
        return class_exists(ExpressionLanguage::class);
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(string $expression, array $variables = [])
    {
        return $this->expressionLanguage->evaluate($expression, $variables);
    }

    /**
     * {@inheritDoc}
     */
    public function validateCalcExpression(string $expression, array $variableNames = []): void
    {
        $this->expressionLanguage->parse($expression, $variableNames);
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate(string $expression, array $variables = []): bool
    {
        return $this->expressionLanguage->evaluate($expression, $variables);
    }

    /**
     * {@inheritDoc}
     */
    public function validateEvaluationExpression(string $expression, array $variableNames = []): void
    {
        $this->expressionLanguage->parse($expression, $variableNames);
    }
}
