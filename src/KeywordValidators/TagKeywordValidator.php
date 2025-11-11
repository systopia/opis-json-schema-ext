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

namespace Systopia\JsonSchema\KeywordValidators;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Tags\TaggedDataContainerUtil;

class TagKeywordValidator extends AbstractKeywordValidator
{
    /**
     * @var array<string, null|mixed>
     */
    private array $tags;

    /**
     * @param array<string, null|mixed> $tags mapping of tag to extra data
     */
    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        foreach ($this->tags as $tag => $extra) {
            /** @var list<int|string> $currentDataPath */
            $currentDataPath = $context->currentDataPath();
            TaggedDataContainerUtil::getTaggedPathsContainer($context)->add($tag, $currentDataPath, $extra);
        }

        return $this->next?->validate($context);
    }
}
