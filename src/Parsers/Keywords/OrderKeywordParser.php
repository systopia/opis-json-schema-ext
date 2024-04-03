<?php
/*
 * Copyright 2024 SYSTOPIA GmbH
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

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Keywords\OrderObjectsKeyword;
use Systopia\JsonSchema\Keywords\OrderSimpleKeyword;

final class OrderKeywordParser extends KeywordParser
{
    public function __construct(string $keyword = '$order')
    {
        parent::__construct($keyword);
    }

    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        $order = $this->keywordValue($info);
        if ($order instanceof \stdClass) {
            $order = (array) $order;
            foreach ($order as $direction) {
                $this->assertDirection($direction, $info);
            }

            return new OrderObjectsKeyword($order);
        }

        $this->assertDirection($order, $info);

        return new OrderSimpleKeyword($order);
    }

    /**
     * @param mixed $direction
     *
     * @throws \Opis\JsonSchema\Exceptions\InvalidKeywordException
     *
     * @phpstan-assert 'ASC'|'DESC' $direction
     */
    private function assertDirection($direction, SchemaInfo $info): void
    {
        if ('ASC' !== $direction && 'DESC' !== $direction) {
            throw $this->keywordException('{keyword} must contain "ASC", "DESC", or a mapping of field names to "ASC" or "DESC"', $info);
        }
    }
}
