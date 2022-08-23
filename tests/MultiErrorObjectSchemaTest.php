<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * Tests that multiple errors on the same level are possible.
 *
 * @covers \Systopia\JsonSchema\Schemas\MultiErrorObjectSchema
 */
final class MultiErrorObjectSchemaTest extends TestCase
{
    public function test(): void
    {
        $validator = new SystopiaValidator([], 20);
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
        static::assertNotNull($rootError);
        static::assertSame('schema', $rootError->keyword());
        static::assertSame('The data must match schema: {data}', $rootError->message());
        static::assertSame($data, $rootError->args()['data']);
        static::assertSame([], $rootError->data()->fullPath());

        $subErrors = $rootError->subErrors();
        static::assertCount(2, $subErrors);
        static::assertSame('required', $subErrors[0]->keyword());
        static::assertSame('properties', $subErrors[1]->keyword());
    }
}
