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

namespace Systopia\JsonSchema\Tags;

/**
 * @codeCoverageIgnore
 */
final class DummyTaggedDataContainer implements TaggedDataContainerInterface
{
    private static self $instance;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function add(string $tag, string $dataPointer, $data, $extra): void {}

    public function get(string $tag, string $dataPointer)
    {
        return null;
    }

    public function has(string $tag, string $dataPointer): bool
    {
        return false;
    }

    public function getAll(): array
    {
        return [];
    }

    public function getByTag(string $tag): array
    {
        return [];
    }

    public function hasTag(string $tag): bool
    {
        return false;
    }

    public function getExtra(string $tag, string $dataPointer)
    {
        return null;
    }

    public function hasExtra(string $tag, string $dataPointer): bool
    {
        return false;
    }
}
