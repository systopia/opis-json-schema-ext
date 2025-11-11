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

namespace Systopia\JsonSchema\KeywordValidators;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;

class CollectErrorsKeywordValidator extends AbstractKeywordValidator
{
    public function validate(ValidationContext $context): ?ValidationError
    {
        $error = $this->next?->validate($context);
        if (null !== $error && !$this->shouldIgnoreError($context)) {
            ErrorCollectorUtil::getErrorCollector($context)->addError($error);
        }

        return $error;
    }

    /**
     * @return bool true if an error should not be added to the error collector,
     *              e.g. if the parent is a oneOf keyword.
     */
    protected function shouldIgnoreError(ValidationContext $context): bool
    {
        $schema = $context->schema();
        if (null === $schema) {
            return false;
        }

        $path = $schema->info()->path();
        if (\in_array('if', $path, true)) {
            return true;
        }

        foreach (ErrorCollector::getExtraLeafErrorKeywords() as $leafErrorKeyword) {
            if (\in_array($leafErrorKeyword, $path, true)) {
                return true;
            }
        }

        return false;
    }
}
