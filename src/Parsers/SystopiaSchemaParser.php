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

namespace Systopia\JsonSchema\Parsers;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Schemas\EmptySchema;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;
use Systopia\JsonSchema\Schemas\MultiErrorObjectSchema;

/**
 * @codeCoverageIgnore
 */
class SystopiaSchemaParser extends SchemaParser
{
    /**
     * {@inheritDoc}
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(array $resolvers = [], array $options = [], ?SystopiaVocabulary $vocabulary = null)
    {
        parent::__construct($resolvers, $this->buildOptions($options), $vocabulary ?? new SystopiaVocabulary());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function buildOptions(array $options): array
    {
        if (!isset($options['calculator']) || !isset($options['evaluator'])) {
            if (SymfonyExpressionHandler::isAvailable()) {
                $expressionHandler = new SymfonyExpressionHandler();
                $options['calculator'] ??= $expressionHandler;
                $options['evaluator'] ??= $expressionHandler;
            }
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     *
     * This overridden implementation uses MultiErrorObjectSchema instead of
     * ObjectSchema.
     *
     * @see MultiErrorObjectSchema
     */
    protected function parseSchemaKeywords(
        SchemaInfo $info,
        ?KeywordValidator $keywordValidator,
        array $parsers,
        object $shared,
        bool $hasRef = false
    ): Schema {
        /** @var Keyword[] $prepend */
        $prepend = [];
        /** @var Keyword[] $append */
        $append = [];
        /** @var Keyword[] $before */
        $before = [];
        /** @var Keyword[] $after */
        $after = [];
        /** @var Keyword[][] $types */
        $types = [];
        /** @var Keyword[] $ref */
        $ref = [];

        if ($hasRef) {
            foreach ($parsers as $parser) {
                $kType = $parser->type();

                if ($kType === KeywordParser::TYPE_APPEND) {
                    $container = &$append;
                } elseif ($kType === KeywordParser::TYPE_AFTER_REF) {
                    $container = &$ref;
                } elseif ($kType === KeywordParser::TYPE_PREPEND) {
                    $container = &$prepend;
                } else {
                    continue;
                }

                if ($keyword = $parser->parse($info, $this, $shared)) {
                    $container[] = $keyword;
                }

                unset($container, $keyword, $kType);
            }
        } else {
            foreach ($parsers as $parser) {
                $keyword = $parser->parse($info, $this, $shared);
                if ($keyword === null) {
                    continue;
                }

                $kType = $parser->type();

                switch ($kType) {
                    case KeywordParser::TYPE_PREPEND:
                        $prepend[] = $keyword;
                        break;
                    case KeywordParser::TYPE_APPEND:
                        $append[] = $keyword;
                        break;
                    case KeywordParser::TYPE_BEFORE:
                        $before[] = $keyword;
                        break;
                    case KeywordParser::TYPE_AFTER:
                        $after[] = $keyword;
                        break;
                    case KeywordParser::TYPE_AFTER_REF:
                        $ref[] = $keyword;
                        break;
                    default:
                        if (!isset($types[$kType])) {
                            $types[$kType] = [];
                        }
                        $types[$kType][] = $keyword;
                        break;

                }
            }
        }

        unset($shared);

        if ($prepend) {
            $before = array_merge($prepend, $before);
        }
        unset($prepend);

        if ($ref) {
            $after = array_merge($after, $ref);
        }
        unset($ref);

        if ($append) {
            $after = array_merge($after, $append);
        }
        unset($append);

        if (empty($before)) {
            $before = null;
        }
        if (empty($after)) {
            $after = null;
        }
        if (empty($types)) {
            $types = null;
        }

        if (empty($types) && empty($before) && empty($after)) {
            return new EmptySchema($info, $keywordValidator);
        }

        return new MultiErrorObjectSchema($info, $keywordValidator, $types, $before, $after);
    }
}
