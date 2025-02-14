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

namespace Systopia\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Evaluation;
use Systopia\JsonSchema\Expression\EvaluatorInterface;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Translation\ErrorTranslator;

final class EvaluateKeyword implements Keyword
{
    use ErrorTrait;

    private EvaluatorInterface $evaluator;

    private Evaluation $evaluation;

    public function __construct(
        EvaluatorInterface $evaluator,
        Evaluation $evaluation
    ) {
        $this->evaluator = $evaluator;
        $this->evaluation = $evaluation;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        try {
            $variables = $this->evaluation->getVariables(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        } catch (ReferencedDataHasViolationException $e) {
            return null;
        } catch (VariableResolveException $e) {
            $variables = ['$' => null];
        }

        if (\in_array(null, $variables, true)) {
            return $this->error(
                $schema,
                $context,
                'evaluate',
                'Evaluation of "{expression}" failed: Not all variables could be resolved',
                [
                    'expression' => $this->evaluation->getExpression(),
                    ErrorTranslator::TRANSLATION_ID_ARG_KEY => 'evaluate.resolve',
                ]
            );
        }

        if (!$this->evaluator->evaluate(
            $this->evaluation->getExpression(),
            ['data' => $context->currentData()] + $variables
        )) {
            return $this->error(
                $schema,
                $context,
                'evaluate',
                'Evaluation of "{expression}" failed',
                ['expression' => $this->evaluation->getExpression()]
            );
        }

        return null;
    }
}
