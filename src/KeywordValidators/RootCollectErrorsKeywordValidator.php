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
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;

final class RootCollectErrorsKeywordValidator extends CollectErrorsKeywordValidator
{
    public function validate(ValidationContext $context): ?ValidationError
    {
        if (!isset($context->globals()['errorCollector'])) {
            // @codeCoverageIgnoreStart
            ErrorCollectorUtil::setErrorCollector($context, new ErrorCollector());
            // @codeCoverageIgnoreEnd
        }

        return parent::validate($context);
    }

    protected function shouldIgnoreError(ValidationContext $context): bool
    {
        return false;
    }
}
