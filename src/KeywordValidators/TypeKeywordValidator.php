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

namespace Systopia\JsonSchema\KeywordValidators;

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Keywords\SetValueTrait;

/**
 * This is actually not a validator, but converts empty arrays to objects if
 * the schema type contains 'object', but not 'array'. This might be necessary
 * if the data to validate was already decoded.
 */
final class TypeKeywordValidator extends AbstractKeywordValidator
{
    use SetValueTrait;

    /**
     * {@inheritDoc}
     */
    public function validate(ValidationContext $context): ?ValidationError
    {
        if ([] === $context->currentData()) {
            $this->setValue($context, static fn () => new \stdClass());
        }

        Assertion::notNull($this->next);

        return $this->next->validate($context);
    }
}
