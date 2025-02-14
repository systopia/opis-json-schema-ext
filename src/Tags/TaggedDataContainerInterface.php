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

interface TaggedDataContainerInterface
{
    /**
     * If the method is called multiple times with the same pair of tag and data
     * pointer, the last data will be available. This might happen e.g. in case
     * of sub schemas.
     *
     * @param null|mixed $data
     * @param null|mixed $extra
     */
    public function add(string $tag, string $dataPointer, $data, $extra): void;

    /**
     * @return null|mixed
     */
    public function get(string $tag, string $dataPointer);

    /**
     * @return bool true if a value (including null) was added for the given tag and pointer
     */
    public function has(string $tag, string $dataPointer): bool;

    /**
     * @return array<string, array<string, null|mixed>> Mapping of tag to a mapping of JSON pointer to data
     */
    public function getAll(): array;

    /**
     * @return array<string, null|mixed> Mapping of JSON pointer to data
     */
    public function getByTag(string $tag): array;

    public function hasTag(string $tag): bool;

    /**
     * @return null|mixed
     */
    public function getExtra(string $tag, string $dataPointer);

    public function hasExtra(string $tag, string $dataPointer): bool;
}
