<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class SymfonyExpressionHandler implements CalculatorInterface, EvaluatorInterface
{
    private ExpressionLanguage $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage = null)
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
