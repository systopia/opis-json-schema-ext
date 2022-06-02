<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression\Variables;

use Opis\JsonSchema\ValidationContext;

final class IdentityVariable extends Variable
{
    /**
     * @var null|mixed
     */
    private $value;

    /**
     * @param null|mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ValidationContext $context, int $flags = 0)
    {
        return $this->value;
    }
}
