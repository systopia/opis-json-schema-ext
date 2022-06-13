<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Variables\Variable;

final class PrecisionKeyword implements Keyword
{
    use ErrorTrait;

    private Variable $precisionVariable;

    public function __construct(Variable $precisionVariable)
    {
        $this->precisionVariable = $precisionVariable;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        if (\is_int($data)) {
            return null;
        }

        try {
            $precision = $this->precisionVariable->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        } catch (ReferencedDataHasViolationException|VariableResolveException $e) {
            return $this->error($schema, $context, 'precision', 'Failed to resolve precision');
        }

        if (!\is_int($precision)) {
            return $this->error($schema, $context, 'precision', 'Invalid precision (got value of type {type})', [
                'type' => \gettype($precision),
            ]);
        }

        if (!\is_float($data)) {
            // Keyword "type" will return an error, so we don't need to do it here
            return null;
        }

        $pattern = sprintf('/^-?\d+\.\d{0,%d}$/', $precision);

        return 1 !== preg_match($pattern, (string) $data) ? $this->error(
            $schema,
            $context,
            'precision',
            'The number must not have more than {precision} decimal places',
            ['precision' => $precision]
        ) : null;
    }
}
