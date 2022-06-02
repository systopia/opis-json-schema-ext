<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Parsers;

use Opis\JsonSchema\Parsers\DefaultVocabulary;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\PragmaParser;
use Systopia\OpisJsonSchemaExt\Parsers\Keywords\EvaluateKeywordParser;
use Systopia\OpisJsonSchemaExt\Parsers\Keywords\ValidationsKeywordParser;
use Systopia\OpisJsonSchemaExt\Parsers\KeywordValidators\CalculateKeywordValidationParser;
use Systopia\OpisJsonSchemaExt\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser;

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
