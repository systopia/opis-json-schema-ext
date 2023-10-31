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

namespace Systopia\JsonSchema\Test\Translation;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Translation\Translator;

/**
 * @covers \Systopia\JsonSchema\Translation\Translator
 */
final class TranslatorTest extends TestCase
{
    public function testAddMessages(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('test', $schema, $dataInfo, 'message');

        $translator = new Translator('en', ['minimum' => 'Minimum: {minimum}']);
        self::assertSame('en', $translator->getLocale());

        self::assertSame('Minimum: 12', $translator->trans('minimum', ['minimum' => '12'], $error));
        self::assertSame('maximum', $translator->trans('maximum', ['maximum' => '12'], $error));

        $translator->addMessages(['maximum' => 'Maximum: {maximum}']);
        self::assertSame('Maximum: 23', $translator->trans('maximum', ['maximum' => '23'], $error));

        $translator->addMessages(['minimum' => 'Minimum']);
        self::assertSame('Minimum', $translator->trans('minimum', ['maximum' => '23'], $error));
    }
}
