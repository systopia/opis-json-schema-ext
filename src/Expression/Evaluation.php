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

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;

final class Evaluation
{
    private string $expression;

    private ExpressionVariablesContainer $variablesContainer;

    private function __construct(
        string $expression,
        ?ExpressionVariablesContainer $variablesContainer = null
    ) {
        $this->expression = $expression;
        $this->variablesContainer = $variablesContainer ?? ExpressionVariablesContainer::createEmpty();
    }

    /**
     * @param \stdClass|string $data
     *
     * @throws ParseException
     */
    public static function parse($data, SchemaParser $parser): self
    {
        if (\is_string($data)) {
            return new self($data);
        }

        if (!$data instanceof \stdClass || !property_exists($data, 'expression')) {
            throw new ParseException('string or an object containing the property "expression" expected');
        }

        return new self(
            $data->expression,
            ExpressionVariablesContainer::parse($data->variables ?? (object) [], $parser),
        );
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReferencedDataHasViolationException|VariableResolveException
     */
    public function getVariables(ValidationContext $context, int $flags = 0): array
    {
        return $this->variablesContainer->getValues($context, $flags);
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return $this->variablesContainer->getNames();
    }
}
