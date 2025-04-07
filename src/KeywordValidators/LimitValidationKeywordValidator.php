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
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\LimitValidation\LimitValidationRule;
use Systopia\JsonSchema\Util\SchemaUtil;

final class LimitValidationKeywordValidator extends ApplyLimitValidationKeywordValidator
{
    /**
     * @var list<self>
     */
    private static array $instanceStack = [];

    private bool $conditionMatched = false;

    private bool|\stdClass $conditionSchema;

    private bool|\stdClass $schema;

    public function __construct(bool|\stdClass $conditionSchema, array $rules, bool|\stdClass $schema)
    {
        $this->conditionSchema = $conditionSchema;
        $this->schema = $schema;
        parent::__construct($rules);
    }

    public static function getCurrentInstance(): ?self
    {
        return [] === self::$instanceStack ? null : end(self::$instanceStack);
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        self::$instanceStack[] = $this;

        try {
            $conditionSchema = SchemaUtil::loadSchema($this->conditionSchema, $context->loader());
            $errorCollector = ErrorCollectorUtil::getErrorCollector($context);
            ErrorCollectorUtil::setErrorCollector($context, clone $errorCollector);

            try {
                $this->conditionMatched = null === SchemaUtil::validateWithoutLimit($conditionSchema, $context);
            } finally {
                ErrorCollectorUtil::setErrorCollector($context, $errorCollector);
            }

            if ($this->conditionMatched) {
                // First continue with "normal" validation, so $schema might reference calculated data.
                $error = parent::validate($context);
                $schema = SchemaUtil::loadSchema($this->schema, $context->loader());
                $subSchemaError = SchemaUtil::validateWithoutLimit($schema, $context);

                if (null !== $subSchemaError) {
                    \assert(null !== $context->schema());

                    return null === $error ? $subSchemaError : new ValidationError(
                        '',
                        $context->schema(),
                        DataInfo::fromContext($context),
                        'Data must match schema',
                        [],
                        [$error, $subSchemaError]
                    );
                }

                return $error;
            }

            return $this->next?->validate($context);
        } finally {
            array_pop(self::$instanceStack);
        }
    }

    public function isConditionMatched(): bool
    {
        return $this->conditionMatched;
    }

    /**
     * @return list<LimitValidationRule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
