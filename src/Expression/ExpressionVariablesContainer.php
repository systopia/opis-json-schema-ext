<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;
use Systopia\OpisJsonSchemaExt\Expression\Variables\Variable;

final class ExpressionVariablesContainer
{
    /**
     * @var array<string, Variable>
     */
    private array $variables;

    /**
     * @param array<string, Variable> $variables
     */
    private function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        $variables = [];
        // @phpstan-ignore-next-line
        foreach ($data as $name => $variable) {
            $variables[$name] = Variable::create($variable, $parser);
        }

        return new self($variables);
    }

    /**
     * @throws ReferencedDataHasViolationException|VariableResolveException
     *
     * @return array<string, mixed>
     */
    public function getValues(ValidationContext $context, int $flags = 0): array
    {
        return array_map(
            fn (Variable $variable) => $variable->getValue($context, $flags),
            $this->variables
        );
    }

    /**
     * @return array<string, Variable>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->variables);
    }
}
