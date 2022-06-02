<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Parsers\KeywordValidators;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\OpisJsonSchemaExt\KeywordValidators\CollectErrorsKeywordValidator;
use Systopia\OpisJsonSchemaExt\KeywordValidators\RootCollectErrorsKeywordValidator;

final class CollectErrorsKeywordValidatorParser extends KeywordValidatorParser
{
    public function __construct()
    {
        parent::__construct('');
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): KeywordValidator
    {
        if ($info->isDocumentRoot()) {
            return new RootCollectErrorsKeywordValidator();
        }

        return new CollectErrorsKeywordValidator();
    }
}
