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

declare(strict_types=1);

namespace Systopia\JsonSchema\Parsers\Keywords;

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\Evaluation;
use Systopia\JsonSchema\Expression\EvaluatorInterface;
use Systopia\JsonSchema\Keywords\EvaluateKeyword;

final class EvaluateKeywordParser extends KeywordParser
{
    public function __construct(string $keyword = 'evaluate')
    {
        parent::__construct($keyword);
    }

    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    /**
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?EvaluateKeyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        if (null === $evaluator = $parser->option('evaluator')) {
            return null;
        }
        Assertion::isInstanceOf($evaluator, EvaluatorInterface::class);

        $value = $this->keywordValue($info);
        $evaluation = Evaluation::parse($value, $parser);

        try {
            $evaluator->validateEvaluationExpression(
                $evaluation->getExpression(),
                array_merge(['data'], $evaluation->getVariableNames())
            );
        } catch (\Exception $e) {
            throw new InvalidKeywordException(
                sprintf('Validating evaluation expression failed: %s', $e->getMessage()),
                $this->keyword,
                $info
            );
        }

        return new EvaluateKeyword($evaluator, $evaluation);
    }
}
