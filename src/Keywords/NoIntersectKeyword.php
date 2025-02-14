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
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;

final class NoIntersectKeyword implements Keyword
{
    use ErrorTrait;

    private string $beginPropertyName;

    private string $endPropertyName;

    public function __construct(string $beginPropertyName, string $endPropertyName)
    {
        $this->beginPropertyName = $beginPropertyName;
        $this->endPropertyName = $endPropertyName;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!ErrorCollectorUtil::getErrorCollector($context)->hasErrorAt($context->currentDataPath())) {
            /** @var list<\stdClass> $array */
            $array = $context->currentData();
            usort(
                $array,
                fn ($a, $b) => ($a->{$this->beginPropertyName} ?? null) <=> ($b->{$this->beginPropertyName} ?? null)
            );

            $count = \count($array);
            for ($i = 1; $i < $count; ++$i) {
                $begin = $array[$i]->{$this->beginPropertyName} ?? null;
                $previousEnd = $array[$i - 1]->{$this->endPropertyName} ?? null;
                if (($begin <=> $previousEnd) <= 0) {
                    return $this->error($schema, $context, 'noIntersect', 'The intervals must not intersect.');
                }
            }
        }

        return null;
    }
}
