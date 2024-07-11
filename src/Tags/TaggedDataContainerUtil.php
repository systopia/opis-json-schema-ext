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

use Opis\JsonSchema\ValidationContext;

final class TaggedDataContainerUtil
{
    public static function getTaggedDataContainer(ValidationContext $context): TaggedDataContainerInterface
    {
        return $context->globals()['taggedDataContainer'] ?? DummyTaggedDataContainer::getInstance();
    }

    public static function getTaggedPathsContainer(ValidationContext $context): TaggedPathsContainer
    {
        return $context->globals()['taggedPathsContainer'];
    }
}
