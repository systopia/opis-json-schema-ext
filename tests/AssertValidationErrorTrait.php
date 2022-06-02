<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use PHPUnit\Framework\Assert;

trait AssertValidationErrorTrait
{
    public static function assertSubErrorsCount(int $expectedCount, ValidationError $error): void
    {
        Assert::assertCount($expectedCount, $error->subErrors());
    }

    public static function assertErrorKeyword(string $expected, ValidationError $error): void
    {
        Assert::assertSame($expected, $error->keyword());
    }

    public static function assertFormattedErrorMessage(string $expected, ValidationError $error): void
    {
        $formatter = new ErrorFormatter();
        Assert::assertSame($expected, $formatter->formatErrorMessage($error));
    }
}
