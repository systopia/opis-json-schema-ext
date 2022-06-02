<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\KeywordValidators;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Errors\ErrorCollectorUtil;

class CollectErrorsKeywordValidator extends AbstractKeywordValidator
{
    public function validate(ValidationContext $context): ?ValidationError
    {
        $error = null === $this->next ? null : $this->next->validate($context);
        if (null !== $error) {
            ErrorCollectorUtil::getErrorCollector($context)->addError($error);
        }

        return $error;
    }
}
