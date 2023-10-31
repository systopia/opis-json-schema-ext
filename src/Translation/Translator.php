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

final class Translator implements TranslatorInterface
{
    private string $locale;

    /**
     * @phpstan-var array<string, string>
     */
    private array $messages = [];

    /**
     * @phpstan-param array<string, string> $messages
     */
    public function __construct(string $locale, array $messages)
    {
        $this->locale = $locale;
        $this->messages = $messages;
    }

    /**
     * @phpstan-param array<string, string> $messages
     */
    public function addMessages(array $messages): void
    {
        $this->messages = $messages + $this->messages;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function trans(string $id, array $parameters, ValidationError $error): string
    {
        $translated = \MessageFormatter::formatMessage($this->locale, $this->messages[$id] ?? $id, $parameters);

        return false === $translated ? $id : $translated;
    }
}
