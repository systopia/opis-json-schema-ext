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

use Systopia\JsonSchema\Exceptions\InvalidArgumentException;

final class TaggedDataContainer implements TaggedDataContainerInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $data = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $extra = [];

    public function add(string $tag, string $dataPointer, $data, $extra): void
    {
        if ($this->has($tag, $dataPointer)) {
            throw new InvalidArgumentException(sprintf('Data for tag "%s" at "%s" already exists', $tag, $dataPointer));
        }

        $this->data[$tag][$dataPointer] = $data;
        $this->extra[$tag][$dataPointer] = $extra;
    }

    public function get(string $tag, string $dataPointer)
    {
        return $this->data[$tag][$dataPointer] ?? null;
    }

    public function has(string $tag, string $dataPointer): bool
    {
        return \array_key_exists($dataPointer, $this->data[$tag] ?? []);
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function getByTag(string $tag): array
    {
        return $this->data[$tag] ?? [];
    }

    public function hasTag(string $tag): bool
    {
        return isset($this->data[$tag]);
    }

    public function getExtra(string $tag, string $dataPointer)
    {
        return $this->extra[$tag][$dataPointer] ?? null;
    }

    public function hasExtra(string $tag, string $dataPointer): bool
    {
        return isset($this->extra[$tag][$dataPointer]);
    }
}
