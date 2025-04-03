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
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Util\SchemaUtil;

final class LimitValidationRule
{
    private bool|Schema|\stdClass $keywordSchema;

    private bool|Schema|\stdClass $keywordValueSchema;

    private bool|Schema|\stdClass $valueSchema;

    private bool $validate;

    public function __construct(
        bool|\stdClass $keywordSchema,
        bool|\stdClass $keywordValueSchema,
        bool|\stdClass $valueSchema,
        bool $validate
    ) {
        $this->keywordSchema = $keywordSchema;
        $this->keywordValueSchema = $keywordValueSchema;
        $this->valueSchema = $valueSchema;
        $this->validate = $validate;
    }

    /**
     * @param array{
     *     keyword?: \stdClass|bool,
     *     keywordValue?: \stdClass|bool,
     *     value?: \stdClass|bool,
     *     validate?: bool,
     * } $rule
     */
    public static function create(array $rule): self
    {
        return new self(
            $rule['keyword'] ?? true,
            $rule['keywordValue'] ?? true,
            $rule['value'] ?? true,
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

        return null === SchemaUtil::validateWithoutLimit(
            $this->keywordSchema,
            new ValidationContext($error->keyword(), $context->loader())
        ) && null === SchemaUtil::validateWithoutLimit(
            $this->keywordValueSchema,
            // @phpstan-ignore property.dynamicName
            new ValidationContext($error->schema()->info()->data()->{$error->keyword()}, $context->loader())
        ) && null === SchemaUtil::validateWithoutLimit(
            $this->valueSchema,
            new ValidationContext($error->data()->value(), $context->loader())
        );
    }
}
