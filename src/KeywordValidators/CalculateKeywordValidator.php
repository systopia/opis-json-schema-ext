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
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Calculation;
use Systopia\JsonSchema\Expression\Variables\CalculationVariable;
use Systopia\JsonSchema\Keywords\SetValueTrait;
use Systopia\JsonSchema\Translation\ErrorTranslator;

final class CalculateKeywordValidator extends AbstractKeywordValidator
{
    use ErrorTrait;
    use SetValueTrait;

    private Calculation $calculation;

    public function __construct(Calculation $calculation)
    {
        $this->calculation = $calculation;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        $calculationVariable = new CalculationVariable($this->calculation);

        try {
            $value = $calculationVariable->getValue($context);
        } catch (ReferencedDataHasViolationException|VariableResolveException $e) {
            $value = null;
        }

        if (null === $value) {
            return $this->handleCalculationFailed($context);
        }

        $this->setValue($context, static fn () => $value);

        return null === $this->next ? null : $this->next->validate($context);
    }

    private function handleCalculationFailed(ValidationContext $context): ?ValidationError
    {
        $schema = $context->schema();
        Assertion::notNull($schema);
        $this->unsetValue($context);
        if ($this->isRequired($context)) {
            // "required" is checked before calculation
            return $this->error(
                $schema,
                $context,
                '$calculate',
                'The property is required, but could not be calculated because of invalid data or unresolvable variables',
                [ErrorTranslator::TRANSLATION_ID_ARG_KEY => '$calculate.required.unresolved'],
            );
        }

        return null;
    }

    private function isRequired(ValidationContext $context): bool
    {
        $path = $context->currentDataPath();
        $key = end($path);
        $parentSchema = $this->getPropertiesSchema($context);

        return \in_array($key, $parentSchema->info()->data()->required ?? [], true);
    }

    private function getPropertiesSchema(ValidationContext $context): Schema
    {
        $loader = $context->loader();
        $schema = $context->schema();
        Assertion::notNull($schema);
        $path = $schema->info()->path();
        Assertion::inArray('properties', $path);
        foreach (array_reverse($path) as $key) {
            if ('properties' === $key) {
                break;
            }

            Assertion::notNull($schema->info()->base());
            $schema = $loader->loadSchemaById($schema->info()->base());
            Assertion::notNull($schema);
        }

        return $schema;
    }
}
