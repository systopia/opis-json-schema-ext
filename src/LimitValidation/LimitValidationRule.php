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

namespace Systopia\JsonSchema\LimitValidation;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\Util\CalculateUtil;
use Systopia\JsonSchema\Util\SchemaUtil;

final class LimitValidationRule
{
    private bool|Schema|\stdClass $keywordSchema;

    private bool|Schema|\stdClass $keywordValueSchema;

    private bool|Schema|\stdClass $valueSchema;

    private ?bool $calculatedValueUsedViolatedData;
    private bool $validate;

    private function __construct(
        bool|\stdClass $keywordSchema,
        bool|\stdClass $keywordValueSchema,
        bool|\stdClass $valueSchema,
        ?bool $calculatedValueUsedViolatedData,
        bool $validate
    ) {
        $this->keywordSchema = $keywordSchema;
        $this->keywordValueSchema = $keywordValueSchema;
        $this->valueSchema = $valueSchema;
        $this->calculatedValueUsedViolatedData = $calculatedValueUsedViolatedData;
        $this->validate = $validate;
    }

    /**
     * @param array{
     *     keyword?: bool|\stdClass,
     *     keywordValue?: bool|\stdClass,
     *     value?: bool|\stdClass,
     *     calculatedValueUsedViolatedData?: ?bool,
     *     validate?: bool,
     * } $rule
     */
    public static function create(array $rule): self
    {
        return new self(
            $rule['keyword'] ?? true,
            $rule['keywordValue'] ?? true,
            $rule['value'] ?? true,
            $rule['calculatedValueUsedViolatedData'] ?? null,
            $rule['validate'] ?? false
        );
    }

    public function shallValidate(ValidationError $error, ValidationContext $context): ?bool
    {
        return $this->isRuleMatched($error, $context) ? $this->validate : null;
    }

    private function isRuleMatched(ValidationError $error, ValidationContext $context): bool
    {
        if (!$this->keywordSchema instanceof Schema) {
            $this->keywordSchema = SchemaUtil::loadSchema($this->keywordSchema, $context->loader());
        }
        if (!$this->keywordValueSchema instanceof Schema) {
            $this->keywordValueSchema = SchemaUtil::loadSchema($this->keywordValueSchema, $context->loader());
        }
        if (!$this->valueSchema instanceof Schema) {
            $this->valueSchema = SchemaUtil::loadSchema($this->valueSchema, $context->loader());
        }

        return null === $this->subValidate($context, $this->keywordSchema, $error->keyword())
            && null === $this->subValidate(
                $context,
                $this->keywordValueSchema,
                // @phpstan-ignore property.dynamicName
                $error->schema()->info()->data()->{$error->keyword()}
            ) && null === $this->subValidate($context, $this->valueSchema, $error->data()->value())
            && (
                null === $this->calculatedValueUsedViolatedData
                || $this->calculatedValueUsedViolatedData === CalculateUtil::wasViolatedDataUsedForCalculatedValue(
                    $context,
                    JsonPointer::pathToString($error->data()->fullPath())
                )
            );
    }

    /**
     * @param mixed $data
     */
    private function subValidate(ValidationContext $context, Schema $schema, $data): ?ValidationError
    {
        $newContext = new ValidationContext($data, $context->loader());
        // Use cloned error collectors so it is possible to check if referenced data has violations.
        ErrorCollectorUtil::setIgnoredErrorCollector(
            $newContext,
            clone ErrorCollectorUtil::getIgnoredErrorCollector($context)
        );
        ErrorCollectorUtil::setErrorCollector(
            $newContext,
            clone ErrorCollectorUtil::getIgnoredErrorCollector($context)
        );

        return SchemaUtil::validateWithoutLimit($schema, $newContext);
    }
}
