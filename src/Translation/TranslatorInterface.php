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

namespace Systopia\JsonSchema\Translation;

use Opis\JsonSchema\Errors\ValidationError;

interface TranslatorInterface
{
    /**
     * @param string $id The translation ID. Usually the keyword sometimes with
     *                   a postfix for differentiation.
     *
     * @phpstan-param array<string, scalar> $parameters
     *   Error arguments converted to scalars. Contains the validation keyword
     *   at key "keyword".
     *
     * @return string the translated error, or $id if no translation is
     *                available
     *
     * @see ErrorTranslator
     * @see \Systopia\JsonSchema\Translation\Util\TranslationIdFactory::getTranslationId()
     * @see \Systopia\JsonSchema\Translation\Util\TranslationParamConverter::toTranslationParam()
     * @see \Opis\JsonSchema\Keyword implementations of this class for possible parameters
     */
    public function trans(string $id, array $parameters, ValidationError $error): string;
}
