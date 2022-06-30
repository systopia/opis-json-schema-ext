<?php
/*
 * Copyright 2022 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Errors;

use Opis\JsonSchema\Errors\ValidationError;

interface ErrorCollectorInterface
{
    public function addError(ValidationError $error): void;

    /**
     * @return array<string, non-empty-array<ValidationError>>
     */
    public function getErrors(): array;

    public function hasErrors(): bool;

    /**
     * @param array<int|string>|string $path
     *
     * @return ValidationError[]
     */
    public function getErrorsAt($path): array;

    /**
     * @param array<int|string>|string $path
     */
    public function hasErrorAt($path): bool;

    /**
     * @return array<string, non-empty-array<ValidationError>>
     */
    public function getLeafErrors(): array;

    /**
     * @param array<int|string>|string $path
     *
     * @return ValidationError[]
     */
    public function getLeafErrorsAt($path): array;

    /**
     * @param array<int|string>|string $path
     */
    public function hasLeafErrorAt($path): bool;
}
