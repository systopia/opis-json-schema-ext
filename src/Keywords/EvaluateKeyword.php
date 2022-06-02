<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;
use Systopia\OpisJsonSchemaExt\Expression\Evaluation;
use Systopia\OpisJsonSchemaExt\Expression\EvaluatorInterface;
use Systopia\OpisJsonSchemaExt\Expression\Variables\Variable;

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
                ['expression' => $this->evaluation->getExpression()]
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
