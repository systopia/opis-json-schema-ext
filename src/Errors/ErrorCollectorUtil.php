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

namespace Systopia\JsonSchema\Errors;

use Opis\JsonSchema\ValidationContext;

final class ErrorCollectorUtil
{
    public static function getErrorCollector(ValidationContext $context): ErrorCollectorInterface
    {
        if (!isset($context->globals()['errorCollector'])) {
            self::setErrorCollector($context, new ErrorCollector());
        }

        return $context->globals()['errorCollector'];
    }

    public static function setErrorCollector(ValidationContext $context, ErrorCollectorInterface $errorCollector): void
    {
        $context->setGlobals(['errorCollector' => $errorCollector]);
    }

    /**
     * Returns an error collector for ignored errors when using the
     * "$limitValidation" keyword.
     */
    public static function getIgnoredErrorCollector(ValidationContext $context): ErrorCollectorInterface
    {
        if (!isset($context->globals()['ignoredErrorCollector'])) {
            self::setIgnoredErrorCollector($context, new ErrorCollector());
        }

        return $context->globals()['ignoredErrorCollector'];
    }

    /**
     * Sets the error collector for ignored errors when using the "$limitValidation" keyword.
     */
    public static function setIgnoredErrorCollector(ValidationContext $context, ErrorCollectorInterface $errorCollector): void
    {
        $context->setGlobals(['ignoredErrorCollector' => $errorCollector]);
    }
}
