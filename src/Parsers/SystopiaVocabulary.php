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

use Opis\JsonSchema\Parsers\DefaultVocabulary;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\PragmaParser;
use Systopia\JsonSchema\Parsers\Keywords\EvaluateKeywordParser;
use Systopia\JsonSchema\Parsers\Keywords\MaxDateKeywordParser;
use Systopia\JsonSchema\Parsers\Keywords\MinDateKeywordParser;
use Systopia\JsonSchema\Parsers\Keywords\PrecisionKeywordParser;
use Systopia\JsonSchema\Parsers\Keywords\ValidationsKeywordParser;
use Systopia\JsonSchema\Parsers\KeywordValidators\CalculateKeywordValidationParser;
use Systopia\JsonSchema\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser;

/**
 * @codeCoverageIgnore
 */
class SystopiaVocabulary extends DefaultVocabulary
{
    /**
     * @param KeywordParser[] $keywords
     * @param KeywordValidatorParser[] $keywordValidators
     * @param PragmaParser[] $pragmas
     */
    public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = [])
    {
        $keywords = array_merge($keywords, [
            new EvaluateKeywordParser(),
            new MaxDateKeywordParser(),
            new MinDateKeywordParser(),
            new PrecisionKeywordParser(),
            new ValidationsKeywordParser(),
        ]);

        $keywordValidators = array_merge(
            [new CollectErrorsKeywordValidatorParser()],
            $keywordValidators,
            [new CalculateKeywordValidationParser()]
        );

        parent::__construct($keywords, $keywordValidators, $pragmas);
    }
}
