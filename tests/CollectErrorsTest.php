<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Errors\ErrorCollector
 * @covers \Systopia\JsonSchema\KeywordValidators\CollectErrorsKeywordValidator
 * @covers \Systopia\JsonSchema\KeywordValidators\RootCollectErrorsKeywordValidator
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser
 */
final class CollectErrorsTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function test(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "parent": {
                        "type": "object",
                        "properties": {
                            "child1": { "type": "integer" },
                            "child2": { "type": "string", "minLength": 10 }
                        }
                    },
                    "string": { "type": "string" }
                }
            }
            JSON;

        $data = (object) [
            'parent' => (object) [
                'child1' => 1,
                'child2' => 'string',
            ],
            'string' => 2,
        ];

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];
        static::assertFalse($errorCollector->hasErrors());

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validator->validate($data, $schema, $globals);

        static::assertTrue($errorCollector->hasErrors());
        static::assertCount(4, $errorCollector->getErrors());
        static::assertTrue($errorCollector->hasErrorAt([]));
        static::assertTrue($errorCollector->hasErrorAt(['parent']));
        static::assertTrue($errorCollector->hasErrorAt(['parent', 'child2']));
        static::assertTrue($errorCollector->hasErrorAt('/parent/child2'));
        static::assertTrue($errorCollector->hasErrorAt(['string']));
        static::assertFalse($errorCollector->hasErrorAt(['parent', 'child1']));

        $expectedErrorKeys = [
            '/parent/child2',
            '/parent',
            '/string',
            '/',
        ];
        static::assertSame($expectedErrorKeys, array_keys($errorCollector->getErrors()));

        $stringErrors = $errorCollector->getErrorsAt(['string']);
        static::assertCount(1, $stringErrors);
        static::assertErrorKeyword('type', $stringErrors[0]);

        static::assertCount(2, $errorCollector->getLeafErrors());
        static::assertTrue($errorCollector->hasLeafErrorAt('/parent/child2'));
        static::assertFalse($errorCollector->hasLeafErrorAt('/parent'));
        static::assertTrue($errorCollector->hasLeafErrorAt(['string']));
        static::assertSame(['/parent/child2', '/string'], array_keys($errorCollector->getLeafErrors()));

        $child2Errors = $errorCollector->getLeafErrorsAt(['parent', 'child2']);
        static::assertCount(1, $child2Errors);
        static::assertErrorKeyword('minLength', $child2Errors[0]);
    }

    public function testMultipleViolations(): void
    {
        $schema = <<<'JSON'
            {
                "type": "string",
                "$validations": [
                    { "keyword": "minLength", "value": 10 },
                    { "keyword": "pattern", "value": "test" }
                ]
            }
            JSON;

        $errorCollector = new ErrorCollector();
        $globals = ['errorCollector' => $errorCollector];

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validator->validate('foo', $schema, $globals);

        static::assertCount(1, $errorCollector->getErrors());
        $stringErrors = $errorCollector->getErrorsAt('/');
        static::assertCount(3, $stringErrors);
        static::assertErrorKeyword('minLength', $stringErrors[0]);
        static::assertErrorKeyword('pattern', $stringErrors[1]);
        static::assertErrorKeyword('$validations', $stringErrors[2]);

        static::assertCount(1, $errorCollector->getLeafErrors());
        $leafErrors = $errorCollector->getLeafErrorsAt('/');
        static::assertCount(2, $leafErrors);
        static::assertErrorKeyword('minLength', $leafErrors[0]);
        static::assertErrorKeyword('pattern', $leafErrors[1]);
    }
}
