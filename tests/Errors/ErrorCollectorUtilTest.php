<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Errors;

use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;

/**
 * @covers \Systopia\JsonSchema\Errors\ErrorCollectorUtil
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
