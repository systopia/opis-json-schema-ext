<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;

final class Evaluation
{
    private string $expression;

    private ExpressionVariablesContainer $variablesContainer;

    private function __construct(
        string $expression,
        ExpressionVariablesContainer $variablesContainer = null
    ) {
        $this->expression = $expression;
        $this->variablesContainer = $variablesContainer ?? ExpressionVariablesContainer::createEmpty();
    }

    /**
     * @param \stdClass|string $data
     *
     * @throws ParseException
     */
    public static function parse($data, SchemaParser $parser): self
    {
        if (\is_string($data)) {
            return new self($data);
        }

        if (!$data instanceof \stdClass || !property_exists($data, 'expression')) {
            throw new ParseException('string or an object containing the property "expression" expected');
        }

        return new self(
            $data->expression,
            ExpressionVariablesContainer::parse($data->variables ?? (object) [], $parser),
        );
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @throws ReferencedDataHasViolationException|VariableResolveException
     *
     * @return array<string, mixed>
     */
    public function getVariables(ValidationContext $context, int $flags = 0): array
    {
        return $this->variablesContainer->getValues($context, $flags);
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return $this->variablesContainer->getNames();
    }
}
