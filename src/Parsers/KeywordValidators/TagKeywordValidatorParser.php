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

namespace Systopia\JsonSchema\Parsers\KeywordValidators;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\KeywordValidators\RootTagKeywordValidator;
use Systopia\JsonSchema\KeywordValidators\TagKeywordValidator;

final class TagKeywordValidatorParser extends KeywordValidatorParser
{
    public function __construct(string $keyword = '$tag')
    {
        parent::__construct($keyword);
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator
    {
        if (!$this->keywordExists($info)) {
            return $info->isDocumentRoot() ? new RootTagKeywordValidator([]) : null;
        }

        $tags = (array) $this->keywordValue($info);
        $parsedTags = [];
        foreach ($tags as $key => $value) {
            if (\is_string($key)) {
                $parsedTags[$key] = $value;
            } elseif (\is_string($value)) {
                $parsedTags[$value] = null;
            } else {
                throw $this->keywordException('Invalid value for keyword {keyword}', $info);
            }
        }

        if ($info->isDocumentRoot()) {
            return new RootTagKeywordValidator($parsedTags);
        }

        return [] === $parsedTags ? null : new TagKeywordValidator($parsedTags);
    }
}
