<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\KeywordValidators;

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;
use Systopia\OpisJsonSchemaExt\Expression\Calculation;
use Systopia\OpisJsonSchemaExt\Expression\Variables\CalculationVariable;
use Systopia\OpisJsonSchemaExt\Expression\Variables\Variable;
use Systopia\OpisJsonSchemaExt\Keywords\SetValueTrait;

final class CalculateKeywordValidator extends AbstractKeywordValidator
{
    use ErrorTrait;
    use SetValueTrait;

    private Calculation $calculation;

    public function __construct(Calculation $calculation)
    {
        $this->calculation = $calculation;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        $calculationVariable = new CalculationVariable($this->calculation);

        try {
            $value = $calculationVariable->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        } catch (VariableResolveException|ReferencedDataHasViolationException $e) {
            $value = null;
        }

        if (null === $value) {
            return $this->handleCalculationFailed($context);
        }

        $this->setValue($context, fn () => $value);

        return null === $this->next ? null : $this->next->validate($context);
    }

    private function handleCalculationFailed(ValidationContext $context): ?ValidationError
    {
        $schema = $context->schema();
        Assertion::notNull($schema);
        $this->unsetValue($context);
        if ($this->isRequired($context)) {
            // "required" is checked before calculation
            return $this->error(
                $schema,
                $context,
                '$calculate',
                'The property is required, but could not be calculated because of unresolvable variables',
            );
        }

        return null;
    }

    private function isRequired(ValidationContext $context): bool
    {
        $path = $context->currentDataPath();
        $key = end($path);
        $parentSchema = $this->getPropertiesSchema($context);

        return \in_array($key, $parentSchema->info()->data()->required ?? [], true);
    }

    private function getPropertiesSchema(ValidationContext $context): Schema
    {
        $loader = $context->loader();
        $schema = $context->schema();
        Assertion::notNull($schema);
        $path = $schema->info()->path();
        Assertion::inArray('properties', $path);
        foreach (array_reverse($path) as $key) {
            if ('properties' === $key) {
                break;
            }

            Assertion::notNull($schema->info()->base());
            $schema = $loader->loadSchemaById($schema->info()->base());
            Assertion::notNull($schema);
        }

        return $schema;
    }
}
