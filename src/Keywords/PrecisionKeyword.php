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

namespace Systopia\JsonSchema\Keywords;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Translation\ErrorTranslator;

final class PrecisionKeyword implements Keyword
{
    use ErrorTrait;

    private Variable $precisionVariable;

    public function __construct(Variable $precisionVariable)
    {
        $this->precisionVariable = $precisionVariable;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $data = $context->currentData();
        if (\is_int($data)) {
            return null;
        }

        if (!\is_float($data)) {
            // Keyword "type" (dependency of this keyword) will return an error, so we don't need to do it here.
            return null;
        }

        try {
            $precision = $this->precisionVariable->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED | Variable::FLAG_FAIL_ON_VIOLATION
            );
        } catch (ReferencedDataHasViolationException|VariableResolveException $e) {
            return $this->error($schema, $context, 'precision', 'Failed to resolve {keyword}', [
                'keyword' => 'precision',
                'message' => $e->getMessage(),
                ErrorTranslator::TRANSLATION_ID_ARG_KEY => '_resolveFailed',
            ]);
        }

        if (!\is_int($precision)) {
            return $this->error($schema, $context, 'precision', 'Invalid precision (got value of type {type})', [
                'precision' => $precision,
                'type' => \gettype($precision),
                ErrorTranslator::TRANSLATION_ID_ARG_KEY => '_invalidKeywordValue',
                'value' => $precision,
            ]);
        }

        $pattern = sprintf('/^-?\d+(\.\d{0,%d})?$/', $precision);

        return 1 !== preg_match($pattern, (string) $data) ? $this->error(
            $schema,
            $context,
            'precision',
            'The number must not have more than {precision} decimal places',
            ['precision' => $precision]
        ) : null;
    }
}
