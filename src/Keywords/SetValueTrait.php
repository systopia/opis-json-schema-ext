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

namespace Systopia\JsonSchema\Keywords;

use Opis\JsonSchema\ValidationContext;

trait SetValueTrait
{
    /**
     * @param callable(mixed $data): mixed $transform
     *                                                Gets the current data and returns the data to set
     */
    public function setValue(ValidationContext $context, callable $transform): void
    {
        $path = $context->currentDataPath();
        $target = &$this->getDataReference($context, $path);
        $target = $transform($target);
        $this->resetCurrentData($context);
    }

    public function unsetValue(ValidationContext $context): void
    {
        $path = $context->currentDataPath();
        $lastKey = array_pop($path);
        $parentData = &$this->getDataReference($context, $path);

        if (\is_object($parentData)) {
            // @phpstan-ignore-next-line
            unset($parentData->{$lastKey});
        } else {
            unset($parentData[$lastKey]);
        }

        $this->resetCurrentData($context);
    }

    /**
     * @param array<int|string> $path
     *
     * @return mixed
     */
    private function &getDataReference(ValidationContext $context, array $path)
    {
        $data = $context->rootData();
        foreach ($path as $key) {
            if (\is_object($data)) {
                // @phpstan-ignore-next-line
                $data = &$data->{$key};
            } else {
                $data = &$data[$key];
            }
        }

        return $data;
    }

    private function resetCurrentData(ValidationContext $context): void
    {
        $resetPath = [];
        foreach (array_reverse($context->currentDataPath()) as $key) {
            array_unshift($resetPath, $key);
            $context->popDataPath();
            if ('array' !== $context->currentDataType()) {
                break;
            }
        }

        foreach ($resetPath as $key) {
            $context->pushDataPath($key);
        }
    }
}
