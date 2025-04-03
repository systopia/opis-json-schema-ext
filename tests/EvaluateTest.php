<?php

/*
 * Copyright 2022 SYSTOPIA GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Parsers\DefaultVocabulary;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;
use Systopia\JsonSchema\Parsers\Keywords\EvaluateKeywordParser;
use Systopia\JsonSchema\Parsers\KeywordValidators\CollectErrorsKeywordValidatorParser;
use Systopia\JsonSchema\SystopiaValidator;

/**
 * @covers \Systopia\JsonSchema\Keywords\EvaluateKeyword
 * @covers \Systopia\JsonSchema\Parsers\Keywords\EvaluateKeywordParser
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
        self::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        self::assertNotNull($validationResult->error());
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
        self::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        self::assertNotNull($validationResult->error());
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
        self::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['a' => 4, 'evaluated' => 3], $schema);
        self::assertNotNull($validationResult->error());
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
        self::assertTrue($validationResult->isValid());

        $validationResult = $validator->validate((object) ['evaluated' => 10], $schema);
        self::assertNotNull($validationResult->error());
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
        self::assertNotNull($validationResult->error());
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
        self::assertNotNull($validationResult->error());
        self::assertSubErrorsCount(1, $validationResult->error());
        $error = $validationResult->error()->subErrors()[0];
        self::assertErrorKeyword('type', $error);
    }

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
        self::assertNotNull($validationResult->error());
        self::assertErrorKeyword('', $validationResult->error());
        $subErrors = $validationResult->error()->subErrors();
        self::assertCount(2, $subErrors);
        self::assertErrorKeyword('required', $subErrors[0]);
        self::assertErrorKeyword('properties', $subErrors[1]);
        $propertiesErrors = $subErrors[1]->subErrors();
        self::assertCount(1, $propertiesErrors);
        self::assertErrorKeyword('evaluate', $propertiesErrors[0]);
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
        self::assertTrue($validationResult->isValid());
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
