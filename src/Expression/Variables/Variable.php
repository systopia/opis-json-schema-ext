<?php

/*
 * Copyright 2022 SYSTOPIA GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

declare(strict_types=1);

namespace Systopia\JsonSchema\Expression\Variables;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;

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
     * @param bool $violated Will be set to true, if false is given and the
     *                       variable has violated data
     *
     * @return null|mixed
     *
     * @throws ReferencedDataHasViolationException|VariableResolveException
     */
    abstract public function getValue(ValidationContext $context, int $flags = 0, ?bool &$violated = null);
}
