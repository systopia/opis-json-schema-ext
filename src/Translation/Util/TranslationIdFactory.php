<?php

/*
 * Copyright 2023 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Translation\Util;

use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Errors\ErrorUtil;
use Systopia\JsonSchema\Translation\ErrorTranslator;

final class TranslationIdFactory
{
    /**
     * @return string An ID for translation. By default, it is the error's
     *                keyword unless the args already contain a translation ID.
     *                In cases where there are different messages for one
     *                keyword a postfix is appended. A common one is
     *                ".notAllowed". This indicates that the schema value was
     *                `false` and thus no sub schema was checked.
     *
     * @see \Opis\JsonSchema\Keyword implementations of this class
     * @see \Systopia\JsonSchema\Translation\TranslatorInterface
     * @see ErrorTranslator
     *
     * @codeCoverageIgnore
     */
    public static function getTranslationId(ValidationError $error): string
    {
        if (isset($error->args()[ErrorTranslator::TRANSLATION_ID_ARG_KEY])) {
            return $error->args()[ErrorTranslator::TRANSLATION_ID_ARG_KEY];
        }

        if ('additionalItems' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'additionalItems.notAllowed';
            }
        } elseif ('additionalProperties' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'additionalProperties.notAllowed';
            }
        } elseif ('contains' === $error->keyword()) {
            $keywordValue = ErrorUtil::getKeywordValue($error);
            if (false === $keywordValue) {
                return 'contains.false';
            }

            if (true === $keywordValue) {
                return 'contains.true';
            }
        } elseif ('minContains' === $error->keyword()) {
            if (\is_object(ErrorUtil::getKeywordValue($error))) {
                return 'minContains.schema';
            }
        } elseif ('maxContains' === $error->keyword()) {
            if (\is_object(ErrorUtil::getKeywordValue($error))) {
                return 'maxContains.schema';
            }
        } elseif ('contentSchema' === $error->keyword()) {
            if (false !== strpos($error->message(), 'Invalid JSON')) {
                return 'contentSchema.json';
            }
        } elseif ('dependencies' === $error->keyword()) {
            if (isset($error->args()['missing'])) {
                return 'dependencies.missing';
            }

            if ([] === $error->subErrors()) {
                return 'dependencies.notAllowed';
            }
        } elseif ('dependentSchemas' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'dependentSchemas.notAllowed';
            }
        } elseif ('items' === $error->keyword()) {
            $keywordValue = ErrorUtil::getKeywordValue($error);
            if (false === $keywordValue) {
                return 'items.false';
            }

            if ([] === $error->subErrors()) {
                return 'items.notAllowed';
            }
        } elseif ('not' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'not.notAllowed';
            }
        } elseif ('patternProperties' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'patternProperties.notAllowed';
            }
        } elseif ('properties' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'properties.notAllowed';
            }
        } elseif ('propertyNames' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'propertyNames.notAllowed';
            }
        } elseif ('unevaluatedItems' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'unevaluatedItems.notAllowed';
            }
        } elseif ('unevaluatedProperties' === $error->keyword()) {
            if ([] === $error->subErrors()) {
                return 'unevaluatedProperties.notAllowed';
            }
        }

        // 'Invalid $data' is used as error message by Opis if the keyword value
        // referenced by a JSON pointer could not be resolved to usable value.
        return 'Invalid $data' === $error->message() ? '_invalidData' : $error->keyword();
    }
}
