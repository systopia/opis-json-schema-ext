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
use Systopia\JsonSchema\Translation\NullTranslator;
use Systopia\JsonSchema\Translation\TranslatorFactory;

/**
 * @covers \Systopia\JsonSchema\Translation\TranslatorFactory
 */
final class TranslatorFactoryTest extends TestCase
{
    public function testDeDE(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('test', $schema, $dataInfo, 'message');

        $translator = TranslatorFactory::createTranslator('de_DE');
        self::assertSame(
            'Der Wert muss größer oder gleich 12 sein.',
            $translator->trans('minimum', ['min' => 12], $error)
        );

        self::assertSame(
            'Es ist nicht mehr als ein Eintrag erlaubt.',
            $translator->trans('maxContains', ['max' => 1], $error)
        );

        self::assertSame(
            'Es sind nicht mehr als 2 Einträge erlaubt.',
            $translator->trans('maxContains', ['max' => 2], $error)
        );

        self::assertSame(
            'Das Datum darf nicht vor dem 01.01.1970 sein.',
            $translator->trans('minDate', ['minDateTimestamp' => strtotime('1970-01-01')], $error)
        );
    }

    public function testDe(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('test', $schema, $dataInfo, 'message');

        $translator = TranslatorFactory::createTranslator('de');
        self::assertSame(
            'Der Wert muss größer oder gleich 12 sein.',
            $translator->trans('minimum', ['min' => 12], $error)
        );
    }

    public function testUnknownLocale(): void
    {
        self::assertInstanceOf(
            NullTranslator::class,
            TranslatorFactory::createTranslator('abcd')
        );
    }
}
