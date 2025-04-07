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

final class OrderSimpleKeyword implements Keyword
{
    use SetValueTrait;

    /**
     * @phpstan-var 'ASC'|'DESC'
     */
    private string $direction;

    /**
     * @phpstan-param 'ASC'|'DESC' $direction
     */
    public function __construct(string $direction)
    {
        $this->direction = $direction;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (!ErrorCollectorUtil::getErrorCollector($context)->hasErrorAt($context->currentDataPath())
            && !ErrorCollectorUtil::getIgnoredErrorCollector($context)->hasErrorAt($context->currentDataPath())
            && 'array' === $context->currentDataType()
        ) {
            $this->setValue($context, function (array $array) {
                'ASC' === $this->direction ? sort($array) : rsort($array);

                return $array;
            });
        }

        return null;
    }
}
