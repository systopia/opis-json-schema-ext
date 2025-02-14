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

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\KeywordParserTrait;

trait EnsurePropertyTrait
{
    use KeywordParserTrait;

    /**
     * @throws InvalidKeywordException
     */
    protected function assertPropertyExists(
        \stdClass $data,
        string $property,
        SchemaInfo $info,
        ?string $keyword = null
    ): void {
        if (!property_exists($data, $property)) {
            throw $this->keywordException(
                \sprintf('{keyword} entries must contain property "%s"', $property),
                $info,
                $keyword
            );
        }
    }
}
