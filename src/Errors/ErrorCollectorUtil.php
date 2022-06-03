<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Errors;

use Opis\JsonSchema\ValidationContext;

final class ErrorCollectorUtil
{
    public static function getErrorCollector(ValidationContext $context): ErrorCollectorInterface
    {
        return $context->globals()['errorCollector'];
    }
}
