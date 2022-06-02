<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\KeywordValidators;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Errors\ErrorCollector;

final class RootCollectErrorsKeywordValidator extends CollectErrorsKeywordValidator
{
    public function validate(ValidationContext $context): ?ValidationError
    {
        if (!isset($context->globals()['errorCollector'])) {
            // @codeCoverageIgnoreStart
            $context->setGlobals(['errorCollector' => new ErrorCollector()]);
            // @codeCoverageIgnoreEnd
        }

        return parent::validate($context);
    }
}
