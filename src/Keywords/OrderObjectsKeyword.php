<?php

/*
 * Copyright 2024 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;

final class OrderObjectsKeyword implements Keyword
{
    use SetValueTrait;

    /**
     * @phpstan-var array<int|string, 'ASC'|'DESC'>
     */
    private array $order;

    /**
     * @phpstan-param array<int|string, 'ASC'|'DESC'> $order
     */
    public function __construct(array $order)
    {
        $this->order = $order;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!ErrorCollectorUtil::getErrorCollector($context)->hasErrorAt($context->currentDataPath())
            && !ErrorCollectorUtil::getIgnoredErrorCollector($context)->hasErrorAt($context->currentDataPath())
            && 'array' === $context->currentDataType()
        ) {
            $this->setValue($context, function (array $array) {
                usort($array, [$this, 'compare']);

                return $array;
            });
        }

        return null;
    }

    private function compare(?\stdClass $a, ?\stdClass $b): int
    {
        if (null === $a || null === $b) {
            return $a <=> $b;
        }

        foreach ($this->order as $field => $direction) {
            $result = ($a->{$field} ?? null) <=> ($b->{$field} ?? null);
            if (0 !== $result) {
                return 'ASC' === $direction ? $result : -$result;
            }
        }

        return 0;
    }
}
