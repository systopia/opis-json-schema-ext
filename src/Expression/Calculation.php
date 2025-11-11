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
use Systopia\JsonSchema\Expression\Variables\Variable;

final class Calculation
{
    private string $expression;

    private ExpressionVariablesContainer $variablesContainer;

    private ?Variable $fallback;

    private function __construct(
        string $expression,
        ?ExpressionVariablesContainer $variablesContainer = null,
        ?Variable $fallback = null
    ) {
        $this->expression = $expression;
        $this->variablesContainer = $variablesContainer ?? ExpressionVariablesContainer::createEmpty();
        $this->fallback = $fallback;
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

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }
        $fallback = null === ($data->fallback ?? null) ? null : Variable::create($data->fallback, $parser);

        if ([] === ($data->variables ?? [])) {
            $variablesContainer = ExpressionVariablesContainer::createEmpty();
        } else {
            $variablesContainer = ExpressionVariablesContainer::parse($data->variables, $parser);
        }

        return new self($data->expression, $variablesContainer, $fallback);
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param bool $violated Will be set to true, if false is given and one of
     *                       the variables is violated
     *
     * @return array<string, mixed>
     *
     * @throws ReferencedDataHasViolationException|VariableResolveException
     */
    public function getVariables(ValidationContext $context, int $flags = 0, bool &$violated = false): array
    {
        return $this->variablesContainer->getValues($context, $flags, $violated);
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return $this->variablesContainer->getNames();
    }

    public function getFallback(): ?Variable
    {
        return $this->fallback;
    }
}
