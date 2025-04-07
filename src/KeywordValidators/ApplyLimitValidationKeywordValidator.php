<?php

/*
 * Copyright 2025 SYSTOPIA GmbH
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
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\LimitValidation\LimitValidationRule;

class ApplyLimitValidationKeywordValidator extends AbstractKeywordValidator
{
    public static bool $disabled = false;

    /**
     * @var list<LimitValidationRule>
     */
    protected array $rules;

    /**
     * @param list<LimitValidationRule> $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        $error = $this->next?->validate($context);

        return (null === $error || self::$disabled) ? $error : $this->handleError($error, $context);
    }

    private function handleError(ValidationError $error, ValidationContext $context): ?ValidationError
    {
        if ('' !== $error->keyword()) {
            if ($this->shouldIgnoreError($error, $context)) {
                ErrorCollectorUtil::getIgnoredErrorCollector($context)->addError($error);

                return null;
            }

            return $error;
        }

        $subErrors = $error->subErrors();
        foreach ($subErrors as $index => &$subError) {
            $subError = $this->handleError($subError, $context);
            if (null === $subError) {
                unset($subErrors[$index]);
            }
        }

        if ([] === $subErrors) {
            return null;
        }

        if (1 === \count($subErrors)) {
            return reset($subErrors);
        }

        return new ValidationError(
            '',
            $error->schema(),
            $error->data(),
            $error->message(),
            $error->args(),
            array_values($subErrors)
        );
    }

    private function shouldIgnoreError(ValidationError $error, ValidationContext $context): bool
    {
        foreach ($this->rules as $rule) {
            $shallValidate = $rule->shallValidate($error, $context);
            if (null !== $shallValidate) {
                return !$shallValidate;
            }
        }

        return false;
    }
}
