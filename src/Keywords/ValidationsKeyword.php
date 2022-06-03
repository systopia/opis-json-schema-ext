<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Keywords;

use Assert\Assertion;
use Opis\JsonSchema\Errors\ErrorContainer;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\ExpressionVariablesContainer;
use Systopia\JsonSchema\Expression\Variables\Variable;

final class ValidationsKeyword implements Keyword
{
    use ErrorTrait;

    /**
     * @var \stdClass[]
     */
    private array $validations;

    /**
     * @param \stdClass[] $validations
     */
    public function __construct(array $validations)
    {
        $this->validations = $validations;
    }

    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $errors = new ErrorContainer($context->maxErrors());
        foreach ($this->validations as $validation) {
            $validationSchema = $this->createValidationSchema($context, $schema->info(), $validation);
            if (null !== $error = $validationSchema->validate($context)) {
                $errors->add($this->createError($validationSchema, $context, $validation, $error));
                if ($errors->isFull()) {
                    break;
                }
            }
        }

        if (!$errors->isEmpty()) {
            return $this->error(
                $schema,
                $context,
                '$validations',
                'The property must match validations',
                [],
                $errors,
            );
        }

        return null;
    }

    private function createValidationSchema(
        ValidationContext $context,
        SchemaInfo $info,
        \stdClass $validation
    ): Schema {
        return $context->loader()->loadObjectSchema(
            $this->createValidationSchemaData($context, $validation),
            null,
            $info->draft()
        );
    }

    private function createValidationSchemaData(ValidationContext $context, \stdClass $validation): object
    {
        try {
            $value = $this->getValidationValue($context, $validation->value);
        } catch (VariableResolveException|ReferencedDataHasViolationException $e) {
            $value = null;
        }

        if (null === $value || (\is_array($value) && \in_array(null, $value, true))) {
            // No validation if variable could not be resolved or has violation
            return (object) [];
        }

        return (object) [
            $validation->keyword => $value,
        ];
    }

    /**
     * @param mixed $value
     *
     * @throws ReferencedDataHasViolationException|VariableResolveException
     *
     * @return mixed
     */
    private function getValidationValue(ValidationContext $context, $value)
    {
        if ($value instanceof Variable) {
            return $value->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        }

        if ($value instanceof ExpressionVariablesContainer) {
            return (object) $value->getValues(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        }

        return $value;
    }

    private function createError(
        Schema $validationSchema,
        ValidationContext $context,
        \stdClass $validation,
        ValidationError $error
    ): ValidationError {
        if (!property_exists($validation, 'message')) {
            return $error;
        }

        // @phpstan-ignore-next-line
        $validationValue = $validationSchema->info()->data()->{$validation->keyword};
        if ($validationValue instanceof \stdClass) {
            $args = $this->getLeafProperties($validationValue);
        } else {
            $args = [$validation->keyword => $validationValue];
        }

        return $this->error($validationSchema, $context, $validation->keyword, $validation->message, $args);
    }

    /**
     * @return array<string, null|scalar|scalar[]>
     */
    private function getLeafProperties(\stdClass $properties): array
    {
        $leafProperties = [];
        // @phpstan-ignore-next-line
        foreach ($properties as $name => $value) {
            if ($value instanceof \stdClass) {
                $leafProperties += $this->getLeafProperties($value);
            } else {
                Assertion::string($name);
                Assertion::nullOrScalar($value);
                $leafProperties[$name] = $value;
            }
        }

        return $leafProperties;
    }
}
