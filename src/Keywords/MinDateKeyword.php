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

final class MinDateKeyword implements Keyword
{
    use ErrorTrait;

    private Variable $minDateVariable;

    public function __construct(Variable $minDateVariable)
    {
        $this->minDateVariable = $minDateVariable;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        try {
            $minDate = $this->minDateVariable->getValue(
                $context,
                Variable::FLAG_FAIL_ON_UNRESOLVED
            );
        } catch (VariableResolveException $e) {
            return $this->error($schema, $context, 'minDate', 'Failed to resolve {keyword}', [
                'keyword' => 'minDate',
                'message' => $e->getMessage(),
                ErrorTranslator::TRANSLATION_ID_ARG_KEY => '_resolveFailed',
            ]);
        }

        if (!\is_string($minDate) || 1 !== preg_match(DateTimeFormats::DATE_REGEX, $minDate)) {
            return $this->error($schema, $context, 'minDate', 'Invalid minDate {minDate}', [
                'minDate' => $minDate,
                ErrorTranslator::TRANSLATION_ID_ARG_KEY => '_invalidKeywordValue',
                'value' => $minDate,
            ]);
        }

        $data = $context->currentData();
        if ($data >= $minDate) {
            return null;
        }

        return $this->error($schema, $context, 'minDate', 'Date must not be before {minDate}', [
            'minDate' => $minDate,
            'minDateTimestamp' => strtotime($minDate),
        ]);
    }
}
