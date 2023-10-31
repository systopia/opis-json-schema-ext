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

final class TranslatorFactory
{
    public static function createTranslator(string $locale): TranslatorInterface
    {
        $translationFile = self::getTranslationFile($locale);
        if (null === $translationFile) {
            return new NullTranslator();
        }

        $messages = require $translationFile;

        return new Translator($locale, $messages);
    }

    private static function getTranslationFile(string $locale): ?string
    {
        $filename = sprintf(__DIR__.'/../../messages/%s.php', $locale);
        if (file_exists($filename)) {
            return $filename;
        }

        [$lang] = explode('_', $locale, 2);
        if ($lang !== $locale) {
            $filename = sprintf(__DIR__.'/../../messages/%s.php', $lang);
            if (file_exists($filename)) {
                return $filename;
            }
        }

        return null;
    }
}
