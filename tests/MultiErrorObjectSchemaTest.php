<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * Tests that multiple errors on the same depth are possible.
 *
 * @covers \Systopia\JsonSchema\Schemas\MultiErrorObjectSchema
 */
final class MultiErrorObjectSchemaTest extends TestCase
{
    public function test(): void
    {
        $validator = new SystopiaValidator();
        $data = (object) [
            'a' => 1,
        ];

        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'a' => (object) ['type' => 'string'],
                'b' => (object) ['type' => 'string'],
            ],
            'required' => ['a', 'b'],
        ];
        $result = $validator->validate($data, $schema);

        $rootError = $result->error();
        self::assertNotNull($rootError);
        self::assertSame('', $rootError->keyword());
        self::assertSame('Data must match schema', $rootError->message());
        self::assertSame([], $rootError->args());
        self::assertSame([], $rootError->data()->fullPath());

        $subErrors = $rootError->subErrors();
        self::assertCount(2, $subErrors);
        self::assertSame('required', $subErrors[0]->keyword());
        self::assertSame('properties', $subErrors[1]->keyword());
    }
}
