<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression\Variables;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;

abstract class Variable
{
    public const FLAG_FAIL_ON_UNRESOLVED = 1;

    public const FLAG_FAIL_ON_VIOLATION = 2;

    /**
     * @param mixed $data
     *
     * @throws ParseException
     */
    public static function create($data, SchemaParser $parser): self
    {
        if (null === $data) {
            throw new ParseException('null is not allowed as variable');
        }

        if (!$data instanceof \stdClass) {
            return new IdentityVariable($data);
        }

        if (property_exists($data, '$data')) {
            return JsonPointerVariable::parse($data, $parser);
        }

        if (property_exists($data, '$calculate')) {
            return CalculationVariable::parse($data, $parser);
        }

        return new IdentityVariable($data);
    }

    /**
     * @throws ReferencedDataHasViolationException|VariableResolveException
     *
     * @return null|mixed
     */
    abstract public function getValue(ValidationContext $context, int $flags = 0);
}
