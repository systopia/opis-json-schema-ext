<?php

/*
 * Copyright 2025 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Parsers\KeywordValidators;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\KeywordValidators\ApplyLimitValidationKeywordValidator;
use Systopia\JsonSchema\KeywordValidators\LimitValidationKeywordValidator;
use Systopia\JsonSchema\LimitValidation\LimitValidationRule;

final class LimitValidationKeywordParser extends KeywordValidatorParser
{
    /**
     * @var array<string, array<string, LimitValidationKeywordValidator>>
     */
    private array $validators = [];

    /**
     * @var null|list<LimitValidationRule>
     */
    private ?array $standardRules = null;

    public function __construct()
    {
        parent::__construct('$limitValidation');
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator
    {
        if (!$this->keywordExists($info)) {
            $currentValidator = LimitValidationKeywordValidator::getCurrentInstance();

            return (bool) $currentValidator?->isConditionMatched() ? new ApplyLimitValidationKeywordValidator($currentValidator->getRules()) : null;
        }

        $limitValidation = $this->keywordValue($info);
        if (!$limitValidation instanceof \stdClass && !\is_bool($limitValidation)) {
            throw $this->keywordException('{keyword} must contain an object or a boolean', $info);
        }

        if (\is_bool($limitValidation)) {
            $condition = $limitValidation;
        } elseif (property_exists($limitValidation, 'condition')) {
            $condition = $limitValidation->condition;
            if (!$condition instanceof \stdClass && !\is_bool($condition)) {
                throw $this->keywordException('{keyword} must contain a JSON schema (object or a boolean) at "condition" ', $info);
            }
        } elseif (null !== LimitValidationKeywordValidator::getCurrentInstance()) {
            $condition = LimitValidationKeywordValidator::getCurrentInstance()->isConditionMatched();
        } else {
            $condition = false;
        }

        if (!\is_array($limitValidation->rules ?? [])) {
            throw $this->keywordException('{keyword} must contain an array at "rules" ', $info);
        }

        $rules = array_merge(
            array_map(
                function ($rule) use ($info) {
                    if (!$rule instanceof \stdClass) {
                        throw $this->keywordException('{keyword} must contain an array of objects at "rules" ', $info);
                    }

                    $this->assertRule($rule, $info);

                    return LimitValidationRule::create((array) $rule);
                },
                $limitValidation->rules ?? []
            ),
            $this->getStandardRules($parser)
        );
        $schema = $limitValidation->schema ?? true;

        return new LimitValidationKeywordValidator($condition, $rules, $schema);
    }

    /**
     * @return list<LimitValidationRule>
     */
    private function getStandardRules(SchemaParser $parser): array
    {
        if (null !== $this->standardRules) {
            return $this->standardRules;
        }

        $this->standardRules[] = LimitValidationRule::create(
            ['value' => (object) ['const' => null]]
        );
        $this->standardRules[] = LimitValidationRule::create([
            'keyword' => (object) ['not' => (object) ['const' => 'type']],
            'value' => (object) ['enum' => [false, '']],
        ]);
        $this->standardRules[] = LimitValidationRule::create([
            'keyword' => (object) [
                'enum' => [
                    'minLength',
                    'minItems',
                    'minContains',
                    'minProperties',
                    'required',
                    'dependentRequired',
                ],
            ],
        ]);
        $this->standardRules[] = LimitValidationRule::create([
            'calculatedValueUsedViolatedData' => true,
        ]);
        $this->standardRules[] = LimitValidationRule::create(
            ['validate' => true]
        );

        return $this->standardRules;
    }

    private function assertRule(\stdClass $rule, SchemaInfo $info): void
    {
        $this->assertRuleProperty($rule, 'keyword', $info);
        $this->assertRuleProperty($rule, 'keywordValue', $info);
        $this->assertRuleProperty($rule, 'value', $info);
        if (property_exists($rule, 'validate') && !\is_bool($rule->validate)) {
            throw $this->keywordException('Property "validate" of a {keyword} rule must be a boolean', $info);
        }
    }

    private function assertRuleProperty(\stdClass $rule, string $keyword, SchemaInfo $info): void
    {
        if (property_exists($rule, $keyword) && !self::isJsonSchema($rule->{$keyword})) {
            throw $this->keywordException(
                \sprintf('Property "%s" of a {keyword} rule must be a JSON schema (boolean or object)', $keyword),
                $info
            );
        }
    }

    private static function isJsonSchema(mixed $value): bool
    {
        return \is_bool($value) || $value instanceof \stdClass;
    }
}
