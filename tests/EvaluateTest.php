<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Parsers\DefaultVocabulary;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Systopia\OpisJsonSchemaExt\Expression\SymfonyExpressionHandler;
use Systopia\OpisJsonSchemaExt\Parsers\Keywords\EvaluateKeywordParser;
use Systopia\OpisJsonSchemaExt\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser;
use Systopia\OpisJsonSchemaExt\SystopiaValidator;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Keywords\EvaluateKeyword
 * @covers \Systopia\OpisJsonSchemaExt\Parsers\Keywords\EvaluateKeywordParser
 */
final class EvaluateTest extends TestCase
{
    use AssertValidationErrorTrait;

    public function testSimpleEvaluation(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "evaluated": {
                        "type": "integer",
                        "evaluate": "data > 10"
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['evaluated' => 11], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('evaluate', $error);
        self::assertFormattedErrorMessage('Evaluation of "data > 10" failed', $error);
    }

    public function testEvaluationWithVariable(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data > a",
                            "variables": { "a": 10 }
                        }
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['evaluated' => 11], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('evaluate', $error);
        self::assertFormattedErrorMessage('Evaluation of "data > a" failed', $error);
    }

    public function testEvaluationWithReferencedVariable(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data >= a",
                            "variables": { "a": { "$data": "/a" } }
                        }
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['a' => 3, 'evaluated' => 3], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['a' => 4, 'evaluated' => 3], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('evaluate', $error);
        self::assertFormattedErrorMessage('Evaluation of "data >= a" failed', $error);
    }

    public function testEvaluationWithCalculatedVariable(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data > a",
                            "variables": { "a": { "$calculate": "2 * 5" } }
                        }
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['evaluated' => 11], $schema);
        static::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('evaluate', $error);
        self::assertFormattedErrorMessage('Evaluation of "data > a" failed', $error);
    }

    public function testEvaluationIfVariableNotSet(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data >= a",
                            "variables": { "a": { "$data": "/a" } }
                        }
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validationResult = $validator->validate((object) ['evaluated' => 3], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('evaluate', $error);
        self::assertFormattedErrorMessage(
            'Evaluation of "data >= a" failed: Not all variables could be resolved',
            $error
        );
    }

    public function testNoEvaluationIfVariableHasViolation(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data >= a",
                            "variables": { "a": { "$data": "/a" } }
                        }
                    }
                }
            }
            JSON;

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validationResult = $validator->validate((object) ['a' => null, 'evaluated' => 3], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('type', $error);
    }

    /**
     * Note: As of now (opis/json-schema v2.3.0) no property validations are
     * performed at all if "required" fails.
     */
    public function testNoEvaluationIfVariableHasViolation2(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "a": { "type": "integer" },
                    "evaluated": {
                        "type": "integer",
                        "evaluate": {
                            "expression": "data >= a",
                            "variables": { "a": { "$data": "/a" } }
                        }
                    }
                },
                "required": ["a"]
            }
            JSON;

        $validator = new SystopiaValidator();
        $validator->setMaxErrors(2);
        $validationResult = $validator->validate((object) ['evaluated' => 3], $schema);
        static::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(0, $validationResult->error());
        self::assertErrorKeyword('required', $validationResult->error());
    }

    public function testNoEvaluationWithoutEvaluator(): void
    {
        $expressionHandler = new SymfonyExpressionHandler();
        $options = [
            'calculator' => $expressionHandler,
        ];
        $vocabulary = new DefaultVocabulary(
            [new EvaluateKeywordParser()],
            [new CollectErrorsKeywordValidatorParser()]
        );
        $parser = new SchemaParser([], $options, $vocabulary);
        $loader = new SchemaLoader($parser);
        $validator = new Validator($loader);

        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "evaluated": {
                        "type": "integer",
                        "evaluate": "data > 2"
                    }
                }
            }
            JSON;

        $validationResult = $validator->validate((object) ['evaluated' => 2], $schema);
        static::assertTrue($validationResult->isValid());
    }

    public function testInvalidExpression(): void
    {
        $schema = <<<'JSON'
            {
                "type": "object",
                "properties": {
                    "evaluated": {
                        "type": "integer",
                        "evaluate": "data > a"
                    }
                }
            }
            JSON;

        $this->expectException(InvalidKeywordException::class);
        $validator = new SystopiaValidator();
        $validator->validate((object) ['evaluated' => 11], $schema);
    }
}
