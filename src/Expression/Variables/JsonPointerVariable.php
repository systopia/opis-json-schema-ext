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

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;

final class JsonPointerVariable extends Variable
{
    private JsonPointer $pointer;

    private Variable $fallback;

    public function __construct(JsonPointer $pointer, ?Variable $fallback = null)
    {
        $this->pointer = $pointer;
        $this->fallback = null === $fallback ? new IdentityVariable($fallback) : $fallback;
    }

    public static function isAllowed(SchemaParser $parser): bool
    {
        return true === $parser->option('allowDataKeyword');
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        if (!self::isAllowed($parser)) {
            throw new ParseException('keyword "$data" is not allowed');
        }

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }
        $fallback = null === ($data->fallback ?? null) ? null : Variable::create($data->fallback, $parser);

        if (!property_exists($data, '$data')) {
            throw new ParseException('keyword "$data" is required');
        }

        $pointer = JsonPointer::parse($data->{'$data'});
        if (null === $pointer) {
            throw new ParseException(sprintf('Invalid JSON pointer "%s"', $data->{'$data'}));
        }

        return new self($pointer, $fallback);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ValidationContext $context, int $flags = 0)
    {
        if (0 !== ($flags & self::FLAG_FAIL_ON_VIOLATION)) {
            $path = $this->pointer->absolutePath($context->fullDataPath());
            Assertion::notNull($path);

            if (ErrorCollectorUtil::getErrorCollector($context)->hasErrorAt($path)) {
                throw new ReferencedDataHasViolationException(
                    sprintf('The property at path "%s" has violations', JsonPointer::pathToString($path))
                );
            }
        }

        $value = $this->pointer->data($context->rootData(), $context->currentDataPath())
            ?? $this->fallback->getValue($context, $flags);
        if (null === $value && 0 !== ($flags & Variable::FLAG_FAIL_ON_UNRESOLVED)) {
            $path = $this->pointer->absolutePath($context->fullDataPath());
            Assertion::notNull($path);

            throw new VariableResolveException(
                sprintf('The property at path "%s" could not be resolved', JsonPointer::pathToString($path))
            );
        }

        return $value;
    }
}
