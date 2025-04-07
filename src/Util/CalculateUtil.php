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

use Opis\JsonSchema\ValidationContext;

final class CalculateUtil
{
    public static function setCalculatedValueUsedViolatedData(
        ValidationContext $context,
        string $path,
        bool $violatedDataUsed
    ): void {
        $valueUsedViolatedData = $context->globals()['calculatedValueUsedViolatedData'] ?? null;
        if (null === $valueUsedViolatedData) {
            $valueUsedViolatedData = new \ArrayObject();
            $context->setGlobals(['calculatedValueUsedViolatedData' => $valueUsedViolatedData]);
        }

        $valueUsedViolatedData[$path] = $violatedDataUsed;
    }

    public static function wasViolatedDataUsedForCalculatedValue(ValidationContext $context, string $path): ?bool
    {
        return $context->globals()['calculatedValueUsedViolatedData'][$path] ?? null;
    }
}
