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

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Translation\Util\TranslationIdFactory;
use Systopia\JsonSchema\Translation\Util\TranslationParamConverter;

final class ErrorTranslator
{
    /**
     * Can be used in the error arguments to use this value as translation ID
     * instead of the keyword.
     */
    public const TRANSLATION_ID_ARG_KEY = '__translationId';

    /**
     * Can be used in the error arguments to indicate that the message is
     * already translated. Errors that have this argument set to true won't be
     * translated, but just formatted with Opis error formatter.
     *
     * @see ErrorFormatter::formatErrorMessage()
     */
    public const TRANSLATED_ARG_KEY = '__translated';

    private ErrorFormatter $errorFormatter;

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->errorFormatter = new ErrorFormatter();
        $this->translator = $translator;
    }

    public function trans(ValidationError $error): string
    {
        if ($this->isTranslated($error)) {
            return $this->errorFormatter->formatErrorMessage($error);
        }

        $id = TranslationIdFactory::getTranslationId($error);
        $translated = $this->translator->trans(
            $id,
            $this->getTranslationParams($error),
            $error
        );

        return $id === $translated ? $this->errorFormatter->formatErrorMessage($error) : $translated;
    }

    /**
     * @phpstan-return array<string, scalar>
     */
    private function getTranslationParams(ValidationError $error): array
    {
        $params = ['keyword' => $error->keyword()];

        foreach ($error->args() as $key => $value) {
            $params[$key] = TranslationParamConverter::toTranslationParam($value);
        }

        return $params;
    }

    private function isTranslated(ValidationError $error): bool
    {
        return true === ($error->args()[self::TRANSLATED_ARG_KEY] ?? null);
    }
}
