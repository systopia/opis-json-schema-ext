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

final class ExpressionVariablesContainer
{
    /**
     * @var array<string, Variable>
     */
    private array $variables;

    /**
     * @param array<string, Variable> $variables
     */
    private function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        $variables = [];

        /** @var string $name */
        // @phpstan-ignore-next-line
        foreach ($data as $name => $variable) {
            $variables[$name] = Variable::create($variable, $parser);
        }

        return new self($variables);
    }

    /**
     * @param bool $violated Will be set to true, if false is given and one of
     *                       the variables is violated
     *
     * @return array<string, mixed>
     *
     * @throws ReferencedDataHasViolationException|VariableResolveException
     */
    public function getValues(ValidationContext $context, int $flags = 0, bool &$violated = false): array
    {
        return array_map(
            // With lambda function $violated would not be set.
            static function (Variable $variable) use ($context, $flags, &$violated) {
                return $variable->getValue($context, $flags, $violated);
            },
            $this->variables
        );
    }

    /**
     * @return array<string, Variable>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->variables);
    }
}
