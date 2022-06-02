<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Parsers;

use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\OpisJsonSchemaExt\Expression\SymfonyExpressionHandler;

/**
 * @codeCoverageIgnore
 */
class SystopiaSchemaParser extends SchemaParser
{
    /**
     * {@inheritDoc}
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(array $resolvers = [], array $options = [], ?SystopiaVocabulary $vocabulary = null)
    {
        parent::__construct($resolvers, $this->buildOptions($options), $vocabulary ?? new SystopiaVocabulary());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function buildOptions(array $options): array
    {
        if (!isset($options['calculator']) || !isset($options['evaluator'])) {
            if (SymfonyExpressionHandler::isAvailable()) {
                $expressionHandler = new SymfonyExpressionHandler();
                $options['calculator'] ??= $expressionHandler;
                $options['evaluator'] ??= $expressionHandler;
            }
        }

        return $options;
    }
}
