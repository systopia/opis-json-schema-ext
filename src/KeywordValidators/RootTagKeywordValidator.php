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
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Tags\TaggedDataContainerUtil;
use Systopia\JsonSchema\Tags\TaggedPathsContainer;

final class RootTagKeywordValidator extends AbstractKeywordValidator
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
        if (!isset($context->globals()['taggedPathsContainer'])) {
            $taggedPathsContainer = new TaggedPathsContainer();
            $context->setGlobals(['taggedPathsContainer' => $taggedPathsContainer]);
        }

        $error = null === $this->next ? null : $this->next->validate($context);
        $taggedDataContainer = TaggedDataContainerUtil::getTaggedDataContainer($context);

        // On sub schema validation this method is called again, so we want to
        // fill the data container only at the end of the overall validation.
        if (isset($taggedPathsContainer)) {
            $taggedPathsContainer->fillDataContainer($context->rootData(), $taggedDataContainer);
        }

        // The root data in context cannot be changed, so we have to use the
        // current data here to have the actual value (in case it has been
        // changed by some keyword).
        if ([] !== $this->tags) {
            $data = $context->currentData();
            $dataPointer = JsonPointer::pathToString($context->currentDataPath());
            foreach ($this->tags as $tag => $extra) {
                $taggedDataContainer->add($tag, $dataPointer, $data, $extra);
            }
        }

        return $error;
    }
}
