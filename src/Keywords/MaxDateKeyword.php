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
use Opis\JsonSchema\Formats\DateTimeFormats;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Exceptions\VariableResolveException;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Translation\ErrorTranslator;

final class MaxDateKeyword implements Keyword
{
    use ErrorTrait;

    private Variable $maxDateVariable;

    public function __construct(Variable $maxDateVariable)
    {
        $this->maxDateVariable = $maxDateVariable;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        try {
            $maxDate = $this->maxDateVariable->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED
            );
        } catch (VariableResolveException $e) {
            return $this->error($schema, $context, 'maxDate', 'Failed to resolve {keyword}', [
                'keyword' => 'maxDate',
                'message' => $e->getMessage(),
                ErrorTranslator::TRANSLATION_ID_ARG_KEY => '_resolveFailed',
            ]);
        }

        if (!\is_string($maxDate) || 1 !== preg_match(DateTimeFormats::DATE_REGEX, $maxDate)) {
            return $this->error($schema, $context, 'maxDate', 'Invalid maxDate {maxDate}', [
                'maxDate' => $maxDate,
            ]);
        }

        $data = $context->currentData();
        if ($data <= $maxDate) {
            return null;
        }

        return $this->error($schema, $context, 'maxDate', 'Date must not be after {maxDate}', [
            'maxDate' => $maxDate,
            'maxDateTimestamp' => strtotime($maxDate),
        ]);
    }
}
