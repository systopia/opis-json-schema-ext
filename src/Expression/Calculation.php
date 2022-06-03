<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Expression;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;

final class Calculation
{
    private string $expression;

    private ExpressionVariablesContainer $variablesContainer;

    /**
     * @var null|mixed
     */
    private $fallback;

    /**
     * @param null|mixed $fallback
     */
    private function __construct(
        string $expression,
        ExpressionVariablesContainer $variablesContainer = null,
        $fallback = null
    ) {
        $this->expression = $expression;
        $this->variablesContainer = $variablesContainer ?? ExpressionVariablesContainer::createEmpty();
        $this->fallback = $fallback;
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

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }

        return new self(
            $data->expression,
            ExpressionVariablesContainer::parse($data->variables ?? (object) [], $parser),
            $data->fallback ?? null
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

    /**
     * @return null|mixed
     */
    public function getFallback()
    {
        return $this->fallback;
    }
}
