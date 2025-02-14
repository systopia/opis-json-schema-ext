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

namespace Systopia\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\PrecisionKeyword;

final class PrecisionKeywordParser extends KeywordParser
{
    public function __construct(string $keyword = 'precision')
    {
        parent::__construct($keyword);
    }

    /**
     * {@inheritDoc}
     */
    public function type(): string
    {
        return self::TYPE_NUMBER;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        $value = $this->keywordValue($info);
        if (!$value instanceof \stdClass && !\is_int($value)) {
            throw $this->keywordException('{keyword} must contain an integer', $info);
        }

        return new PrecisionKeyword(Variable::create($value, $parser));
    }
}
