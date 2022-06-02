<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

interface EvaluatorInterface
{
    /**
     * @param array<string, mixed> $variables
     */
    public function evaluate(string $expression, array $variables = []): bool;

    /**
     * @param string[] $variableNames
     *
     * @throws \Exception
     */
    public function validateEvaluationExpression(string $expression, array $variableNames = []): void;
}
