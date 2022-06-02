<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Errors;

use Opis\JsonSchema\Errors\ValidationError;

interface ErrorCollectorInterface
{
    public function addError(ValidationError $error): void;

    /**
     * @return array<string, ValidationError[]>
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
     * @return array<string, ValidationError[]>
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
