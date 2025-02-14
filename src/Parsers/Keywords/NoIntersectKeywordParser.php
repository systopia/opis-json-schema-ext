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

namespace Systopia\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Keywords\NoIntersectKeyword;
use Systopia\JsonSchema\Parsers\EnsurePropertyTrait;

final class NoIntersectKeywordParser extends KeywordParser
{
    use EnsurePropertyTrait;

    public function __construct(string $keyword = 'noIntersect')
    {
        parent::__construct($keyword);
    }

    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        $noIntersect = $this->keywordValue($info);
        if (!$noIntersect instanceof \stdClass) {
            throw $this->keywordException('{keyword} must contain an object with "begin" and "end"', $info);
        }

        $this->assertPropertyExists($noIntersect, 'begin', $info);
        $this->assertPropertyExists($noIntersect, 'end', $info);

        return new NoIntersectKeyword($noIntersect->begin, $noIntersect->end);
    }
}
