<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test\Errors;

use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\OpisJsonSchemaExt\Errors\ErrorCollector;
use Systopia\OpisJsonSchemaExt\Errors\ErrorCollectorUtil;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Errors\ErrorCollectorUtil
 */
final class ErrorCollectorUtilTest extends TestCase
{
    public function testGetErrorCollector(): void
    {
        $schemaLoader = new SchemaLoader();
        $errorCollector = new ErrorCollector();
        $context = new ValidationContext(
            '',
            $schemaLoader,
            null,
            null,
            ['errorCollector' => $errorCollector]
        );
        static::assertSame($errorCollector, ErrorCollectorUtil::getErrorCollector($context));
    }
}
