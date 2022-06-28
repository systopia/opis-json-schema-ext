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
use Opis\JsonSchema\JsonPointer;

final class ErrorCollector implements ErrorCollectorInterface
{
    /**
     * @var array<ValidationError[]>
     */
    private array $errors = [];

    /**
     * @var array<string, ValidationError[]>
     */
    private array $leafErrors = [];

    public function addError(ValidationError $error): void
    {
        $path = $this->pathToString($error->data()->fullPath());
        if (isset($this->errors[$path])) {
            $this->errors[$path][] = $error;
        } else {
            $this->errors[$path] = [$error];
        }

        if ($this->isLeafError($error)) {
            $this->addLeafError($error);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorsAt($path): array
    {
        return $this->errors[$this->pathToString($path)] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function hasErrorAt($path): bool
    {
        return isset($this->errors[$this->pathToString($path)]);
    }

    /**
     * {@inheritDoc}
     */
    public function getLeafErrors(): array
    {
        return $this->leafErrors;
    }

    /**
     * {@inheritDoc}
     */
    public function getLeafErrorsAt($path): array
    {
        return $this->leafErrors[$this->pathToString($path)] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function hasLeafErrorAt($path): bool
    {
        return isset($this->leafErrors[$this->pathToString($path)]);
    }

    private function addLeafError(ValidationError $error): void
    {
        $path = $this->pathToString($error->data()->fullPath());
        if (isset($this->leafErrors[$path])) {
            $this->leafErrors[$path][] = $error;
        } else {
            $this->leafErrors[$path] = [$error];
        }
    }

    private function isLeafError(ValidationError $error): bool
    {
        return [] === $error->subErrors();
    }

    /**
     * @param array<int|string>|string $path
     */
    private function pathToString($path): string
    {
        if (\is_array($path)) {
            return JsonPointer::pathToString($path);
        }

        return $path;
    }
}
