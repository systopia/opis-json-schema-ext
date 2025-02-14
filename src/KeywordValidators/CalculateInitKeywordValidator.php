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

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Keywords\SetValueTrait;

/**
 * Ensures that calculated properties exists so that the calculation will be
 * evaluated.
 */
final class CalculateInitKeywordValidator extends AbstractKeywordValidator
{
    use SetValueTrait;

    /**
     * @var string[]
     */
    private array $calculatedProperties;

    /**
     * @param string[] $calculatedProperties
     */
    public function __construct(array $calculatedProperties)
    {
        $this->calculatedProperties = $calculatedProperties;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        $data = $context->currentData();
        foreach ($this->calculatedProperties as $property) {
            if (!property_exists($data, $property)) {
                $context->pushDataPath($property);
                $this->setValue($context, static fn () => '$calculated');
                $context->popDataPath();
            }
        }

        Assertion::notNull($this->next);

        return $this->next->validate($context);
    }
}
