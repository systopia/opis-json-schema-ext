<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test\Expression\Variables;

use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\OpisJsonSchemaExt\Expression\Variables\IdentityVariable;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Expression\Variables\IdentityVariable
 */
final class IdentityVariableTest extends TestCase
{
    public function testGetValue(): void
    {
        $variable = new IdentityVariable('test');
        $context = new ValidationContext('', new SchemaLoader());
        static::assertSame('test', $variable->getValue($context));
    }
}
