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

namespace Systopia\JsonSchema\Parsers;

use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;

/**
 * @codeCoverageIgnore
 */
class SystopiaSchemaParser extends SchemaParser
{
    /**
     * {@inheritDoc}
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(array $resolvers = [], array $options = [], ?SystopiaVocabulary $vocabulary = null)
    {
        parent::__construct($resolvers, $this->buildOptions($options), $vocabulary ?? new SystopiaVocabulary());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function buildOptions(array $options): array
    {
        if (!isset($options['calculator']) || !isset($options['evaluator'])) {
            if (SymfonyExpressionHandler::isAvailable()) {
                $expressionHandler = new SymfonyExpressionHandler();
                $options['calculator'] ??= $expressionHandler;
                $options['evaluator'] ??= $expressionHandler;
            }
        }

        return $options;
    }
}
