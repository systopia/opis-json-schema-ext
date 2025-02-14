<?php

/*
 * Copyright 2023 SYSTOPIA GmbH
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

namespace Systopia\JsonSchema\Test\Translation\Util;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Translation\Util\TranslationParamConverter;

/**
 * @covers \Systopia\JsonSchema\Translation\Util\TranslationParamConverter
 */
final class TranslationParamConverterTest extends TestCase
{
    public function testToTranslationParam(): void
    {
        self::assertSame(1, TranslationParamConverter::toTranslationParam(1));
        self::assertSame(1.1, TranslationParamConverter::toTranslationParam(1.1));
        self::assertSame('test', TranslationParamConverter::toTranslationParam('test'));
        self::assertFalse(TranslationParamConverter::toTranslationParam(false));
        self::assertTrue(TranslationParamConverter::toTranslationParam(true));
        self::assertSame('1, a', TranslationParamConverter::toTranslationParam([1, 'a']));
        // @phpstan-ignore-next-line
        self::assertStringContainsString('test', TranslationParamConverter::toTranslationParam(new \Exception('test')));
        self::assertSame('(array)', TranslationParamConverter::toTranslationParam([1, new \stdClass()]));
        self::assertSame('(object)', TranslationParamConverter::toTranslationParam(new \stdClass()));
        self::assertSame('(NULL)', TranslationParamConverter::toTranslationParam(null));
    }
}
