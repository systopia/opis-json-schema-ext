<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

interface CalculatorInterface
{
    /**
     * @param array<string, mixed> $variables
     *
     * @return mixed
     */
    public function calculate(string $expression, array $variables = []);

    /**
     * @param string[] $variableNames
     *
     * @throws \Exception
     */
    public function validateCalcExpression(string $expression, array $variableNames = []): void;
}
