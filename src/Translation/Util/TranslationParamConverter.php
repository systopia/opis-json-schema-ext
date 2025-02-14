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

use Systopia\JsonSchema\Util\TypeChecker;

final class TranslationParamConverter
{
    /**
     * @param mixed $value
     *
     * @return scalar
     *
     * @see \Systopia\JsonSchema\Translation\TranslatorInterface
     */
    public static function toTranslationParam($value)
    {
        if (\is_scalar($value)) {
            return $value;
        }

        if (TypeChecker::isScalarArray($value)) {
            return implode(', ', $value);
        }

        if (\is_object($value) && method_exists($value, '__toString')) {
            return $value->__toString();
        }

        return '('.\gettype($value).')';
    }
}
