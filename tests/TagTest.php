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

namespace Systopia\JsonSchema\Test;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\SystopiaValidator;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

/**
 * @covers \Systopia\JsonSchema\KeywordValidators\RootTagKeywordValidator
 * @covers \Systopia\JsonSchema\KeywordValidators\TagKeywordValidator
 * @covers \Systopia\JsonSchema\Parsers\KeywordValidators\TagKeywordValidatorParser
 * @covers \Systopia\JsonSchema\Tags\TaggedDataContainerUtil
 * @covers \Systopia\JsonSchema\Tags\TaggedPathsContainer
 */
final class TagTest extends TestCase
{
    public function testString(): void
    {
        $schema = <<<'JSON'
{
    "type": "string",
    "$tag": "test"
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();
        self::assertTrue($validator->validate('foo', $schema, $globals)->isValid());

        self::assertSame(['test' => ['/' => 'foo']], $taggedDataContainer->getAll());
        self::assertNull($taggedDataContainer->getExtra('test', '/'));
    }

    public function testArray(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "foo": {
          "type": "string",
          "$tag": ["test1", "test2"]
        }
    }
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();
        self::assertTrue($validator->validate((object) ['foo' => 'bar'], $schema, $globals)->isValid());

        self::assertSame([
            'test1' => ['/foo' => 'bar'],
            'test2' => ['/foo' => 'bar'],
        ], $taggedDataContainer->getAll());
        self::assertNull($taggedDataContainer->getExtra('test1', '/foo'));
        self::assertNull($taggedDataContainer->getExtra('test2', '/foo'));
    }

    public function testExtra(): void
    {
        $schema = <<<'JSON'
{
    "type": "string",
    "$tag": {"test1": "extra1", "test2": "extra2"}
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();
        self::assertTrue($validator->validate('foo', $schema, $globals)->isValid());

        self::assertSame([
            'test1' => ['/' => 'foo'],
            'test2' => ['/' => 'foo'],
        ], $taggedDataContainer->getAll());
        self::assertSame('extra1', $taggedDataContainer->getExtra('test1', '/'));
        self::assertSame('extra2', $taggedDataContainer->getExtra('test2', '/'));
    }

    /**
     * @covers \Systopia\JsonSchema\KeywordValidators\CalculateKeywordValidator
     */
    public function testCalculate(): void
    {
        // Tests that the calculation is performed before the value is added to
        // the tagged data container.
        $schema = <<<'JSON'
{
    "type": "number",
    "$calculate": "1 + 2",
    "$tag": "test"
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();
        self::assertTrue($validator->validate(0, $schema, $globals)->isValid());

        self::assertSame(['test' => ['/' => 3]], $taggedDataContainer->getAll());
        self::assertNull($taggedDataContainer->getExtra('test', '/'));
    }

    /**
     * Tests that the tagged data container contains the final, i.e. ordered
     * values.
     */
    public function testOrderBy(): void
    {
        $schema = <<<'JSON'
{
    "type": "object",
    "properties": {
      "array": {
        "type": "array",
        "items": { "type":  ["number"], "$tag": {"test": "extra"} },
        "$order": "ASC"
    }
  }
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();
        $data = (object) ['array' => [3, 2]];
        self::assertTrue($validator->validate($data, $schema, $globals)->isValid());

        self::assertSame(['test' => ['/array/0' => 2, '/array/1' => 3]], $taggedDataContainer->getAll());
        self::assertSame('extra', $taggedDataContainer->getExtra('test', '/array/0'));
        self::assertSame('extra', $taggedDataContainer->getExtra('test', '/array/1'));
    }

    /**
     * @covers \Systopia\JsonSchema\KeywordValidators\CalculateKeywordValidator
     */
    public function testInvalid(): void
    {
        // Tests that the calculation is performed before the value is added to
        // the tagged data container.
        $schema = <<<'JSON'
{
    "type": "number",
    "$tag": [123]
}
JSON;

        $taggedDataContainer = new TaggedDataContainer();
        $globals = ['taggedDataContainer' => $taggedDataContainer];
        $validator = new SystopiaValidator();

        $this->expectException(InvalidKeywordException::class);
        $this->expectExceptionMessage('Invalid value for keyword $tag');
        $validator->validate(0, $schema, $globals)->isValid();
    }
}
