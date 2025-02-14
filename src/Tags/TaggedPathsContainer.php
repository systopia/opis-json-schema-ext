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

namespace Systopia\JsonSchema\Tags;

use Opis\JsonSchema\JsonPointer;

/**
 * The tagged data paths are collected here. Finally, the data is put into the
 * tagged data container. This way it is ensured that the data container
 * contain the final values, e.g. an array with tagged data might get sorted
 * after the "$tag" keyword was applied to the items.
 */
final class TaggedPathsContainer
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $extra = [];

    /**
     * @var array<string, list<string>>
     */
    private array $dataPointers = [];

    /**
     * @var array<string, list<int|string>>
     */
    private array $paths = [];

    /**
     * @param list<int|string> $path
     * @param null|mixed $extra
     */
    public function add(string $tag, array $path, $extra): void
    {
        $dataPointer = JsonPointer::pathToString($path);
        $this->paths[$dataPointer] = $path;
        $this->dataPointers[$tag][] = $dataPointer;
        $this->extra[$tag][$dataPointer] = $extra;
    }

    /**
     * @param mixed $rootData
     */
    public function fillDataContainer($rootData, TaggedDataContainerInterface $dataContainer): void
    {
        foreach ($this->dataPointers as $tag => $dataPointers) {
            foreach ($dataPointers as $dataPointer) {
                $data = JsonPointer::getData($rootData, $this->paths[$dataPointer]);
                $dataContainer->add($tag, $dataPointer, $data, $this->extra[$tag][$dataPointer]);
            }
        }
    }
}
