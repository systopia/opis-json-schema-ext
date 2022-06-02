<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\KeywordValidators;

use Assert\Assertion;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Keywords\SetValueTrait;

/**
 * Ensures that calculated properties exists so that the calculation will be
 * evaluated.
 */
final class CalculateInitKeywordValidator extends AbstractKeywordValidator
{
    use SetValueTrait;

    /**
     * @var string[]
     */
    private array $calculatedProperties;

    /**
     * @param string[] $calculatedProperties
     */
    public function __construct(array $calculatedProperties)
    {
        $this->calculatedProperties = $calculatedProperties;
    }

    public function validate(ValidationContext $context): ?ValidationError
    {
        $data = $context->currentData();
        foreach ($this->calculatedProperties as $property) {
            if (!property_exists($data, $property)) {
                $context->pushDataPath($property);
                $this->setValue($context, fn () => '$calculated');
                $context->popDataPath();
            }
        }

        Assertion::notNull($this->next);

        return $this->next->validate($context);
    }
}
