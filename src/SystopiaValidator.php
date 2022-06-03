<?php

declare(strict_types=1);

namespace Systopia\JsonSchema;

use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;
use Systopia\JsonSchema\Parsers\SystopiaSchemaParser;

/**
 * @codeCoverageIgnore
 */
class SystopiaValidator extends Validator
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [], int $maxErrors = 1)
    {
        $loader = new SchemaLoader(new SystopiaSchemaParser([], $options));
        parent::__construct($loader, $maxErrors);
    }
}
