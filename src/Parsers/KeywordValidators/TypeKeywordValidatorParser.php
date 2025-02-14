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
use Systopia\JsonSchema\KeywordValidators\TypeKeywordValidator;

/**
 * @see TypeKeywordValidator
 */
class TypeKeywordValidatorParser extends KeywordValidatorParser
{
    public function __construct()
    {
        parent::__construct('type');
    }

    /**
     * {@inheritDoc}
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator
    {
        if (true !== $parser->option('convertEmptyArrays')) {
            return null;
        }

        if (!$this->keywordExists($info)) {
            return null;
        }

        $type = (array) $this->keywordValue($info);
        if (\in_array('object', $type, true) && !\in_array('array', $type, true)) {
            return new TypeKeywordValidator();
        }

        return null;
    }
}
