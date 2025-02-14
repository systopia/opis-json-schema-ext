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

namespace Systopia\JsonSchema\Parsers\Keywords;

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\ExpressionVariablesContainer;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\ValidationsKeyword;
use Systopia\JsonSchema\Parsers\EnsurePropertyTrait;

final class ValidationsKeywordParser extends KeywordParser
{
    use EnsurePropertyTrait;

    public function __construct(string $keyword = '$validations')
    {
        parent::__construct($keyword);
    }

    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    /**
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        $validations = [];
        foreach ($this->keywordValue($info) as $validation) {
            $this->assertPropertyExists($validation, 'keyword', $info);
            $this->assertPropertyExists($validation, 'value', $info);

            $validation = Helper::cloneValue($validation);
            $validation->value = $this->createValidationValue($validation->value, $parser);
            $validations[] = $validation;
        }

        return new ValidationsKeyword($validations);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws ParseException
     */
    private function createValidationValue($value, SchemaParser $parser)
    {
        if (!$value instanceof \stdClass) {
            Assertion::notNull($value);

            return $value;
        }

        if (property_exists($value, '$calculate') || property_exists($value, '$data')) {
            return Variable::create($value, $parser);
        }

        return ExpressionVariablesContainer::parse($value, $parser);
    }
}
