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

namespace Systopia\JsonSchema\Test\Tags;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

/**
 * @covers \Systopia\JsonSchema\Tags\TaggedDataContainer
 */
final class TaggedDataContainerTest extends TestCase
{
    public function test(): void
    {
        $container = new TaggedDataContainer();
        self::assertSame([], $container->getAll());
        self::assertSame([], $container->getByTag('test'));
        self::assertFalse($container->hasTag('test'));
        self::assertFalse($container->has('test', '/foo'));
        self::assertNull($container->get('test', '/foo'));
        self::assertNull($container->getExtra('test', '/foo'));
        self::assertFalse($container->hasExtra('test', '/foo'));

        $container->add('test', '/foo', 'bar', null);
        self::assertSame(['test' => ['/foo' => 'bar']], $container->getAll());
        self::assertSame(['/foo' => 'bar'], $container->getByTag('test'));
        self::assertTrue($container->hasTag('test'));
        self::assertTrue($container->has('test', '/foo'));
        self::assertSame('bar', $container->get('test', '/foo'));
        self::assertNull($container->getExtra('test', '/foo'));
        self::assertFalse($container->hasExtra('test', '/foo'));

        $container->add('test', '/foo2', 'bar2', 'extra');
        self::assertSame(['test' => [
            '/foo' => 'bar',
            '/foo2' => 'bar2',
        ]], $container->getAll());
        self::assertSame([
            '/foo' => 'bar',
            '/foo2' => 'bar2',
        ], $container->getByTag('test'));
        self::assertTrue($container->has('test', '/foo2'));
        self::assertSame('bar2', $container->get('test', '/foo2'));
        self::assertSame('extra', $container->getExtra('test', '/foo2'));
        self::assertTrue($container->hasExtra('test', '/foo2'));

        $container->add('test2', '/foo', null, null);
        self::assertSame(['/foo' => null], $container->getByTag('test2'));
        self::assertTrue($container->has('test2', '/foo'));
        self::assertNull($container->get('test2', '/foo'));
    }
}
