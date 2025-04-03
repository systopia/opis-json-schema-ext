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

namespace Systopia\JsonSchema\Util;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\KeywordValidators\ApplyLimitValidationKeywordValidator;

/**
 * @codeCoverageIgnore
 */
final class SchemaUtil
{
    public static function loadSchema(bool|\stdClass $schema, SchemaLoader $loader): Schema
    {
        return \is_bool($schema) ? $loader->loadBooleanSchema($schema) : $loader->loadObjectSchema($schema);
    }

    public static function validateWithoutLimit(Schema $schema, ValidationContext $context): ?ValidationError
    {
        $wasDisabled = ApplyLimitValidationKeywordValidator::$disabled;
        ApplyLimitValidationKeywordValidator::$disabled = true;

        try {
            return $schema->validate($context);
        } finally {
            ApplyLimitValidationKeywordValidator::$disabled = $wasDisabled;
        }
    }
}
